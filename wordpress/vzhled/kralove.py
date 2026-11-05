"""Překlopí stránku Králové: nadpis a rovnou průběžné pořadí."""
from bloky import hlavicka, obsah, zkratka
from pripojeni import rest


# Žebříček vykresluje doplněk bm-kralove zkratkou [bm_kralove]. Ročník si
# dohledá sám — bere nejnovější, který má kandidáty — a vypíše ho nad výpis.

stranka = hlavicka(
    "Volba krále",
    "Každá střední škola vysílá svého kandidáta. Body se sbírají na studentských "
    "jamech, v královské hře a za plnění výzev. Vítěz vede svou školu v pátečním průvodu.",
) + obsah(zkratka("bm_kralove"))

r = rest("pages/85", "POST", {
    "content": stranka,
    "title": "Králové",
    "meta": {"_kad_post_title": "hide"},
})
print("stránka:", r.get("id"), (r.get("title") or {}).get("rendered"), r.get("status"))
