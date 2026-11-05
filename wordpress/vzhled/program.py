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
    # Vodorovné odsazení drží skupina, svislé taky — ale časová osa z něj na
    # mobilu schválně vystupuje až k okrajům displeje. Dělá to program.css sám
    # přes šířku okna, takže se tady nesmí sahat na layout: kdyby skupina byla
    # „full", vystoupil by i seznam a nalepil by se na kraj.
    '<!-- wp:group {"style":{"spacing":{"padding":{"top":"40px","bottom":"72px","left":"24px","right":"24px"}}},'
    '"layout":{"type":"constrained","contentSize":"1160px"}} -->\n'
    '<div class="wp-block-group" style="padding:40px 24px">'
    '<!-- wp:shortcode -->\n[bm_program]\n<!-- /wp:shortcode -->\n'
    '</div>\n<!-- /wp:group -->\n')

obsah = pas + vypis

r = rest("pages/83", "POST", {"content": obsah, "title": "Program"})
print("stránka:", r.get("id"), (r.get("title") or {}).get("rendered"), r.get("status"))
