"""Vyrobí QR kódy pro platbu na účet spolku a nahraje je do médií.

Je to standard **QR Platba** (řetězec SPAYD), který umí přečíst každá česká
bankovní aplikace: návštěvník namíří foťák, aplikace mu předvyplní číslo účtu,
částku i zprávu a on jen potvrdí. Nestojí to nic a nepotřebuje to žádnou
službu třetí strany — jen účet spolku.

Proč obrázky, a ne generování v prohlížeči:
Program i zbytek webu se obejdou bez cizích knihoven a tahle stránka taky má.
Obrázky se vyrobí jednou a leží v médiích; výměna účtu znamená spustit tenhle
skript znovu. Starý kód se před nahráním smaže — WordPress by stejné jméno
nepřepsal, jen by přidal „-1" a v médiích by zůstaly dva kódy se stejným
číslem a jiným účtem.

Potřebuje knihovnu segno (čistý Python, bez závislostí), stejně jako
oriznout-logo.py potřebuje Pillow:

    python3 -m pip install segno
    export BM_WP=~/bm-wordpress/staging/wp.txt
    python3 qr-platba.py

POZOR: QR kód posílá peníze. Po změně účtu ho **vždycky načti bankovní
aplikací** a zkontroluj, že sedí číslo účtu i částka. Tenhle skript to za tebe
ověřit neumí.
"""
import io

import segno

from pripojeni import rest

# Účet spolku ze stránky Kontakt (266541760/0300), převedený na IBAN.
# Kontrolní číslice ověřené; po změně účtu je potřeba přepočítat.
IBAN = "CZ2003000000000266541760"
PRIJEMCE = "BUDEJOVICKY MAJALES Z.S."   # SPAYD je ASCII, bez diakritiky
ZPRAVA = "DAR MAJALES"

# Částky, které stránka nabízí. Rozhodnutí zadavatele ze 7. 11. 2026;
# „jiná částka" vypadla — kdo chce poslat jinak, přepíše si ji v bankovnictví.
CASTKY = [100, 300, 500]


def spayd(castka):
    """Řetězec QR Platby. ACC musí být první pole za verzí."""
    pole = ["SPD*1.0", "ACC:" + IBAN]
    if castka is not None:
        pole.append("AM:%.2f" % castka)
    pole += ["CC:CZK", "MSG:" + ZPRAVA, "RN:" + PRIJEMCE]
    return "*".join(pole)


def smaz_stary(jmeno):
    """Zahodí dřívější kód téhož jména, ať v médiích nezůstanou dva."""
    zaklad = jmeno[:-4]
    nalezene = rest("media?search=%s&per_page=10" % zaklad, "GET")
    for polozka in nalezene if isinstance(nalezene, list) else []:
        if polozka.get("slug", "").startswith(zaklad):
            rest("media/%d?force=true" % polozka["id"], "DELETE")


def nahraj(jmeno, data, popis):
    """Nahraje PNG do médií."""
    smaz_stary(jmeno)
    hlavicky = {
        "Content-Disposition": 'attachment; filename="%s"' % jmeno,
        "Content-Type": "image/png",
    }
    return rest_binarne("media", data, hlavicky, popis)


def rest_binarne(cesta, data, hlavicky, popis):
    """REST s binárním tělem — pripojeni.rest() posílá jen JSON."""
    import json
    import subprocess
    import tempfile

    from pripojeni import AUTH, WEB

    with tempfile.NamedTemporaryFile(suffix=".png", delete=False) as fh:
        fh.write(data)
        docasny = fh.name

    args = ["curl", "-s", "-u", AUTH, "--max-time", "90", "-X", "POST",
            "%s/wp-json/wp/v2/%s" % (WEB, cesta)]
    for klic, hodnota in hlavicky.items():
        args += ["-H", "%s: %s" % (klic, hodnota)]
    args += ["--data-binary", "@" + docasny]

    vystup = subprocess.run(args, capture_output=True, text=True).stdout
    try:
        odpoved = json.loads(vystup)
    except ValueError:
        return {"chyba": vystup[:300]}

    if odpoved.get("id"):
        rest("media/%d" % odpoved["id"], "POST",
             {"alt_text": popis, "title": popis})
    return odpoved


for castka in CASTKY:
    retezec = spayd(castka)
    jmeno = "qr-dar-%s.png" % (castka if castka else "bez-castky")
    popis = ("QR platba %d Kč na účet Budějovického Majálesu" % castka
             if castka else "QR platba na účet Budějovického Majálesu")

    obraz = segno.make(retezec, error="m")
    buffer = io.BytesIO()
    # scale 10 = kód kolem 400 px, border 2 = tichá zóna, kterou čtečky chtějí
    obraz.save(buffer, kind="png", scale=10, border=2, dark="#121212", light="#FFFFFF")

    odpoved = nahraj(jmeno, buffer.getvalue(), popis)
    print("%-22s %s" % (jmeno, odpoved.get("source_url") or odpoved))
    print("   %s" % retezec)
