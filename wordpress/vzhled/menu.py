"""Založí hlavní menu a napojí ho na pozici primary."""
from pripojeni import rest


STROM = [
    # Program nemá podstránky: zkratka [bm_program] drží Lineup i Seznam
    # na jedné stránce. Stránky /program/lineup/ a /program/prehled/ jsou
    # od 5. 11. 2026 v koši, znovu je sem nepřidávat.
    ("Program", 83, []),
    ("Králové", 85, []),
    ("Partneři", 84, [("Osvěžovny", 90), ("Podpoř nás!", 91)]),
    ("Praktické info", 86, [("Pro návštěvníky", 92), ("Doprava", 94),
                            ("Často kladené otázky", 95), ("Přístupnost", 93)]),
    ("O festivalu", 87, [("O nás", 97), ("Historie", 99), ("Dobrovolníci", 98),
                         ("Fotogalerie", 100), ("Pro média", 102), ("Kontakt", 101)]),
    ("Aktuality", 114, []),
]

menu = rest("menus", "POST", {"name": "Hlavní menu", "locations": ["primary"]})
mid = menu.get("id")
print("menu:", mid, menu.get("name"), menu.get("locations"))

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
