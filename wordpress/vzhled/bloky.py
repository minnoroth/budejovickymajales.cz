"""Společné bloky stránek. Dřív byly rozkopírované v každém skriptu zvlášť.

Rozdíl mezi úvodem a podstránkami (rozhodnuto 5. 11. 2026):

**Úvodní stránka** má velký barevný pás přes celou šířku — je to první věc, kterou
návštěvník uvidí, a má zabrat. Skládá si ho uvod.py vlastním pomocníkem `sekce()`,
protože takových pásů má víc. Pozadí pásu se dá vyměnit za fotku: stačí u stránky
Úvod nastavit náhledový obrázek, zbytek obstará doplněk bm-pozadi.php.

**Podstránky** měly ten samý pás a byla to chyba. Na Programu zabíral skoro celou
první obrazovku, takže návštěvník musel odrolovat, než uviděl jedinou akci — a kvůli
tomu na Program chodí. Podstránky proto mají jen nadpis a hned obsah.
"""

ODSAZENI_OBSAHU = '{"top":"24px","bottom":"72px","left":"0","right":"0"}'


def hlavicka(nadpis, text=""):
    """Nadpis podstránky a nic víc. Žádný pás, žádná barva.

    Skupina nemá žádné odsazení ani spodní okraj. Mezeru nad nadpisem drží
    `.content-area` (24 px, v bm.css) a mezeru pod ním `obsah()`, aby byla
    na jednom místě a stejná i na stránkách, kde nadpis kreslí Kadence.
    Zadavatel 6. 11. 2026: odsazení kolem nadpisu bylo „obrovský úplně
    zbytečně", nejvýš 24 px.
    """
    blok = ('<!-- wp:heading {"level":1} -->\n'
            f'<h1 class="wp-block-heading">{nadpis}</h1>\n<!-- /wp:heading -->\n')
    if text:
        blok += ('<!-- wp:paragraph {"className":"bm-lead"} -->\n'
                 f'<p class="bm-lead">{text}</p>\n<!-- /wp:paragraph -->\n')

    return ('<!-- wp:group {"style":{"spacing":'
            '{"padding":{"top":"0","bottom":"0","left":"0","right":"0"},'
            '"margin":{"bottom":"0"}}},'
            '"layout":{"type":"constrained","contentSize":"1160px"}} -->\n'
            '<div class="wp-block-group" style="margin-bottom:0;padding:0">'
            + blok +
            '</div>\n<!-- /wp:group -->\n')


def obsah(vnitrek):
    """Obal obsahu stránky.

    Odsazení JEN SVISLÉ. Vodorovné má už obal stránky (.entry-content, 24 px)
    a kdyby ho skupina přidala podruhé, odsadí se obsah o 48 px — na mobilu
    je to 13 % displeje a navíc to nesedí na svislici nadpisu nad tím.
    """
    return ('<!-- wp:group {"style":{"spacing":{"padding":' + ODSAZENI_OBSAHU + '}},'
            '"layout":{"type":"constrained","contentSize":"1160px"}} -->\n'
            '<div class="wp-block-group" style="padding:24px 0 72px">'
            + vnitrek +
            '</div>\n<!-- /wp:group -->\n')


def zkratka(kod):
    return f'<!-- wp:shortcode -->\n[{kod}]\n<!-- /wp:shortcode -->\n'
