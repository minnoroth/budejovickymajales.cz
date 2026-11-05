"""Překlopí stránku Program do podoby návrhu: barevný pás, úvod, výpis akcí."""
from pripojeni import rest


# Zkratka [tribe_events] je jen v placené verzi The Events Calendar.
# Výpis proto dělá vlastní doplněk bm-program, zkratkou [bm_program].
# Odkazy do nativního kalendáře zůstávají v záchranném bloku uvnitř doplňku,
# takže se sem už nedávají.

pas = (
    '<!-- wp:group {"align":"full","className":"bm-pas-hlavni","style":{"spacing":{"padding":'
    '{"top":"64px","bottom":"64px","left":"24px","right":"24px"}}},"layout":{"type":"constrained"}} -->\n'
    '<div class="wp-block-group alignfull bm-pas-hlavni" style="padding:64px 24px">'
    '<!-- wp:group {"layout":{"type":"constrained","contentSize":"1160px"}} -->\n'
    '<div class="wp-block-group bm-obsah">'
    '<!-- wp:paragraph {"className":"bm-nadlinka"} -->\n<p class="bm-nadlinka">[bm_termin]</p>\n<!-- /wp:paragraph -->\n'
    '<!-- wp:heading {"level":1} -->\n<h1 class="wp-block-heading">Program</h1>\n<!-- /wp:heading -->\n'
    '<!-- wp:paragraph -->\n<p>Sedm dní, desítky míst po celých Budějovicích. Vstup na všechno zdarma.</p>\n<!-- /wp:paragraph -->\n'
    '</div>\n<!-- /wp:group -->\n'
    '</div>\n<!-- /wp:group -->\n')

vypis = (
    # Odsazení JEN SVISLÉ. Vodorovné má už obal stránky (.entry-content, 24 px)
    # a kdyby ho skupina přidala podruhé, odsadí se obsah o 48 px — na mobilu
    # je to 13 % displeje a navíc to nesedí na svislici nadpisu v pásu nad tím.
    #
    # Skupina zároveň nesmí být „full": časová osa si z odsazení na mobilu
    # vystupuje sama přes šířku okna (program.css), kdežto seznam a filtry
    # odsazení držet mají.
    '<!-- wp:group {"style":{"spacing":{"padding":{"top":"40px","bottom":"72px","left":"0","right":"0"}}},'
    '"layout":{"type":"constrained","contentSize":"1160px"}} -->\n'
    '<div class="wp-block-group" style="padding:40px 0 72px">'
    '<!-- wp:shortcode -->\n[bm_program]\n<!-- /wp:shortcode -->\n'
    '</div>\n<!-- /wp:group -->\n')

obsah = pas + vypis

# Záhlaví Kadence s názvem stránky se skrývá, pás níž má vlastní nadpis.
# Na webu to bylo naklikané ručně, takže by to tenhle skript na čisté
# instalaci neobnovil a Program by měl nadpis dvakrát.
r = rest("pages/83", "POST", {
    "content": obsah,
    "title": "Program",
    "meta": {"_kad_post_title": "hide"},
})
print("stránka:", r.get("id"), (r.get("title") or {}).get("rendered"), r.get("status"))
