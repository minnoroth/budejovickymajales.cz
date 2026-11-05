"""Překlopí stránku Program do podoby návrhu: nadpis a rovnou výpis akcí."""
from bloky import hlavicka, obsah, zkratka
from pripojeni import rest


# Zkratka [tribe_events] je jen v placené verzi The Events Calendar.
# Výpis proto dělá vlastní doplněk bm-program, zkratkou [bm_program].
# Odkazy do nativního kalendáře zůstávají v záchranném bloku uvnitř doplňku.
#
# Velký barevný pás, který tu byl do 5. 11. 2026, zabíral skoro celou první
# obrazovku. Návštěvník musel odrolovat, než uviděl jedinou akci — a kvůli
# akcím na Program chodí.

stranka = hlavicka(
    "Program",
    "Sedm dní, desítky míst po celých Budějovicích. Vstup na všechno zdarma.",
) + obsah(zkratka("bm_program"))

# Kadence vypisuje nad obsahem vlastní záhlaví s názvem stránky; nadpis je
# v obsahu, takže by se „Program" ukázalo dvakrát.
r = rest("pages/83", "POST", {
    "content": stranka,
    "title": "Program",
    "meta": {"_kad_post_title": "hide"},
})
print("stránka:", r.get("id"), (r.get("title") or {}).get("rendered"), r.get("status"))
