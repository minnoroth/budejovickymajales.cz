"""Sloučí čtyři podstránky Praktického info do jedné stránky s kotvami.

Proč: rozbalovátko se čtyřmi položkami bylo na mobilu to, co zadavatele na menu
štvalo nejvíc. Tři z těch čtyř stránek přitom byly jen zástupný text — skutečný
obsah měla jedině stránka s častými otázkami. Sloučením zmizí rozbalovátko
a nic se nezahrabe: na každou sekci vede kotva.

Časté otázky se překlápějí do rozbalovacích bloků (otázka / odpověď), aby se
dvanáct otázek dalo přehlédnout naráz. Použitý je nativní prvek <details>,
takže to funguje bez jediného řádku JavaScriptu, na dotek i z klávesnice.

Skript je možné spustit opakovaně — obsah stránky vždy přepíše celý.
Texty otázek jsou dole v tomhle souboru, ne na webu: stránka s častými
otázkami, ze které se původně vzaly, je v koši, a až ho někdo vysype,
nebylo by je odkud přečíst. Platí tu totéž co u ostatních skriptů ve složce:
zdrojem obsahu je repozitář, ruční úpravy ve WordPressu příští běh přepíše.
"""
import html
import re

from bloky import hlavicka, obsah as obal_obsahu
from pripojeni import rest

STRANKA = 86          # Praktické info
# Původní podstránky. Při prvním běhu šly do koše, pak už tam jsou.
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


# ── časté otázky ─────────────────────────────────────────────────────────
# Převzaté z původní stránky /faq/ starého webu. Text se mění tady.
OTAZKY = [
    (
        "Kde zjistím, co je za program a kde přesně program najdu?",
        "Program naleznete na našich sociálních sítích a webových stránkách. Na instagramu program sdílíme do postů a také ve stories, kam dáváme co nás v daný den přesně za program čeká. Pokud nás ještě nesledujete, měli byste to určitě napravit. Na webových stránkách máme přímo sekci s názvem PROGRAM , kde najdete celý přehled. Každý den se navíc aktualizuje hlavní stránka s nejzajímavějším programem.",
    ),
    (
        "Jak mohu podpořit svého krále?",
        "Pro svého vybraného favorita můžete hlasovat skrze studentské jamy, zapojení se do královské hry. Králové také plní různé výzvy, za které dostávají body. Na webových stránkách se v sekci KRÁLOVÉ dozvíte, jak si váš favorit vede a jaký má počet hlasů.",
    ),
    (
        "Jaké je vstupné na program Budějovického Majálesu?",
        "Vstupné na všechny body programu je zcela dobrovolné. Ačkoliv není tento ročník z finančního hlediska příznivý, vkládáme veškeré úsilí do udržení nekomerčního charakteru a zpřístupnění kvalitní kultury všem a zdarma.",
    ),
    (
        "Je BM věkově omezený?",
        "BM není věkově omezený! Náš festival je přístupný komukoliv bez ohledu na jeho věk.",
    ),
    (
        "Ruší se venkovní program v případě nepříznivého počasí?",
        "Pokud je kvůli špatnému počasí narušen venkovní program, máme pro vás vždy připravenou jinou alternativu ve vnitřních prostorách. O změnách a nové lokaci náhradního programu se můžete dozvědět na našich webových stránkách nebo sociálních sítích, kde změny neustále aktualizujeme.",
    ),
    (
        "Jak můžu BM podpořit?",
        "Festival můžete finančně podpořit přispěním jakékoliv částky do veřejné sbírky, koupí unikátního merche či dobrovolným vstupným na akcích BM.",
    ),
    (
        "Kde můžu koupit merch BM a kolik stojí?",
        "Jako každý rok jsme si pro vás připravili unikátní kousky merche (trička, plátěnky, plakáty a samolepky). Máte tak jedinečnou příležitost odnést si kus BM a vzpomínku na něj domů! Vše ohledně merche najdete <a href=\"https://shop.budejovickymajales.cz/\" target=\"_blank\" rel=\"noreferrer noopener\">zde</a>.",
    ),
    (
        "Budu si moct zakoupit merch i po konci festivalu?",
        "Nebude to možné, proto nepromeškejte jedinečnou příležitost a podpořte svůj oblíbený festival a potěšte zároveň šatník novým speciálním kouskem!",
    ),
    (
        "Odkud BM čerpá peníze?",
        "BM je nezisková organizace, tudíž je závislá na svých sponzorech a partnerech. Projekt vzniká za podpory Jihočeského kraje, Statutárního města České Budějovice a Budějovického Budvaru. Mezi naše sponzory patří například Borovka Event, Dopravní podnik města České Budějovice, Jihočeské divadlo a mnoho dalších. Přehled všech našich partnerů naleznete <a href=\"/partneri/\">zde</a>.",
    ),
    (
        "Kde najdu Autokino?",
        "Majálesové večerní Autokino se tradičně koná na Náplavce u Dlouhého mostu. Neváhejte a přijeďte na promítání filmů z pohodlí svého auta!",
    ),
    (
        "Odkud, kudy a kam Majálesový průvod vede?",
        "Páteční závěrečný průvod začíná v parku Háječek, kde se sejdou všechny školy spolu se svými králi a následně putují přes centrum města Českých Budějovic, včetně náměstí Přemysla Otakara II. nebo Krajinské ulice. Průvod vrcholí na Sokolském ostrově, kde program pokračuje s poslední disciplínou mezi třemi finálními králi, kteří se utkají o místo výherce daného ročníku Budějovického Majálesu.",
    ),
    (
        "Co jsou to osvěžovny?",
        "Osvěžovny vznikají ve spolupráci s budějovickými <a href=\"/osvezovny/\">podniky</a> (většinou s kavárnami). Kavárny si pro návštěvníky festivalu připravují speciální majálesová menu a drinky za zvýhodněné ceny, které platí po celý týden festivalu.",
    ),
]


# ── sestavení ────────────────────────────────────────────────────────────

telo = rozcestnik([(k, n) for k, n, _ in SEKCE] + [("caste-otazky", "Časté otázky")])

for kotva, nazev, pid in SEKCE:
    telo += nadpis(nazev, kotva)
    texty = odstavce(obsah_stranky(pid))
    telo += "".join(odstavec(t) for t in texty) if texty else ""
    if len(texty) < 2:
        telo += pripravujeme()

telo += nadpis("Časté otázky", "caste-otazky")
telo += "".join(rozbalovatko(q, a) for q, a in OTAZKY)

stranka = hlavicka(
    "Praktické info",
    "Všechno, co potřebuješ vědět, než vyrazíš: doprava, přístupnost a odpovědi "
    "na nejčastější otázky.",
) + obal_obsahu(telo)

r = rest("pages/%d" % STRANKA, "POST", {
    "content": stranka,
    "title": "Praktické info",
    "meta": {"_kad_post_title": "hide"},
})
print("stránka:", r.get("id"), (r.get("title") or {}).get("rendered"), r.get("status"))
print("otázek:", len(OTAZKY))

for pid in DO_KOSE:
    d = rest("pages/%d" % pid, "DELETE")
    stav = (d.get("previous") or {}).get("slug") or d.get("slug") or pid
    print("  do koše:", stav)
