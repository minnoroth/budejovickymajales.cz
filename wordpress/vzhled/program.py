"""Překlopí stránku Program do podoby návrhu: barevný pás, úvod, výpis akcí."""
from pripojeni import rest


# Zkratka [tribe_events] je jen v placené verzi The Events Calendar,
# proto se na kalendář odkazuje tlačítkem.

pas = (
    '<!-- wp:group {"align":"full","className":"bm-pas-hlavni","style":{"spacing":{"padding":'
    '{"top":"64px","bottom":"64px","left":"24px","right":"24px"}}},"layout":{"type":"constrained"}} -->\n'
    '<div class="wp-block-group alignfull bm-pas-hlavni" style="padding:64px 24px">'
    '<!-- wp:group {"layout":{"type":"constrained","contentSize":"1160px"}} -->\n'
    '<div class="wp-block-group bm-obsah">'
    '<!-- wp:paragraph {"className":"bm-nadlinka"} -->\n<p class="bm-nadlinka">30. 5. – 5. 6. 2027</p>\n<!-- /wp:paragraph -->\n'
    '<!-- wp:heading {"level":1} -->\n<h1 class="wp-block-heading">Program</h1>\n<!-- /wp:heading -->\n'
    '<!-- wp:paragraph -->\n<p>Sedm dní, desítky míst po celých Budějovicích. Vstup na všechno zdarma.</p>\n<!-- /wp:paragraph -->\n'
    '</div>\n<!-- /wp:group -->\n'
    '</div>\n<!-- /wp:group -->\n')

vypis = (
    '<!-- wp:group {"style":{"spacing":{"padding":{"top":"56px","bottom":"72px","left":"24px","right":"24px"}}},'
    '"layout":{"type":"constrained","contentSize":"1160px"}} -->\n'
    '<div class="wp-block-group" style="padding:56px 24px">'
    '<!-- wp:heading -->\n<h2 class="wp-block-heading">Kalendář akcí</h2>\n<!-- /wp:heading -->\n'
    '<!-- wp:paragraph -->\n<p>Celý program najdeš v kalendáři — dá se filtrovat podle dne, '
    'kategorie i místa.</p>\n<!-- /wp:paragraph -->\n'
    '<!-- wp:html -->\n<p><a class="bm-tlacitko" href="/events/">Otevřít kalendář</a></p>\n<!-- /wp:html -->\n'
    '</div>\n<!-- /wp:group -->\n')

obsah = pas + vypis

r = rest("pages/83", "POST", {"content": obsah, "title": "Program"})
print("stránka:", r.get("id"), (r.get("title") or {}).get("rendered"), r.get("status"))
