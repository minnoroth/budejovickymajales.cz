# bm-program — program a lineup

Zkratka `[bm_program]` na stránce `/program/`. Návrh, ze kterého to vzniklo, je
v [docs/wordpress/11-lineup-navrh/](../../../docs/wordpress/11-lineup-navrh/) — tenhle
soubor vysvětluje jen **proč je kód takhle rozřezaný**, ne co dělá.

| Soubor | Čí je | K čemu |
|---|---|---|
| `bm-program.php` | datová vrstva | zkratka, zařazení souborů, záchranný blok |
| `program-data.js` | datová vrstva | načtení a úprava dat z REST API |
| `program-view.js` | pohled | vykreslení osy a seznamu |
| `program.css` | pohled | vzhled |
| `overeni.mjs` | datová vrstva | ověřovací skript, do produkce se nenahrává |

## Proč to vůbec existuje

Zkratka `[tribe_events]` je jen v placené verzi The Events Calendar a nativní pohledy
neumí časovou osu patnácti scén vedle sebe. Program je přitom celoročně nejnavštěvovanější
stránka webu, takže na ni nestačí odkaz do kalendáře.

## Proč je PHP tak hloupé

Všechna logika je v JavaScriptu a čte veřejné REST endpointy. PHP nevolá jedinou funkci
The Events Calendar, nesahá na šablony pluginu a nezapisuje do databáze. Rozbít nás umí
jen změna REST API — a ta je vidět hned, protože stránka spadne na záchranný blok.

Záchranný blok s odkazy do kalendáře je v HTML **vždycky**, ne ve `<noscript>`. Kryje tím
tři selhání naráz: vypnutý JavaScript, chybu ve skriptu i nedostupné REST API. Skript ho
odstraní, teprve až se program opravdu vykreslí.

> **Nativní pohledy The Events Calendar se proto nikdy nevypínají.** `/events/list/`,
> `/events/month/` a `/events/today/` musí zůstat funkční — jsou to ty odkazy v záchranném
> bloku. Vyndat z menu ano, vypnout ne.

## Proč je vedle složky ještě `bm-program-nacti.php`

WordPress načítá z `mu-plugins/` jen soubory `.php` ležící přímo v ní — do podsložek se
nedívá. Bez zavaděče by se `bm-program/bm-program.php` nikdy nespustil. Zavaděč má
podmínku `file_exists`, aby test izolace („smaž složku a web jede dál") neshodil celý web
na fatální chybu.

## Proč je načítání dat ve vlastním souboru

`program-data.js` je jediné místo, kde se potkáváme s The Events Calendar. Až TEC
přejmenuje endpoint nebo Secure Custom Fields přestane vracet klíč `acf`, opraví se to
tam a nikde jinde. Pohled o REST API neví.

Funkce vrací přesně ten tvar, jaký má zmrazená kopie dat v prototypu — návrh s tím
počítá větou „na ostrém webu se vymění jediná funkce `nactiData()`":

```js
const { rocnik, akce, kategorie, mista } = await window.bmProgram.nactiData();

rocnik     // { id, name: '2026', datum_od: '2026-05-31', datum_do: '2026-06-06' } nebo null
akce       // syrové akce z tribe/events/v1/events, jen s dekódovanými názvy
kategorie  // [{ id, name, slug, parent, acf: { barva } }]
mista      // [{ id, venue, slug, url, acf: { bezbarierovost } }]
```

Navíc je na `window.bmProgram`:

```js
HRANICE_DNE      // 6 — hodina, kterou končí festivalový den
klicDne(datum)   // Date → 'YYYY-MM-DD' s tou hranicí
klicDneAkce(a)   // totéž pro jednu akci; celodenní akce hranici neposouvá
odstranEntity(s) // dekódování HTML entit bez DOM
zaklad           // základ REST API, dosazuje bm-program.php
```

**Prázdné `akce` není chyba**, ale ročník bez programu — stránka na to má stav „Program
se připravuje". Chybu `nactiData()` vyhodí jen tehdy, když selže dotaz na ročníky nebo na
akce; pohled ji nechytá do ztracena, ale nechá na stránce záchranný blok.

Když selže jen `wp/v2/tribe_events_cat` nebo `wp/v2/tribe_venue`, program běží dál bez
barev a bez bezbariérovosti. Návrh to v tabulce rizik výslovně připouští — barvy nejsou
důvod, proč shodit celou stránku.

## Pasti, které už nás stály čas

Všechno níž je vykrvácené proti živému API, ne převzaté z dokumentace.

- **`per_page` u kalendáře je zastropované na 50.** Požádat o 100 projde, ale odpověď
  vrátí padesát a v `rest_url` je `per_page=50`. Sto akcí = tři dotazy přes `?page=`.
  Počet stránek se bere z `total_pages` v těle odpovědi — **stránka za koncem vrací 404**,
  takže se na ni nedá narazit „naslepo".
- **`wp/v2` má strop 100** a chová se jinak: stránka za koncem vrací 400, konec se pozná
  podle kratší odpovědi.
- **Konec časového rozsahu si TEC přepíše na `23:59:59`.** Pošleme `datum_do + 1 den 06:00`
  a v `rest_url` se vrátí `23:59:59`. Hranici 6:00 proto musí dodržet až klient — jinak by
  do programu spadlo, co se děje den po festivalu, a vyskočila by osmá záložka dne.
  Posílat rovnou `datum_do` ale nejde: vypadly by ranní sety po půlnoci, přesně ty tři na
  K2, které ztratila i stará osa.
- **Ořezává se jen konec rozsahu, ne začátek.** Co začne mezi půlnocí a šestou prvního dne,
  v programu zůstane, i když mu hranice dne dá klíč předchozího dne. Ztratit akci je horší
  než ukázat navíc jednu záložku.
- **Celodenní akce dostává od kalendáře začátek 00:00.** Hranice 6:00 na ni proto platit
  nesmí — jinak spadne celá o den zpátky. Není to teorie: všech pět dílů výstavy ŠIROKO
  HLUBOKO v ročníku 2026 začíná v 00:00, takže by se výstava ukazovala o den dřív a před
  prvním dnem festivalu by vyskočila prázdná sedmá záložka. Řeší to `klicDneAkce()`.
- **Secure Custom Fields vrací datum jako `20270509`**, kdežto prototyp má `2027-05-09`.
  Obojí se přijímá, ven jde vždycky ISO.
- **Místo ve `wp/v2/tribe_venue` je obyčejný příspěvek** (`title.rendered`, `link`), ne tvar
  kalendáře (`venue`, `url`). Sjednocuje se to v datové vrstvě.
- **Názvy chodí jako HTML** — `Žižkárna &#8211; garáž`. Bez dekódování je entita vidět
  v programu.
- **`image` může být `false`**, ne objekt. A pole `_bm_rezervace`, které si vymyslel
  prototyp, ve skutečných datech není — free verze Event Tickets rozdíl mezi dobrovolnou
  a povinnou rezervací neposílá.
- **Výběr ročníku se dělá přes termín, ne přes taxonomii.** `wp/v2/tribe_events` filtruje
  na nadcházející akce, takže na archiv vrátí prázdno, a `count` u termínu počítá i jiné
  typy obsahu. Jediné spolehlivé je zeptat se kalendáře na rozsah `datum_od`–`datum_do`.

## Jak to ověřit, když je na webu málo dat

```bash
cd wordpress/mu-plugins/bm-program
node overeni.mjs          # plný program ročníku 2026 ze zmrazených dat prototypu
node overeni.mjs --zivy   # živé REST API, jen dotazy GET
```

První běh prožene datovou vrstvu 102 vystoupeními a 28 místy a kontroluje mimo jiné to, že
počty akcí a scén po dnech sedí na tabulku z návrhu. Druhý běh ověřuje mechaniku proti
skutečnému API a na webu nic nemění.

`overeni.mjs` je vývojářský nástroj — **na hosting se nenahrává.**

## Jak ověřit, že doplněk žije

1. `/program/` se načte a **nevidíš** záchranný blok s odkazy do kalendáře.
2. Přepnutí dne mění obsah.
3. Smaž na hostingu složku `bm-program/`: na stránce zůstane text `[bm_program]` a web
   i administrace jedou dál.
