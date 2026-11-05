"""Nahraje oříznutá loga do médií, nastaví logo hlavičky a znak v patičce.

Spouští se po `nastaveni.py` — patří do kroků stavby vzhledu od nuly, protože
ID přílohy a adresa souboru jsou na každé instalaci jiné.

    export BM_WP=~/bm-wordpress/staging/wp.txt
    python3 logo.py
"""

import json
import os
import subprocess

from pripojeni import AUTH, WEB, customizer, rest

SLOZKA = os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "branding")
# Odkazy v patičce, jako ID stránek. Partneři tu jsou proto, že z hlavního
# menu zmizeli (rozhodnutí 5. 11. 2026) — loga partnerů musí zůstat
# dosažitelná. GDPR tu je proto, že jinde na webu na něj nevede jediný odkaz.
#
# Adresy se dotahují z WordPressu, nepíšou se sem natvrdo: stránky jsou
# zanořené, takže Kontakt má adresu /o-festivalu/kontakt/. Napsat „/kontakt/"
# funguje jen díky tomu, že WordPress adresu uhodne a přesměruje — a to je
# berlička, která jednou přestane platit.
ODKAZY_PATICKA = [
    (84, "Partneři"),
    (91, "Podpoř nás"),
    (101, "Kontakt"),
    (96, "Zpracování osobních údajů"),
]


def paticka_odkazy():
    casti = []
    for pid, popis in ODKAZY_PATICKA:
        stranka = rest("pages/%d" % pid, "GET")
        adresa = stranka.get("link")
        if not adresa:
            raise SystemExit("Stránka %d nemá adresu — patičku neměním." % pid)
        casti.append('<a href="%s">%s</a>' % (adresa, popis))
    return '<nav class="bm-paticka-odkazy">' + "".join(casti) + "</nav>"


PATICKA = ('<img src="{url}" alt="" width="38" style="display:block;margin:0 auto 14px" />'
           "{odkazy}"
           "© {{year}} {{site-title}} · Nezisková organizace. "
           "Festival dělají studenti a dobrovolníci.")


def najdi(soubor):
    """Už nahraný obrázek téhož jména. Bez téhle kontroly nasype každé
    spuštění skriptu do médií další kopii — po pár bězích je v knihovně
    majales-znak-bile-1, -2, -3 a nikdo neví, která je ta použitá."""
    slug = os.path.splitext(soubor)[0]
    for m in rest("media?per_page=100&_fields=id,slug,source_url", "GET"):
        if m.get("slug") == slug:
            return m["id"], m["source_url"]
    return None


def nahraj(soubor):
    hotovo = najdi(soubor)
    if hotovo:
        print("  %s už v médiích je (%d)" % (soubor, hotovo[0]))
        return hotovo

    vystup = subprocess.run([
        "curl", "-s", "-u", AUTH, "--max-time", "120", "-X", "POST",
        f"{WEB}/wp-json/wp/v2/media",
        "-H", f'Content-Disposition: attachment; filename="{soubor}"',
        "-H", "Content-Type: image/png",
        "--data-binary", f"@{os.path.join(SLOZKA, soubor)}",
    ], capture_output=True, text=True).stdout
    r = json.loads(vystup)
    if "id" not in r:
        raise SystemExit(f"Nahrání {soubor} selhalo: {vystup[:300]}")
    return r["id"], r["source_url"]


if __name__ == "__main__":
    bile_id, bile_url = nahraj("majales-znak-bile.png")
    nahraj("majales-znak-cerne.png")
    # Relativní adresa přežije i změnu domény (staging → ostrý web).
    cesta = bile_url.split(WEB, 1)[-1] if bile_url.startswith(WEB) else bile_url
    kod, odpoved = customizer({
        "custom_logo": str(bile_id),
        "footer_html_content": PATICKA.format(url=cesta, odkazy=paticka_odkazy()),
    })
    print("logo hlavičky: příloha", bile_id, "|", cesta)
    print("HTTP", kod, "|", (odpoved.get("data") or {}).get("setting_validities")
          if isinstance(odpoved, dict) else odpoved)
