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

# Částky rozhodl zadavatel 7. 11. 2026. „Jiná částka" vypadla — kdo chce
# poslat jinak, přepíše si ji v bankovnictví, takže kód navíc nic nepřidal.
# Výchozí je prostřední: nabídnout rovnou nejvyšší je tlak, nejnižší je škoda.
CASTKY = [(100, "100 Kč"), (300, "300 Kč"), (500, "500 Kč")]
VYCHOZI = 300


def adresa_kodu(castka):
    """Najde v médiích QR kód, který nahrál qr-platba.py."""
    jmeno = "qr-dar-%s" % castka
    nalezene = rest("media?search=%s&per_page=5" % jmeno, "GET")
    for polozka in nalezene if isinstance(nalezene, list) else []:
        if polozka.get("slug", "").startswith(jmeno):
            return polozka.get("source_url", "")
    return ""


def stitek(cislo, text):
    """Štítek kroku: číslo v kolečku a co se v tom kroku dělá."""
    return ('<p class="bm-krok__stitek"><span class="bm-krok__cislo">%s</span>%s</p>'
            % (cislo, text))


def kroky():
    """Tři kroky kartičky: vyber částku, načti kód, nebo pošli převodem.

    Přepínač částek je pořád bez jediného řádku JS — zaškrtnuté radio přes ~
    obarví svůj štítek a odkryje svůj kód. Proto **radia musí zůstat přímými
    sourozenci kroků**: `~` přes úrovně nefunguje a zabalení do kroku by
    přepínač vypnulo.
    """
    radia, stitky, kody = "", "", ""

    for castka, popisek in CASTKY:
        klic = castka
        zaskrtnuto = " checked" if castka == VYCHOZI else ""
        radia += ('<input type="radio" name="bm-qr" id="bm-qr-%s" class="bm-qr__volba"%s>'
                  % (klic, zaskrtnuto))
        stitky += '<label for="bm-qr-%s">%s</label>' % (klic, popisek)

        url = adresa_kodu(castka)
        popis = "QR kód pro platbu %s na účet Budějovického Majálesu" % popisek
        kody += ('<img class="bm-qr__kod bm-qr__kod--%s" src="%s" alt="%s" '
                 'width="420" height="420" loading="lazy">' % (klic, url, popis))

    return (radia
            + '<div class="bm-krok">'
            + stitek(1, "Vyber částku")
            + '<div class="bm-qr__castky" role="group" aria-label="Částka">'
            + stitky + '</div></div>'

            # Druhý krok se jmenuje podle toho, co s kódem na daném přístroji
            # jde udělat: na monitoru se skenuje, na telefonu ukládá. Odkaz
            # na uložení sem dopisuje mu-plugins/sdilet-qr.js.
            + '<div class="bm-krok bm-krok--kod">'
            + '<p class="bm-krok__stitek"><span class="bm-krok__cislo">2</span>'
            + '<span class="bm-qr__fotak">Namiř na kód foťák</span>'
            + '<span class="bm-qr__mobil">Ulož kód a načti v bance</span>'
            + '</p>'
            + '<div class="bm-qr__obraz">' + kody + '</div></div>'

            + '<div class="bm-krok">'
            + stitek(3, "Nebo pošli převodem")
            + '<p class="bm-qr__cislo bm-kopirovatelne">' + UCET + '</p>'
            + '<p class="bm-qr__drobne">Zpráva pro příjemce '
              '<strong>DAR</strong>.</p></div>')


KARTY = (
    '<div class="bm-podpor">'

    '<section class="bm-podpor__karta bm-podpor__karta--qr">'
    '<header class="bm-podpor__hlava">'
    '<p class="bm-podpor__poradi">Nejrychlejší</p>'
    '<h2>Pošli pár stovek z mobilu</h2></header>'
    '<div class="bm-qr">' + kroky() + '</div>'
    '</section>'

    '<section class="bm-podpor__karta bm-podpor__karta--donio">'
    '<header class="bm-podpor__hlava">'
    '<p class="bm-podpor__poradi">Veřejná sbírka</p>'
    '<h2>Donio</h2></header>'
    '<p>Sbírka s příběhem a cílem. Zaplatíš kartou nebo převodem a dostaneš '
    'potvrzení o daru.</p>'
    '<p><a class="bm-tlacitko bm-tlacitko--donio" href="' + DONIO + '" '
    'target="_blank" rel="noopener">Přispět na Donio →</a></p>'
    '</section>'

    '<section class="bm-podpor__karta bm-podpor__karta--eshop">'
    '<header class="bm-podpor__hlava">'
    '<p class="bm-podpor__poradi">Něco za to</p>'
    '<h2>Kup si kus Majálesu</h2></header>'
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
