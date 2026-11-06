"""Překlopí stránku Osvěžovny: nadpis a rovnou kartičky podniků.

Co se tím zahazuje: dosavadní obsah stránky, tedy devět nadpisů s odrážkami
a — na starém webu — ještě <style> blok s loňskou modrou natvrdo. Předchozí
verze zůstává v revizích stránky ve WordPressu, takže se dá vrátit.

Podniky vykresluje doplněk bm-osvezovny zkratkou [bm_osvezovny]. Data do něj
nasype jednorázově osvezovny-data.py; od příštího ročníku je tým přepisuje
v administraci v Osvěžovny.
"""
from bloky import hlavicka, obsah, zkratka
from pripojeni import rest

# Úvodní věta je doslova ta ze starého webu, jen zkrácená o výčet, který
# teď dělá samotný výpis („kam zajít na dobrý drink, kde ochutnáte domácí
# dobroty…“ — to si návštěvník přečte přímo v kartičkách).
stranka = hlavicka(
    "Osvěžovny",
    "Budějovické podniky si pro festival chystají speciální menu a drinky "
    "za zvýhodněné ceny. Slevy platí po celý týden festivalu, od pondělí do pátku.",
) + obsah(zkratka("bm_osvezovny"))

r = rest("pages/90", "POST", {
    "content": stranka,
    "title": "Osvěžovny",
    # Nadpis kreslí blok v obsahu, ne Kadence — jinak by byl na stránce dvakrát.
    "meta": {"_kad_post_title": "hide"},
})
print("stránka:", r.get("id"), (r.get("title") or {}).get("rendered"), r.get("status"))
