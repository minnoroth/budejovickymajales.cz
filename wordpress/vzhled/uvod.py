from pripojeni import rest


def sekce(cls, inner, pad="72px"):
    """Pás přes celou šířku okna, obsah uvnitř zarovnaný na čitelnou šířku."""
    vnitrek = (f'<!-- wp:group {{"layout":{{"type":"constrained","contentSize":"1160px"}}}} -->\n'
               f'<div class="wp-block-group bm-obsah">{inner}</div>\n<!-- /wp:group -->\n')
    return (f'<!-- wp:group {{"align":"full","className":"{cls}","style":{{"spacing":{{"padding":'
            f'{{"top":"{pad}","bottom":"{pad}","left":"24px","right":"24px"}}}}}},'
            f'"layout":{{"type":"constrained"}}}} -->\n'
            f'<div class="wp-block-group alignfull {cls}" style="padding:{pad} 24px">{vnitrek}</div>\n'
            f'<!-- /wp:group -->\n')


def hero(inner):
    """Úvodní pás. Na rozdíl od ostatních pásů **nemá odsazení v bloku**.

    Hlavička je na úvodu průhledná a leží přes pás, takže si pás musí nahoře
    udělat místo na výšku menu — a ta je na telefonu jiná než na monitoru.
    Odsazení zapsané v bloku je inline styl, který se podle šířky okna měnit
    nedá. **Svislé** odsazení je proto v bm.css u třídy .bm-pas-uvod.

    **Vodorovné** odsazení naopak v bloku zůstat musí: Kadence u bloků přes
    celou šířku vynuluje padding zleva i zprava selektorem, který obyčejná
    třída nepřebije. Inline styl ano.
    """
    vnitrek = ('<!-- wp:group {"layout":{"type":"constrained","contentSize":"1160px"}} -->\n'
               f'<div class="wp-block-group bm-obsah">{inner}</div>\n<!-- /wp:group -->\n')
    return ('<!-- wp:group {"align":"full","className":"bm-pas-hlavni bm-pas-uvod",'
            '"style":{"spacing":{"padding":{"left":"24px","right":"24px"}}},'
            '"layout":{"type":"constrained"}} -->\n'
            '<div class="wp-block-group alignfull bm-pas-hlavni bm-pas-uvod"'
            f' style="padding-left:24px;padding-right:24px">{vnitrek}</div>\n'
            '<!-- /wp:group -->\n')


def nadpis(text, level=2, extra=""):
    return (f'<!-- wp:heading {{"level":{level}{extra}}} -->\n'
            f'<h{level} class="wp-block-heading">{text}</h{level}>\n<!-- /wp:heading -->\n')


def odstavec(text, trida=""):
    attrs = f'{{"className":"{trida}"}}' if trida else "{}"
    cls = f' class="{trida}"' if trida else ""
    return f'<!-- wp:paragraph {attrs} -->\n<p{cls}>{text}</p>\n<!-- /wp:paragraph -->\n'


T, K, Z, L = "var(--global-palette1)", "var(--global-palette10)", "var(--global-palette15)", "var(--global-palette14)"
B3, B9 = "var(--global-palette3)", "var(--global-palette9)"

obsah = ""

# hero
obsah += hero(
    odstavec("[bm_termin]", "bm-nadlinka")
    + nadpis("Studentský festival, který dělá věci jinak.", 1)
    + odstavec("Sedm dní programu po celých Budějovicích. Divadlo, hudba, filmy, přednášky i volba krále. Vstup na všechno zdarma.")
    + '<!-- wp:html -->\n<p><a class="bm-tlacitko" href="/program/">Prohlédnout program</a></p>\n<!-- /wp:html -->\n')

# běžící pás
slogan = "[bm_termin] · VSTUP ZDARMA · SEDM DNÍ · 100+ AKCÍ · ČESKÉ BUDĚJOVICE · "
kopie = ('<span class="bm-bezici__text">' + slogan * 4 + '</span>'
         '<span class="bm-bezici__text" aria-hidden="true">' + slogan * 4 + '</span>')
obsah += ('<!-- wp:html -->\n<div class="bm-bezici alignfull">'
          '<div class="bm-bezici__draha">' + kopie + '</div></div>\n<!-- /wp:html -->\n')

# nepřehlédni
# Kartičky byly napsané natvrdo — text, který někdo přepsal rukou z programu.
# Když se akce zrušila nebo přesunula, úvodní stránka o tom nevěděla. Teď je
# skládá doplněk bm-carousel z akcí označených „Top představení".
#
# Nadpis vypisuje zkratka, ne tahle stránka. Kdyby byl tady, zůstal by viset
# nad prázdnem ve chvíli, kdy žádná akce označená není — třeba než
# dramaturgie program zveřejní.
obsah += sekce("bm-sekce",
    '<!-- wp:shortcode -->\n[bm_carousel]\n<!-- /wp:shortcode -->\n')

# kategorie
dlazdice = "".join(
    f'<a class="bm-dlazdice" href="/program/" style="background:{b};color:{tx}">'
    f'<span>{n}</span><span style="font-size:15px;font-weight:600">{p}</span></a>'
    # Počty byly natvrdo 43 / 22 / 14 / 7, zatímco skutečnost byla
    # 42 / 18 / 7 / 7. Úvodní stránka lhala ve čtyřech číslech a poznat se
    # to dalo jen ručním přepočítáním programu. Teď je počítá doplněk.
    for n, p, b, tx in [
        ("Hudba", "[bm_pocet_akci kategorie=hudba]", T, B9),
        ("Venkovní scéna", "[bm_pocet_akci kategorie=venkovni-scena]", K, B3),
        ("Divadlo", "[bm_pocet_akci kategorie=divadlo]", L, B3),
        ("Volba krále", "[bm_pocet_akci kategorie=volba-krale]", Z, B3),
    ])
obsah += sekce("bm-sekce",
    nadpis("Co tě zajímá?")
    + odstavec("Sedm dní, desítky míst po celém městě. Vyber si, co tě baví.")
    + '<!-- wp:html -->\n<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px">'
    + dlazdice + '</div>\n<!-- /wp:html -->\n')

# králové
obsah += sekce("bm-pas-koral",
    nadpis("Volba krále")
    + odstavec("Každá střední škola vysílá svého kandidáta. Body se sbírají na studentských jamech, v královské hře a za plnění výzev.")
    + '<!-- wp:html -->\n<p><a class="bm-tlacitko" href="/kralove/">Průběžné pořadí</a></p>\n<!-- /wp:html -->\n')

# aktuality
obsah += sekce("bm-sekce",
    nadpis("Aktuality")
    + odstavec("Co je nového kolem festivalu.")
    + '<!-- wp:latest-posts {"postsToShow":3,"displayPostDate":true,"displayFeaturedImage":true,"columns":3,"postLayout":"grid"} /-->\n')

# partneři
obsah += sekce("bm-pas-levand",
    nadpis("Festival vzniká díky nim")
    + odstavec("Hlavní partneři ročníku")
    + '<!-- wp:html -->\n<p><a class="bm-tlacitko" href="/partneri/">Všichni partneři</a></p>\n<!-- /wp:html -->\n')

if __name__ == "__main__":
    front = rest("settings")
    print("  název webu:", front.get("title"))
    # Průhledná hlavička je zapnutá jen tady: fotka na pozadí pásu má jít
    # až nahoru, pod menu. Na ostatních stránkách je vypnutá v nastaveni.py,
    # protože bílé menu na bílém obsahu by zmizelo.
    r = rest("pages/82", "POST", {
        "content": obsah,
        "title": "Úvod",
        "meta": {"_kad_post_transparent": "enable"},
    })
    print("  stránka:", r.get("id"), "|", (r.get("title") or {}).get("rendered"), "|", r.get("status"))
    print("  délka obsahu:", len((r.get("content") or {}).get("rendered", "")), "znaků")
