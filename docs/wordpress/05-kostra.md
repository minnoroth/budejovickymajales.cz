# Kostra — co už stojí

Postaveno 6. 10. 2026 na lokální instalaci WordPressu 7.1.1 (`~/Projects/personal/bm-wordpress-local`).
Čtyři agenti pracovali paralelně, každý s výhradním vlastnictvím své části; stav níže je
**ověřený nezávisle na jejich hlášení** přes WP-CLI a HTTP.

## Stav

| Část | Stav |
|---|---|
| Místa | 28 záznamů `tribe_venue`, publikovaná |
| Žánry a kategorie | 51 termínů `tribe_events_cat` — 9 kategorií s barvou, 42 žánrů pod nimi |
| Stránky | 21, české slugy, správná hierarchie, homepage nastavená |
| Typ obsahu Králové | registrovaný, s opakovačem na body po segmentech |
| Taxonomie Ročník | registrovaná (hierarchická), termín 2027 s datumy a textem na homepage |
| Pole | 5 skupin, 12 polí v Secure Custom Fields |
| Role Dramaturgie | vytvořená přes plugin Members, ověřená na testovacím uživateli |
| Navigace | `wp_navigation` s celou strukturou včetně rozbalovacích úrovní |

Všech 21 URL vrací HTTP 200. Menu se na frontendu vykresluje ve správné hierarchii.

## Datový model

**Typ obsahu `kral`** — jméno je titulek, fotka náhledový obrázek.
Pole: `skola` (text), `tema` (text), `body` (opakovač s podpoli `segment` a `pocet`).
Segmenty: Studentské jamy, Královská hra, Plnění výzev, Ostatní.

**Taxonomie `rocnik`** — hierarchická, napojená na příspěvky, akce i krále.
Pole na termínu: `datum_od`, `datum_do`, `text_na_homepage`.

Hierarchická záměrně: nehierarchická se edituje jako volný text a student by si ročník
mohl překlepnout a založit duplicitní termín. Takhle je to zaškrtávací seznam.

**Pole na existujících typech:**

| Kde | Pole | K čemu |
|---|---|---|
| `tribe_events_cat` | `barva` (color picker) | barva kategorie, žánry ji dědí |
| `tribe_venue` | `bezbarierovost` (ano/ne) | vyplní se jednou pro 28 míst místo 102× ročně |
| `tribe_events` | `v_carouselu` (ano/ne) | nezávisle na nativním „Featured Event" z TEC |
| `tribe_events` | `bezbarierovost_vyjimka` | zdědit / ano / ne, výchozí zdědit |

## Co čeká na rozhodnutí

**Role Dramaturgie dosáhne i na běžné Příspěvky.** Nejde to obejít — Event Tickets a typ Králové
jedou na generických oprávněních pro příspěvky. Zadání říkalo „pouze program, králové, rezervace",
realita je „to plus aktuality".

**Export účastníků do CSV té roli nefunguje.** Event Tickets ho podmiňuje právem `publish_pages`,
které dramaturgie záměrně nemá. Buď jí práva na stránky dáte, nebo export dělá Admin 1.

**Rodičovské položky v menu nejsou proklikávací.** Program, Partneři, Praktické info a O festivalu
jsou tlačítka bez odkazu — důsledek požadavku „rozbalovací". Samotné URL fungují.
Vrátit zpět je jedno zaškrtávátko u bloku Navigace.

**Rozpad složených hudebních žánrů.** `punk/rock` a podobné jsou rozpadlé na základní žánry,
takže hudební akce ponese víc žánrů místo jednoho. Stará administrace dávala akci právě jeden.

**Šest akcí nemá žánr ani kategorii** — Moon Cat Tattoo (2×), SKATECONTEST, speed friending,
Dance Battle, BudeCirkus.

**Názvy partnerů jsou odvozené z domén.** Loga ve stažené kopii webu nebyla, takže je potřeba
projít hlavně Kredance, Lipno Servis, Perspectives a weby pod `matcb.cz` a `vcb.cz`.

## Opravy dřívější analýzy

Stavba kostry opravila dva údaje z [02-moduly-detail.md](02-moduly-detail.md):

- **Žánrů je 47, ne 159.** Unikátních řetězců je 50, ale v tom jsou varianty podle země
  (`Rap / Česko` vs. `Rap / Slovensko`) a jedno holé „Česko".
- **Seznam duplicit byl z poloviny chybný.** Skutečná duplicita je jen `disco` (dvě ID pod stejnou
  kategorií). `Přednáška` i `Soutěž` se vyskytují jen jednou, `Rozhovor` a `Představení` v datech
  nejsou vůbec.

Zařazení žánrů pod kategorie se nehádalo — odvodilo se spárováním kartiček v `program.html`
s detaily akcí přes ID v odkazu, se stoprocentním pokrytím 96 kartiček.

## Přenesení na staging

Jediný artefakt, který kostru přenese, je [`wordpress/scf-export/bm-scf-all.json`](../../wordpress/scf-export/bm-scf-all.json)
— 7 definic (typ obsahu, taxonomie, 5 skupin polí). Na stagingu se naimportuje v
**SCF → Tools → Import**.

Export nese **jen definice, ne obsah**. Termín ročníku, místa, žánry, stránky a role se musí
založit znovu — buď agentem přes MCP, nebo ručně.

## Co chybí

Import 102 akcí, 20 králů a vzhled (paleta, typografie, kartičky programu).

## Jedno riziko do budoucna

Kdyby někdo přepnul adresu akcí v The Events Calendar na `program` (u českého webu reálné
pokušení), archiv akcí by přebil stránku `/program`. Dnes nekoliduje nic: králové jsou na
`/kral/`, ročníky na `/rocnik/`, archiv králů je vypnutý.
