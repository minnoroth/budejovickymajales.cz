"""Překlopí stránku Králové do podoby návrhu: barevný pás a průběžné pořadí."""
from pripojeni import rest


# Žebříček vykresluje doplněk bm-kralove zkratkou [bm_kralove]. Ročník si
# dohledá sám — bere nejnovější, který má kandidáty — a vypíše ho nad výpis.

pas = (
    '<!-- wp:group {"align":"full","className":"bm-pas-hlavni","style":{"spacing":{"padding":'
    '{"top":"64px","bottom":"64px","left":"24px","right":"24px"}}},"layout":{"type":"constrained"}} -->\n'
    '<div class="wp-block-group alignfull bm-pas-hlavni" style="padding:64px 24px">'
    '<!-- wp:group {"layout":{"type":"constrained","contentSize":"1160px"}} -->\n'
    '<div class="wp-block-group bm-obsah">'
    '<!-- wp:paragraph {"className":"bm-nadlinka"} -->\n<p class="bm-nadlinka">[bm_termin]</p>\n<!-- /wp:paragraph -->\n'
    '<!-- wp:heading {"level":1} -->\n<h1 class="wp-block-heading">Volba krále</h1>\n<!-- /wp:heading -->\n'
    '<!-- wp:paragraph -->\n<p>Každá střední škola vysílá svého kandidáta. Body se sbírají '
    'na studentských jamech, v královské hře a za plnění výzev. Vítěz vede svou školu '
    'v pátečním průvodu.</p>\n<!-- /wp:paragraph -->\n'
    '</div>\n<!-- /wp:group -->\n'
    '</div>\n<!-- /wp:group -->\n')

# Odsazení JEN SVISLÉ — vodorovné má už obal stránky (.entry-content, 24 px).
# Stejný důvod a stejná past jako u Programu, viz program.py.
vypis = (
    '<!-- wp:group {"style":{"spacing":{"padding":{"top":"40px","bottom":"72px","left":"0","right":"0"}}},'
    '"layout":{"type":"constrained","contentSize":"1160px"}} -->\n'
    '<div class="wp-block-group" style="padding:40px 0 72px">'
    '<!-- wp:shortcode -->\n[bm_kralove]\n<!-- /wp:shortcode -->\n'
    '</div>\n<!-- /wp:group -->\n')

obsah = pas + vypis

# Kadence vypisuje nad obsahem vlastní záhlaví s názvem stránky. Pás níž má
# vlastní nadpis, takže by se „Králové" ukázalo dvakrát. Skrytí je nastavení
# Kadence u stránky; nastavuje se odsud, aby ho nikdo nemusel naklikat znovu.
r = rest("pages/85", "POST", {
    "content": obsah,
    "title": "Králové",
    "meta": {"_kad_post_title": "hide"},
})
print("stránka:", r.get("id"), (r.get("title") or {}).get("rendered"), r.get("status"))
