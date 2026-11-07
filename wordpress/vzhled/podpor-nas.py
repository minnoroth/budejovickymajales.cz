"""Obsah stránky Podpoř nás. Je možné spustit opakovaně.

Rozhodnutí ze 7. 11. 2026 (zadavatel): stránka má nabídnout tři cesty
a vést návštěvníka k té nejrychlejší.

1. **QR platba** — česká norma, kterou umí každá bankovní aplikace. Tři
   doteky od „chci dát pětistovku" k hotovu, nulový poplatek, žádná další
   služba. Kódy vyrábí a do médií nahrává qr-platba.py.
2. **Donio** — veřejná sbírka s příběhem, cílem a potvrzením o daru.
3. **E-shop** — jiný druh podpory, proto až pod nimi a přes celou šířku.

Proč ne vložený iframe Donia (zadavatel se ptal):
Nejde nastylovat, takže by to byla jediná část webu, která nevypadá jako
Majáles — zrovna ta, na které nejvíc záleží. Navíc cizí skripty na stránce
s penězi a pomalejší načtení. Staré vložení mělo natvrdo výšku 4000 px
a na mobilu bylo rozbité. Rozhodnutí je v docs/wordpress/21-podpor-nas.md.

Přepínání částek je na čistém CSS (radio + :checked), ne na JavaScriptu —
stránka tím pádem funguje i bez něj a nepřibyla žádná závislost.

POZOR: odkaz na Donio míří na sbírku 23. ročníku. Až bude sbírka na ročník
2027, musí se přepsat tady.

    export BM_WP=~/bm-wordpress/staging/wp.txt
    python3 podpor-nas.py
"""
from bloky import obsah
from pripojeni import rest

STRANKA = 91

DONIO = "https://donio.cz/23-rocnik-budejovickeho-majalesu"
ESHOP = "https://shop.budejovickymajales.cz/"
UCET = "266541760 / 0300"

# Jméno souboru v médiích → popisek částky. None = kód bez částky.
CASTKY = [(200, "200 Kč"), (500, "500 Kč"), (1000, "1 000 Kč"), (None, "jiná částka")]
VYCHOZI = 500


def adresa_kodu(castka):
    """Najde v médiích QR kód, který nahrál qr-platba.py."""
    jmeno = "qr-dar-%s" % (castka if castka else "bez-castky")
    nalezene = rest("media?search=%s&per_page=5" % jmeno, "GET")
    for polozka in nalezene if isinstance(nalezene, list) else []:
        if polozka.get("slug", "").startswith(jmeno):
            return polozka.get("source_url", "")
    return ""


def prepinac():
    """Přepínač částek a k němu čtyři QR kódy. Bez jediného řádku JS:
    zaškrtnuté radio přes ~ obarví svůj štítek a odkryje svůj kód."""
    radia, stitky, kody = "", "", ""

    for castka, popisek in CASTKY:
        klic = castka if castka else "jina"
        zaskrtnuto = " checked" if castka == VYCHOZI else ""
        radia += ('<input type="radio" name="bm-qr" id="bm-qr-%s" class="bm-qr__volba"%s>'
                  % (klic, zaskrtnuto))
        stitky += '<label for="bm-qr-%s">%s</label>' % (klic, popisek)

        url = adresa_kodu(castka)
        popis = ("QR kód pro platbu %s na účet Budějovického Majálesu" % popisek
                 if castka else "QR kód pro platbu na účet Budějovického Majálesu")
        kody += ('<img class="bm-qr__kod bm-qr__kod--%s" src="%s" alt="%s" '
                 'width="420" height="420" loading="lazy">' % (klic, url, popis))

    return (radia
            + '<div class="bm-qr__castky" role="group" aria-label="Částka">'
            + stitky + '</div>'
            + '<div class="bm-qr__obraz">' + kody + '</div>')


KARTY = (
    '<div class="bm-podpor">'

    '<section class="bm-podpor__karta bm-podpor__karta--qr">'
    '<p class="bm-podpor__poradi">Nejrychlejší</p>'
    '<h2>Pošli pár stovek z mobilu</h2>'
    '<div class="bm-qr">' + prepinac() +
    '<div class="bm-qr__ucet">'
    '<p class="bm-qr__cislo">' + UCET + '</p>'
    '<p class="bm-qr__drobne">Namiř na kód foťák — aplikace předvyplní částku '
    'i zprávu. Nebo číslo účtu opiš, zpráva pro příjemce <strong>DAR</strong>.</p>'
    '</div></div>'
    '</section>'

    '<section class="bm-podpor__karta bm-podpor__karta--donio">'
    '<p class="bm-podpor__poradi">Veřejná sbírka</p>'
    '<h2>Donio</h2>'
    '<p>Sbírka s příběhem a cílem. Zaplatíš kartou nebo převodem a dostaneš '
    'potvrzení o daru.</p>'
    '<p><a class="bm-tlacitko bm-tlacitko--donio" href="' + DONIO + '" '
    'target="_blank" rel="noopener">Přispět na Donio →</a></p>'
    '</section>'

    '<section class="bm-podpor__karta bm-podpor__karta--eshop">'
    '<p class="bm-podpor__poradi">Něco za to</p>'
    '<h2>Kup si kus Majálesu</h2>'
    '<p>Trička, plakáty a placky. Peníze jdou na festival a ty máš doma důkaz, '
    'že jsi u toho byl.</p>'
    '<p><a class="bm-tlacitko bm-tlacitko--eshop" href="' + ESHOP + '" '
    'target="_blank" rel="noopener">Do e-shopu →</a></p>'
    '</section>'

    '</div>'
)

LEAD = (
    '<!-- wp:paragraph {"className":"bm-lead"} -->\n'
    '<p class="bm-lead">Sedm dní, stovka vystoupení, vstup zdarma. Jde to jen proto, '
    'že se lidi složí. <strong>Budějovický Majáles je nezisková organizace</strong> '
    'a festival dělají studenti a dobrovolníci — každá koruna jde na program.</p>\n'
    '<!-- /wp:paragraph -->\n'
)

TELO = LEAD + obsah('<!-- wp:html -->\n' + KARTY + '\n<!-- /wp:html -->\n')

r = rest("pages/%d" % STRANKA, "POST", {"content": TELO})
print("stránka:", r.get("id"), (r.get("title") or {}).get("rendered"), r.get("status"))
print("odkaz:", r.get("link"))
