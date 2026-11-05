from pripojeni import rest


def sekce(cls, inner, pad="40px"):
    """Pás přes celou šířku okna, obsah uvnitř zarovnaný na čitelnou šířku.

    Odsazení 40 px nahoře i dole. Dřív bylo 72 a mezi dvěma sekcemi tím
    vznikalo skoro sto padesát pixelů prázdna.
    """
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


# Co na úvodu SCHVÁLNĚ není (rozhodnuto 5. 11. 2026):
#
# - „Co tě zajímá?" — čtyři dlaždice kategorií vedly všechny na /program/,
#   takže to byl jen barevný mezikrok k témuž odkazu.
# - „Volba krále" — vlastní pás s tlačítkem na /kralove/, které je v menu.
# - „Festival vzniká díky nim" — partneři mají odkaz v patičce.
#
# Úvod je tím: fotka, běžící pás, zvýrazněné akce, aktuality. Nic, co jen
# odkazuje jinam.

obsah = ""

# hero
obsah += hero(
    odstavec("[bm_termin]", "bm-nadlinka")
    + nadpis("Studentský festival, který dělá věci jinak.", 1)
    + odstavec("Sedm dní programu po celých Budějovicích. Divadlo, hudba, filmy, přednášky i volba krále. Vstup na všechno zdarma.")
    + '<!-- wp:html -->\n<p class="bm-tlacitka">'
      '<a class="bm-tlacitko" href="/program/">Program</a>'
      '<a class="bm-tlacitko bm-tlacitko--podpor" href="/podpor-nas/">Podpořit'
      '<span class="bm-sipka" aria-hidden="true">\u2192</span></a>'
      '</p>\n<!-- /wp:html -->\n')

# Běžící pás. Lepí se rovnou pod fotku a odděluje úvodní sekci od obsahu —
# proto nesmí mít nad sebou mezeru, kterou blokový editor dává mezi bloky.
# Mezera se ruší v bm.css u .bm-bezici, ne tady: inline styl by přebil
# i responzivní pravidla.
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

# aktuality
obsah += sekce("bm-sekce",
    nadpis("Aktuality")
    + odstavec("Co je nového kolem festivalu.")
    + '<!-- wp:latest-posts {"postsToShow":3,"displayPostDate":true,"displayFeaturedImage":true,"columns":3,"postLayout":"grid"} /-->\n')

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
