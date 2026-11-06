"""Založí osvěžovny podle nabídky z dnešního webu (ročník 2026).

Spustí se jednou. Opakované spuštění nic neduplikuje — podnik se hledá
podle názvu a existující se přepíše.

    export BM_WP=~/bm-wordpress/staging/wp.txt
    python3 osvezovny-data.py

Data jsou opsaná z https://budejovickymajales.cz/osvezovny — devět podniků
a 24 položek. Pro příští ročník se tenhle skript nespouští; nabídku přepíše
tým v administraci. Je tu proto, aby šel web postavit od nuly, ne jako
nástroj na každoroční údržbu.

Proč je u většiny položek „druh" prázdný
----------------------------------------
Druh plní dvě role: dělí nabídku na skupiny v kartičce a pohání filtr nad
výpisem. Vyplňuje se **jen tam, kde to říká předloha nebo samotný název**:

* Široko mělo na starém webu nadpisy „Alko:" a „Nealko:" — to je doslovný
  přepis.
* Gyros a buchty jsou jídlo, Kofola je nealko, Budvar je alko — to se pozná
  z názvu.
* „Květnový infarkt" (yerba maté, mangový džus, espresso a trochu zlata)
  zařadit nejde a hádat za tým se nemá. Zůstává prázdný, položka se vypíše
  normálně a jen se neobjeví pod filtrem.

Kartička, kde se sejde míň než dva různé druhy, nadpisy vůbec nedostane —
zůstane plochým seznamem jako dnes.
"""

from pripojeni import rest

# (název, adresa, odkaz na mapu, [(název položky, popis, cena, druh), …])
#
# Odkaz na mapu se vyplňuje jen tam, kde ho měl starý web. Kde chyběl,
# zůstává prázdný a doplněk podnik vyhledá podle názvu — stejně jako to
# dělal starý web čtyřikrát ručně.
PODNIKY = [
    (
        "Mezi plátky",
        "Dr. Stejskala 8/433",
        "https://maps.app.goo.gl/RL9avMmC6n4dfohh6",
        [
            ("Květnový infarkt", "Yerba maté, mangový džus, espresso a trochu zlata", "105 Kč", ""),
            ("Májový les", "Grepová matcha s filtrem", "95 Kč", ""),
            ("Pír", "Aquavit vol.2, hruškové pyré a prosecco", "155 Kč", ""),
        ],
    ),
    (
        "Široko",
        "Široká 443/19",
        "https://maps.app.goo.gl/vDEReLX97DQE28FSA",
        [
            ("Tequila Sunrise", "", "100 Kč", "alko"),
            ("Mangová Mimosa", "", "80 Kč", "alko"),
            ("Bor Ton", "Borovička s tonicem", "90 Kč", "alko"),
            ("Vis Fizz", "Višňovka se sodou", "80 Kč", "alko"),
            ("Okurkový Fizz", "", "70 Kč", "nealko"),
            ("Domácí Jahodová Matcha", "", "80 Kč", "nealko"),
            ("Domácí Mangová Matcha", "", "80 Kč", "nealko"),
        ],
    ),
    (
        "Masné krámy",
        "Krajinská 13",
        "",
        [
            ("Smažený sýrový špíz", "Tatarka, hranolky a Budvar 33 0,3 l", "199 Kč", "jidlo"),
        ],
    ),
    (
        "Peak of matcha",
        "",
        "",
        [
            ("Drink s ISICEM 0,3 l", "", "100 Kč", "nealko"),
        ],
    ),
    (
        "Datel",
        "Piaristická 22/8",
        "https://maps.app.goo.gl/BYp1eyv72uFzAmsX8",
        [
            ("Limonády", "", "70 Kč", "nealko"),
            ("Flash Batch", "Ledový filtr", "70 Kč", "nealko"),
            ("Makovec", "Veganský", "40 Kč", "jidlo"),
            ("Kokosová buchta", "", "40 Kč", "jidlo"),
        ],
    ),
    (
        "Divoko",
        "Dr. Stejskala 177",
        "https://maps.app.goo.gl/QaCJ9jptzq2FMoix7",
        [
            ("Točená Kofola 0,5 l", "", "45 Kč", "nealko"),
            ("Točený Budvar 33 0,5 l", "", "55 Kč", "alko"),
        ],
    ),
    (
        "Café Háječek",
        "Park Háječek",
        "",
        [
            # „Levandule" u kávovaru může být limonáda i drink. Nevíme, tak nehádáme.
            ("Levandule", "", "105 Kč", ""),
            ("Matcha basil lemonade", "", "85 Kč", "nealko"),
            ("Iced tiramisu latte", "", "105 Kč", "nealko"),
        ],
    ),
    (
        "Gyros Creppa Greg",
        "",
        "",
        [
            ("Majálesový Gyros", "Bez hranolek", "100 Kč", "jidlo"),
            ("Majálesová Čoko Creppa", "", "90 Kč", "jidlo"),
        ],
    ),
    (
        "Klika",
        "Hroznová 25",
        "",
        [
            # Jediná „položka", která žádnou položkou není — a kvůli které
            # je cena text, který smí zůstat prázdný.
            ("Sleva 20 % na vše", "Heslo: Majáles", "", ""),
        ],
    ),
]


def najdi(nazev):
    """ID podniku stejného jména, nebo None. Drží skript idempotentní."""
    nalezene = rest(f"osvezovna?search={nazev.replace(' ', '%20')}&per_page=50&status=any")
    if not isinstance(nalezene, list):
        return None
    for polozka in nalezene:
        if (polozka.get("title") or {}).get("rendered", "").strip() == nazev:
            return polozka.get("id")
    return None


def uloz(poradi, nazev, adresa, mapa, polozky):
    telo = {
        "title": nazev,
        "status": "publish",
        # Pořadí drží „Pořadí" z Atributů, ne datum vložení. Bez něj by se
        # podniky seřadily podle abecedy a Mezi plátky by spadly doprostřed.
        "menu_order": poradi,
        "acf": {
            "adresa": adresa,
            "mapa": mapa,
            "polozky": [
                {"nazev": n, "popis": p, "cena": c, "druh": d}
                for n, p, c, d in polozky
            ],
        },
    }

    existujici = najdi(nazev)
    cesta = f"osvezovna/{existujici}" if existujici else "osvezovna"

    return rest(cesta, "POST", telo)


if __name__ == "__main__":
    chyb = 0
    for poradi, (nazev, adresa, mapa, polozky) in enumerate(PODNIKY, start=1):
        odpoved = uloz(poradi * 10, nazev, adresa, mapa, polozky)
        zapsane = (odpoved.get("acf") or {}).get("polozky") or []

        # Kontroluje se, co se vrátilo z databáze, ne co se poslalo. Zápis
        # opakovatelného pole přes REST je jediné místo celého skriptu,
        # které může tiše selhat.
        sedi = len(zapsane) == len(polozky)
        chyb += 0 if sedi else 1
        print(
            f"{'OK   ' if sedi else 'CHYBA'} {nazev:<20} id={odpoved.get('id')} "
            f"položek {len(zapsane)}/{len(polozky)}"
        )

    print(f"\n{'HOTOVO' if not chyb else 'SELHALO'}: {len(PODNIKY)} podniků, {chyb} chyb")
