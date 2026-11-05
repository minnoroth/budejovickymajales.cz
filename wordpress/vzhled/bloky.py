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

ODSAZENI_OBSAHU = '{"top":"40px","bottom":"72px","left":"0","right":"0"}'


def hlavicka(nadpis, text=""):
    """Nadpis podstránky a nic víc. Žádný pás, žádná barva.

    Odsazení je jen svislé a menší nahoře než dole: nadpis má navazovat na
    hlavičku webu, ne se od ní odrážet.
    """
    blok = ('<!-- wp:heading {"level":1} -->\n'
            f'<h1 class="wp-block-heading">{nadpis}</h1>\n<!-- /wp:heading -->\n')
    if text:
        blok += ('<!-- wp:paragraph {"className":"bm-lead"} -->\n'
                 f'<p class="bm-lead">{text}</p>\n<!-- /wp:paragraph -->\n')

    return ('<!-- wp:group {"style":{"spacing":{"padding":'
            '{"top":"28px","bottom":"8px","left":"0","right":"0"}}},'
            '"layout":{"type":"constrained","contentSize":"1160px"}} -->\n'
            '<div class="wp-block-group" style="padding:28px 0 8px">'
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
            '<div class="wp-block-group" style="padding:40px 0 72px">'
            + vnitrek +
            '</div>\n<!-- /wp:group -->\n')


def zkratka(kod):
    return f'<!-- wp:shortcode -->\n[{kod}]\n<!-- /wp:shortcode -->\n'
