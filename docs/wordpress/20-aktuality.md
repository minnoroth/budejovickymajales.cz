# 20. Aktuality v designu webu

> **7. 11. 2026.** Zadavatel: *„Celkově Aktuality jsou v nic moc stavu."*
> Byly to poslední stránky webu ve výchozím vzhledu Kadence. Návrh, ze kterého
> se vybíralo, je v artefaktu *Aktuality v designu webu*; zadavatel zvolil
> **variantu A — mřížku kartiček**.

---

## Tři skoro bílé, které se o sebe otíraly

Na jedné obrazovce se potkávala tři pozadí a každé pocházelo odjinud:

| Co | Barva | Odkud |
|---|---|---|
| stránka | `#FAFAF9` | palette8 |
| pás nad výpisem | `#F2F2F0` | `post_archive_title_background` |
| pás nad článkem | `#FFFFFF` | `post_title_background` |
| rám kolem článku i kartiček | `#FFFFFF` | `content_style: boxed` |

Švy mezi nimi nikdo nečetl jako záměr, jen jako nečistotu. **Zůstala jedna
plocha: papírová stránka.** Pásy mají barvu stránky (vypnout je úplně nejde,
aniž by zmizel i nadpis) a rámy jsou pryč.

Při tom zbělal i titulkový pás **ostatních stránek** — bílá tam byla od
6. 11., kdy nahradila zelenou, a proti papírové stránce dělala tentýž šev.
Teď je `/o-festivalu/o-nas/` i `/testovaci-prispevek/` jedna souvislá plocha.

## Výpis

**Nadpis doleva a podtržený barvou sekce**, jako na všech ostatních stránkách.
Byl jako jediný na webu na střed. Má jinou třídu než stránky a příspěvky
(`.archive-title`), takže se musel do pravidla pro podtržení dopsat zvlášť.

**Kartička je ta samá jako jinde na webu:** černý obrys, rádius 18, plakátový
stín při najetí, rubrika jako štítek. Kadence umí jen měkký stín nebo nic,
takže se kreslí v `bm.css`. Selektor `.loop-entry` je obecný schválně — stejně
vypadá i výsledek hledání nebo výpis rubriky.

**Kartičky mají strop šířky 360 px a pakují se zleva.** Kadence dělí šířku na
tři stejné sloupce, takže jediný článek vypadal jako díra po dvou dalších.
Teď je první v řadě. Na mobilu je jeden sloupec přes celou šířku.

## Článek

Text sedí přímo na stránce, bez bílého rámu, a drží šířku 62 znaků — přes celý
obsah se dlouhý řádek špatně čte. Rubrika, nadpis s podtržením a řádek s autorem
a datem zůstaly, jak byly.

## Kde to žije

| Co | Kde |
|---|---|
| zarovnání nadpisu, barvy pásů, zrušení rámů | `wordpress/vzhled/nastaveni.py` |
| obrys kartičky, stín, štítek rubriky, mřížka, šířka textu | `wordpress/vzhled/bm.css` |

**`post_archive_title_font` Kadence nezná** a Customizer ho odmítne — nadpis
archivu bere velikost z `h1`.

## Co zůstává otevřené

- **Náhledové obrázky.** Kartička na ně je připravená (`.post-thumbnail`), ale
  žádný příspěvek je zatím nemá. Až je tým začne nahrávat, obrázek sedne na
  obrys sám.
- **Autor v řádku s datem.** Dnes tam stojí „Od Michal Marek", což je účet,
  kterým se web stavěl. Jestli má u festivalových aktualit autor vůbec být,
  je rozhodnutí pro zadavatele; vypíná se v Customizeru.
