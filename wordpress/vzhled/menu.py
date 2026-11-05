"""Postaví hlavní menu. Je možné ho spustit opakovaně — menu vždy přestaví celé.

Struktura vychází z rozhodnutí ze 4. a 5. 11. 2026, zapsaných
v docs/wordpress/13-struktura-a-program.md:

- Osvěžovny patří do hlavní úrovně. Pod Partnery byly nesmysl — je to
  informace pro návštěvníka, ne pro sponzora.
- Partneři zůstávají jako stránka, ale z hlavní úrovně mizí. Loga partnerů
  bývají součástí smlouvy o partnerství, takže stránka zmizet nesmí; v menu
  ale zabírala místo, které patří návštěvníkovi.
- Praktické info nemá podstránky: čtyři se slučují do jedné stránky s kotvami
  (prakticke-info.py). Rozbalovátko se čtyřmi položkami bylo na mobilu to,
  co na menu vadilo nejvíc.
- Aktuality jdou pod O festivalu.
- Program nemá podstránky: zkratka [bm_program] drží Lineup i Seznam na jedné
  stránce. Stránky /program/lineup/ a /program/prehled/ jsou v koši,
  znovu je sem nepřidávat.

Zbylo jedno rozbalovátko místo šesti a pět položek místo devatenácti.
"""
from pripojeni import rest

NAZEV = "Hlavní menu"

STROM = [
    ("Program", 83, []),
    ("Osvěžovny", 90, []),
    ("Králové", 85, []),
    ("Praktické info", 86, []),
    ("O festivalu", 87, [
        ("O nás", 97),
        ("Historie", 99),
        # Aktuality jsou tu nejnavštěvovanější položka, proto hned nahoře.
        ("Aktuality", 114),
        ("Dobrovolníci", 98),
        ("Fotogalerie", 100),
        ("Pro média", 102),
        ("Partneři", 84),
        ("Kontakt", 101),
    ]),
]


def najdi_menu():
    """Existující menu, nebo None. Hledá podle názvu, ne podle pozice —
    pozice se dá v Customizeru odpojit a skript by pak založil druhé menu."""
    for m in rest("menus?per_page=100", "GET"):
        if (m.get("name") or "").strip() == NAZEV:
            return m
    return None


def vyprazdni(mid):
    """Smaže položky menu. Bez toho by se při druhém spuštění zdvojily."""
    polozky = rest("menu-items?menus=%d&per_page=100" % mid, "GET")
    for p in polozky:
        rest("menu-items/%d?force=true" % p["id"], "DELETE")
    return len(polozky)


menu = najdi_menu()
if menu:
    print("menu:", menu["id"], NAZEV, "— smazáno starých položek:", vyprazdni(menu["id"]))
    # Pozici nastavit i u existujícího menu: kdyby se někdy odpojilo,
    # skript ho vrátí zpátky, aniž by se muselo klikat.
    rest("menus/%d" % menu["id"], "POST", {"locations": ["primary"]})
else:
    menu = rest("menus", "POST", {"name": NAZEV, "locations": ["primary"]})
    print("menu založeno:", menu.get("id"), menu.get("name"))

mid = menu["id"]
poradi = 0

for nazev, page_id, deti in STROM:
    poradi += 1
    rodic = rest("menu-items", "POST", {
        "title": nazev, "menus": mid, "object": "page", "object_id": page_id,
        "type": "post_type", "status": "publish", "menu_order": poradi})
    print(" ", nazev, "→", rodic.get("id"))
    for d_nazev, d_id in deti:
        poradi += 1
        d = rest("menu-items", "POST", {
            "title": d_nazev, "menus": mid, "object": "page", "object_id": d_id,
            "type": "post_type", "status": "publish", "parent": rodic.get("id"),
            "menu_order": poradi})
        print("     ", d_nazev, "→", d.get("id"))
