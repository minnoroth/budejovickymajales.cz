"""Sloučí čtyři podstránky Praktického info do jedné stránky s kotvami.

Proč: rozbalovátko se čtyřmi položkami bylo na mobilu to, co zadavatele na menu
štvalo nejvíc. Tři z těch čtyř stránek přitom byly jen zástupný text — skutečný
obsah měla jedině stránka s častými otázkami. Sloučením zmizí rozbalovátko
a nic se nezahrabe: na každou sekci vede kotva.

Časté otázky se překlápějí do rozbalovacích bloků (otázka / odpověď), aby se
dvanáct otázek dalo přehlédnout naráz. Použitý je nativní prvek <details>,
takže to funguje bez jediného řádku JavaScriptu, na dotek i z klávesnice.

Skript je možné spustit opakovaně — obsah stránky vždy přepíše celý.
Původní podstránky pošle do koše; obsah otázek si před tím vytáhne z té,
kterou maže, takže na pořadí kroků záleží a dělá se to v jednom běhu.
"""
import html
import re

from pripojeni import rest

STRANKA = 86          # Praktické info
FAQ = 95              # Často kladené otázky — zdroj obsahu
DO_KOSE = (92, 93, 94, 95)

SEKCE = [
    ("pro-navstevniky", "Pro návštěvníky", 92),
    ("doprava", "Doprava", 94),
    ("pristupnost", "Přístupnost", 93),
]


def obsah_stranky(pid):
    p = rest("pages/%d?context=edit" % pid, "GET")
    return ((p.get("content") or {}).get("raw") or "").strip()


def odstavce(surovy):
    """Vytáhne odstavce stránky bez bloků, které popisují samy sebe."""
    texty = re.findall(r"<p>(.*?)</p>", surovy, re.S)
    # Věta „obsah dodá zadavatel“ je poznámka pro tým, ne text pro návštěvníka.
    # Na sloučené stránce by vypadala jako chyba, tak ji nahradí jedna hláška.
    return [t.strip() for t in texty if "dodá zadavatel" not in t]


def otazky(surovy):
    """Dvojice otázka/odpověď z původní stránky s častými otázkami."""
    return re.findall(
        r'<h3 class="wp-block-heading">(.*?)</h3>.*?<!-- wp:paragraph -->\s*<p>(.*?)</p>',
        surovy, re.S)


def nadpis(text, kotva):
    return ('<!-- wp:heading {"anchor":"%s"} -->\n'
            '<h2 class="wp-block-heading" id="%s">%s</h2>\n<!-- /wp:heading -->\n'
            % (kotva, kotva, html.escape(text)))


def odstavec(text):
    return "<!-- wp:paragraph -->\n<p>%s</p>\n<!-- /wp:paragraph -->\n" % text


def pripravujeme():
    return ('<!-- wp:paragraph {"className":"bm-pripravujeme"} -->\n'
            '<p class="bm-pripravujeme">Tuhle část ještě připravujeme.</p>\n'
            "<!-- /wp:paragraph -->\n")


def rozcestnik(polozky):
    """Odkazy na sekce. Nahrazuje rozbalovátko, které bylo v menu."""
    odkazy = "".join(
        '<li><a class="bm-kotva" href="#%s">%s</a></li>' % (k, html.escape(n))
        for k, n in polozky)
    return ('<!-- wp:html -->\n<ul class="bm-kotvy">%s</ul>\n<!-- /wp:html -->\n'
            % odkazy)


def rozbalovatko(otazka, odpoved):
    return ('<!-- wp:html -->\n'
            '<details class="bm-otazka"><summary>%s</summary>'
            "<div class=\"bm-otazka__text\"><p>%s</p></div></details>\n"
            "<!-- /wp:html -->\n" % (otazka.strip(), odpoved.strip()))


# ── sestavení ────────────────────────────────────────────────────────────

faq = otazky(obsah_stranky(FAQ))
if len(faq) < 5:
    raise SystemExit("Načetlo se jen %d otázek — něco je špatně, nic neměním." % len(faq))

pas = (
    '<!-- wp:group {"align":"full","className":"bm-pas-hlavni","style":{"spacing":{"padding":'
    '{"top":"64px","bottom":"64px","left":"24px","right":"24px"}}},"layout":{"type":"constrained"}} -->\n'
    '<div class="wp-block-group alignfull bm-pas-hlavni" style="padding:64px 24px">'
    '<!-- wp:group {"layout":{"type":"constrained","contentSize":"1160px"}} -->\n'
    '<div class="wp-block-group bm-obsah">'
    '<!-- wp:paragraph {"className":"bm-nadlinka"} -->\n<p class="bm-nadlinka">[bm_termin]</p>\n<!-- /wp:paragraph -->\n'
    '<!-- wp:heading {"level":1} -->\n<h1 class="wp-block-heading">Praktické info</h1>\n<!-- /wp:heading -->\n'
    "<!-- wp:paragraph -->\n<p>Všechno, co potřebuješ vědět, než vyrazíš: doprava, "
    "přístupnost a odpovědi na nejčastější otázky.</p>\n<!-- /wp:paragraph -->\n"
    "</div>\n<!-- /wp:group -->\n"
    "</div>\n<!-- /wp:group -->\n")

telo = rozcestnik([(k, n) for k, n, _ in SEKCE] + [("caste-otazky", "Časté otázky")])

for kotva, nazev, pid in SEKCE:
    telo += nadpis(nazev, kotva)
    texty = odstavce(obsah_stranky(pid))
    telo += "".join(odstavec(t) for t in texty) if texty else ""
    if len(texty) < 2:
        telo += pripravujeme()

telo += nadpis("Časté otázky", "caste-otazky")
telo += "".join(rozbalovatko(q, a) for q, a in faq)

# Odsazení jen svislé — vodorovné má už obal stránky. Stejná past jako
# u Programu a Králů, viz program.py.
obsah = pas + (
    '<!-- wp:group {"style":{"spacing":{"padding":{"top":"40px","bottom":"72px","left":"0","right":"0"}}},'
    '"layout":{"type":"constrained","contentSize":"1160px"}} -->\n'
    '<div class="wp-block-group" style="padding:40px 0 72px">'
    + telo +
    "</div>\n<!-- /wp:group -->\n")

r = rest("pages/%d" % STRANKA, "POST", {
    "content": obsah,
    "title": "Praktické info",
    "meta": {"_kad_post_title": "hide"},
})
print("stránka:", r.get("id"), (r.get("title") or {}).get("rendered"), r.get("status"))
print("otázek přeneseno:", len(faq))

for pid in DO_KOSE:
    d = rest("pages/%d" % pid, "DELETE")
    stav = (d.get("previous") or {}).get("slug") or d.get("slug") or pid
    print("  do koše:", stav)
