# Carousel na úvodu a výpis králů — analýza

Podklad pro implementaci dvou chybějících modulů. Vznikl 5. 11. 2026 po testování,
při kterém zadavatel narazil na to, že přepínač „V carouselu" nic nedělá a že se
králové nikde nezobrazují.

**Stav: analýza. Nic z toho není naprogramované ani nasazené.**

---

## Shrnutí na tři věty

Oba moduly mají **hotový datový model a chybějící výstup** — data v administraci jsou,
jen je nikdo nečte a nevykresluje. Carousel navíc naráží na to, že se o „co je na úvodu
důležité" dnes perou **dva nezávislé přepínače** a jen jeden z nich je na něco napojený.
U králů je model hotový včetně bodů po segmentech, ale výpis bude muset vzniknout od
nuly a musí si pohlídat tři věci ze starého webu, na které se odkazuje okolí.

| | Datový model | Administrace | Výstup na webu |
|---|---|---|---|
| Carousel | ✅ pole `v_carouselu` | ✅ přepínač u akce | ❌ nic ho nečte |
| Králové | ✅ typ `kral` + body + ročník | ✅ funguje, data zadaná | ❌ nic ho nečte |

---

# Část A — Carousel

## A.1 Co je dnes na úvodní stránce

Sekce **„Nepřehlédni"** jsou tři **natvrdo napsané kartičky** v
[`wordpress/vzhled/uvod.py`](../../wordpress/vzhled/uvod.py):

```python
karta(Z, "Volba krále",     "neděle 18:00", "Piaristické náměstí", "Zahájení a představení králů"),
karta(K, "Venkovní scéna",  "neděle 11:00", "Široko",              "Výstava ŠIROKO HLUBOKO"),
karta(L, "Hudba",           "neděle 20:00", "Piaristické náměstí", "LOUD DISCO", "Nutná rezervace"),
```

Nejsou to data. Je to text, který někdo přepsal rukou z programu. Když se akce zruší
nebo přesune, úvodní stránka o tom neví.

Totéž o kus níž u dlaždic „Co tě zajímá?" — počty akcí jsou natvrdo `43 / 22 / 14 / 7`,
zatímco ve skutečnosti je to `42 / 18 / 7 / 7`. **Úvodní stránka dnes lže ve čtyřech
číslech.**

## A.2 Proč přepínač nefunguje

Ověřeno v datech 5. 11. 2026: u akce *LOUD DISCO* (id 196) má pole `v_carouselu`
uloženou hodnotu `false`. Pole existuje a je vidět v REST API. **Na webu ho ale nečte
vůbec nikdo.** Jestli se zaškrtnutí ukládá správně, je otevřená otázka — viz otázka 1.

Chybí tedy frontend, ne pole. Jedna věc se ale musí ověřit u zadavatele, než se začne
psát kód — viz otázka 1 níž: hodnota je dnes `false`, takže buď se zaškrtnutí neuložilo,
nebo se zaškrtnutí vrátilo zpět.

## A.3 Past, kterou jsem našel: dva přepínače na jednu věc

| Přepínač | Kde se nastavuje | Co dnes dělá |
|---|---|---|
| **Top představení** (`featured`, nativní v The Events Calendar) | Akce → Event Options | **čte ho stránka Program** — `program-view.js:191`, kreslí štítek „Top" |
| **V carouselu** (`v_carouselu`, pole SCF) | Akce → box „Akce – nastavení webu" | **nečte ho nic** |

V datech ročníku 2026 má `featured = true` pět akcí a `v_carouselu` nemá ani jedna.

Pro člověka, který web převezme, to znamená dvě zaškrtávátka se skoro stejným významem,
z nichž jedno je tiše mrtvé. To je přesně ten typ věci, kvůli které se v předávačce
někdo zasekne na hodinu. **Doporučuju je sloučit** — viz otázka 2.

Je v tom i technický rozdíl, který rozhoduje o tom, jak se carousel dá vůbec napsat:

| | čte ho `tribe/events/v1` (JS) | čte ho PHP |
|---|---|---|
| `featured` | ✅ ano, je v odpovědi | ✅ |
| `v_carouselu` | ❌ **ne** — ACF pole tam není; `custom_fields` je u všech 101 akcí prázdné pole (ověřeno) | ✅ |

Zůstat u `v_carouselu` tedy **vylučuje** řešení v JavaScriptu nad stejným API, jaké
používá stránka Program. (`wp/v2/tribe_events` sice ACF vrací, ale skrývá proběhlé akce —
známá past, je v `CLAUDE.md`.)

## A.4 Možnosti

### Varianta 1 — nativní Query Loop blok, žádný kód
**Nejde.** Query Loop umí filtrovat podle taxonomie, ne podle pole ACF
([`02-moduly-detail.md`](02-moduly-detail.md) ř. 288). Navíc by neuměl vytáhnout čas
a místo z kalendáře a neumí řadit podle data akce, protože to je u TEC v meta poli.

### Varianta 2 — přepínač jako termín taxonomie `oznaceni`
Query Loop by filtrovat uměl. Ale pořád neumí kartičku s časem, místem a barvou
kategorie, a neumí řadit podle data akce. Vyřeší čtvrtinu problému a přidá další
taxonomii k vysvětlování. **Nedoporučuju.**

### Varianta 3 — zkratka `[bm_carousel]` v PHP, vykreslená na serveru ⭐
Malý must-use doplněk, `WP_Query` nad `tribe_events` s meta dotazem, výstup je
vodorovný pruh kartiček se `scroll-snap-type: x mandatory`. Žádná JS knihovna.

- čte obě varianty příznaku, takže volba v otázce 2 ho nerozbije
- vykreslí se na serveru → úvodní stránka není při načtení prázdná a vidí to i vyhledávač
- `scroll-snap` = nativní swipe na mobilu, odkud chodí 80 % návštěv

### Varianta 4 — JavaScript, znovu použít datovou vrstvu `bm-program`
Jedna datová vrstva pro program i úvod. Jenže: nejdůležitější sekce úvodní stránky by
byla při načtení prázdná a doplnila by se až po dvou dotazech do REST API, a s polem
`v_carouselu` to vůbec nejde (viz tabulka výš).

**Doporučení: varianta 3.**

Cena, kterou je za to potřeba přiznat: kartička akce pak existuje dvakrát — jednou v PHP
pro úvod, jednou v JS v `bm-program`. Je to vědomá duplicita, ne opomenutí. Alternativa
(jedna kartička v JS) znamená prázdný úvod při načtení, což je horší.

## A.5 Co carousel potřebuje a nemá: obrázky

**Ani jedna ze 101 akcí ročníku 2026 nemá náhledový obrázek.** Ověřeno v datech.

Carousel bez obrázků je pruh barevných obdélníků s textem. Dnešní kartička na úvodu to
řeší tak, že místo fotky dá plochu v barvě kategorie — vypadá to záměrně a není to
ostuda, ale **není to to, co zadavatel myslí, když řekne „carousel ve stylu
dikyzemuzem.cz"**.

Je to úkol pro obsah, ne pro kód. Zmiňuju to, aby po nasazení nepřišlo zklamání:
*carousel bude fungovat a bude vypadat chudě, dokud někdo nenahraje fotky.*

## A.6 Co se musí dořešit v kódu

| Věc | Jak to navrhuju |
|---|---|
| Co se ukáže, když ročník skončil | Poslední ročník, který akce má — stejné pravidlo jako na stránce Program. Prázdný carousel se celý skryje, nenechá po sobě nadpis. |
| Kolik kartiček | Nejvýš 8, seřazené podle data akce. Návod už dnes radí „nedávej do něj všechno". |
| Akce bez příznaku | Když nemá příznak žádná akce, sekce se skryje. Nenahrazovat ji náhodnými akcemi — tichá automatika se špatně ladí. |
| Klávesnice a odečítač | Pruh `tabindex="0"` s `aria-label`, kartičky jsou odkazy. Scroll-snap sám o sobě ovladatelný z klávesnice není. |
| `prefers-reduced-motion` | Žádné samovolné posouvání. Carousel se hýbe jen na pokyn návštěvníka. |

---

# Část B — Výpis králů

## B.1 Co je hotové

Datový model je **kompletní a správný** — zadavatel to ověřil při testování:

```
typ obsahu  kral
  titulek     = jméno kandidáta
  náhledový obrázek = fotka
  skola       text
  tema        text
  body        repeater → { segment: select, pocet: number }
  taxonomie   rocnik
```

Na webu je dnes jeden kandidát: *Daniel Neubauer*, ČAG, téma Barbie, ročník 2027,
body 100 + 200 + 400 + 90 = **790**.

## B.2 Co chybí

Stránka `/kralove/` (id 85) obsahuje dva odstavce zástupného textu a **žádný výpis**.
Na úvodní stránce je korálový pás „Volba krále" s tlačítkem na `/kralove/`, taky bez
jediného jména.

## B.3 Co musí zůstat ze starého webu

Stáhl jsem a rozebral dnešní `budejovickymajales.cz/kralove`. Tři věci se odtamtud
musí přenést, protože na nich visí okolí:

**1. Kotvy podle školy.** Každý kandidát má `<a name="gymji">` a školy i Instagram
odkazují na `/kralove#gymji`. Ověřeno u všech 20 položek: kotva = název školy bez
diakritiky, malými písmeny, mezery na pomlčky (`Husova 9` → `husova-9`).

> **Pozor:** pravidlo není jednoznačné. Šest záznamů na starém webu má školu `t`, takže
> sdílejí jednu kotvu. Nový kód musí při shodě přidat pořadové číslo, jinak odkaz
> skočí na náhodného kandidáta.

**2. Pruh v poměru k vítězi.** `width: 100%` u prvního, `91.08 %` u druhého —
tedy `body / nejvyšší_body × 100`. Ne podíl z celkového součtu.

**3. Značkování pro vyhledávače.** `schema.org/ItemList` s `position`, `name`,
`image`, `url` u každé položky.

Doporučuju převzít i původní třídy (`kings`, `photo`, `info`, `name`, `school`,
`chart`, `progress`, `votes`) — starý web na nich má styly a usnadní to srovnávání.

## B.4 Body: jedno číslo, nebo rozpad po segmentech?

Tady si dva zdroje protiřečí a je to **otázka na zadavatele** (otázka 4):

- **Starý web** ukazuje jedno číslo (`5 898`). Totéž říká
  [`00-analyza.md`](00-analyza.md) ř. 190: *„Body králů jako jedno číslo, ne rozpad
  po segmentech."*
- **Nový model** body po segmentech už sbírá a zadavatel je u testovacího kandidáta
  vyplnil všechny čtyři.

Návrh: **součet velkým písmem, rozpad až v detailu kandidáta.** Na žebříčku, kde je
dvacet jmen pod sebou, je osmdesát čísel nečitelných; v detailu je rozpad zajímavý
a vysvětluje, proč někdo vede.

## B.5 Součet: počítat při výpisu, nebo ukládat?

| | Počítat při každém výpisu | Ukládat do `body_celkem` při uložení |
|---|---|---|
| Kód | méně, žádný háček | háček na `save_post` |
| Řaditelný sloupec v administraci | **nejde** | jde |
| Riziko | žádné | součet se rozejde s body, když někdo upraví data mimo editor |

Zadání chce **řaditelný sloupec „Body celkem"** jako parita s dnešním sloupcem Hlasy,
a to bez uloženého čísla nejde. Navrhuju tedy ukládat — a přidat tlačítko
„přepočítat vše" pro případ, že se to rozejde.

> Pozor na pořadí háčků: u barev kategorií se tahle věc už jednou vymstila.
> `save_post` běží u REST **dřív** než se uloží pole SCF, takže součet se musí počítat
> z háčku, který běží až po nich (`acf/save_post` s prioritou > 10).

---

# Část C — Co jsem našel mimo zadání

## C.1 Export datového modelu je prázdný ⚠️

`wordpress/scf-export/bm-scf-all.json` má u **všech sedmi skupin** `fields: []`:

```
Králové                      fields: chybí
Ročníky                      fields: chybí
Akce – nastavení webu        fields: 0
Kategorie programu – barva   fields: 0
Král – detail kandidáta      fields: 0
Místo – bezbariérovost       fields: 0
Ročník – termín festivalu    fields: 0
```

README u souboru přitom tvrdí, že obsahuje definice datového modelu a že se z něj dá
model obnovit. **Nedá.** Import vyrobí sedm prázdných skupin — žádné `barva`, `body`,
`skola`, `tema`, `v_carouselu`, `datum_od`.

Zrovna u králů na tom záleží: celý modul stojí na repeateru `body`, a ten v záloze není.
Navrhuju export opravit dřív, než se začne psát `[kralove]`.

## C.2 Na veřejném webu je šest cizích záznamů ⚠️

Mezi dvaceti kandidáty na `budejovickymajales.cz/kralove` je šest bez fotky, s nulou
bodů a se jmény:

```
s1_test    s2_phtml    gif_pure    double_ext    path_test    curl_test_final
```

`gif_pure`, `double_ext` a `s2_phtml` jsou **standardní názvy testů na obcházení kontroly
přípon při nahrávání souborů**. Někdo zkoušel, jestli jde přes formulář kandidáta nahrát
spustitelný soubor. Jestli to byl bezpečnostní audit, nebo někdo cizí, se z webu poznat
nedá.

Pro nás to má dva dopady:

1. Ty záznamy jsou **veřejně vidět teď** — patří smazat bez ohledu na migraci.
2. Nový formulář kandidáta bude přijímat fotky. Stojí za to vědět, že se tohle na starém
   webu už jednou zkoušelo. WordPress kontrolu přípon dělá sám a je na to spolehlivější
   než vlastní skript, ale nahrávání nedávat roli, která ho nepotřebuje.

**Není to úkol téhle analýzy**, jen jsem na to narazil a mlčet se o tom nedá.

## C.3 Úvodní stránka má čtyři nepravdivá čísla

Dlaždice „Co tě zajímá?" tvrdí `43 / 22 / 14 / 7 akcí`, skutečnost je `42 / 18 / 7 / 7`.
Když se bude sahat na úvod kvůli carouselu, je to oprava na pár řádků — čísla se dají
spočítat ze stejného dotazu.

---

# Část D — Dílčí celky a odhad

Pořadí je zvolené tak, aby se dalo kdykoli zastavit a to, co je hotové, mělo smysl samo
o sobě.

| # | Celek | Odhad |
|---|---|---|
| 1 | Oprava exportu SCF (C.1) — vyexportovat skupiny včetně polí, ověřit reimportem | 1 h |
| 2 | Doplněk `bm-kralove`: dotaz podle ročníku, součet bodů, háček na `acf/save_post`, řaditelný sloupec | 3 h |
| 3 | Zkratka `[kralove]`: karty, pruh v poměru k vítězi, kotvy podle školy vč. ošetření shody, schema.org | 4 h |
| 4 | Styl výpisu králů z palety ročníku, mobil | 2 h |
| 5 | Doplněk `bm-carousel` + zkratka: dotaz podle příznaku, řazení podle data, prázdný stav | 3 h |
| 6 | Styl carouselu (scroll-snap, klávesnice, `prefers-reduced-motion`) | 2 h |
| 7 | Nahradit natvrdo psané kartičky a čísla na úvodu (A.1, C.3) | 1 h |
| 8 | Sjednocení přepínačů podle odpovědi na otázku 2 | 0–2 h |
| 9 | Doplnit návod pro tým o obě sekce | 1 h |
| | **Celkem** | **17–19 h** |

Celky 2–4 (králové) a 5–7 (carousel) na sobě nezávisí a dají se dělat souběžně, každý
ve vlastním worktree. Reálný čas na hodinách je pak zhruba **10–12 h**, ne 17–19.

Na „dneska" to i tak nevyjde celé. Nejrozumnější řez: **celek 1** (hodina, a chrání
všechno ostatní), pak **králové** (celky 2–4) — ty mají hotová data a prázdnou stránku,
na kterou dnes vede tlačítko z úvodu. Carousel čeká na odpověď k otázce 2 a bez fotek
stejně nebude vypadat, jak má.

---

# Část E — Otázky, které potřebuju zodpovědět

**1. Carousel — uložil se ti ten přepínač?**
U akce *LOUD DISCO* je dnes `v_carouselu: false`. Zaškrtl jsi ho a ono se to neuložilo,
nebo jsi ho zase odškrtl? Je v tom rozdíl mezi „chybí frontend" (pak je to 5 h práce)
a „navíc se špatně ukládá" (pak je to chyba v modelu a musím ji najít dřív).
*Nezkoušel jsem to přepsat sám, abych ti nesahal na testovací data.*

**2. Mají zůstat dva přepínače, nebo jeden?**
Dnes jsou „Top představení" (čte ho Program) a „V carouselu" (nečte ho nic).
→ **Doporučuju jeden** — zrušit `v_carouselu` a používat nativní „Top představení".
Ubyde jedno pole, ubyde vysvětlování v návodu a carousel pak může číst stejné API jako
Program. Proti: ztratíš možnost mít akci v programu nahoře, ale ne na úvodu.

**3. Kam přijde výpis králů?**
→ **Doporučuju na `/kralove/` celý žebříček a na úvod první tři + tlačítko.**
Úvodní pás „Volba krále" dnes vede na prázdnou stránku, což je horší než nic.

**4. Body — jedno číslo, nebo i rozpad po segmentech?**
→ **Doporučuju součet v žebříčku, rozpad v detailu kandidáta** (viz B.4).

**5. Je to opravdu všechno?** Co podle mě k věci patří a v zadání nebylo:
- natvrdo psané kartičky „Nepřehlédni" na úvodu (A.1) — carousel je má nahradit
- čtyři nepravdivá čísla v dlaždicích (C.3)
- prázdný export datového modelu (C.1) — u králů to je riziko, ne kosmetika
- šest cizích záznamů na starém webu (C.2) — mimo tuhle práci, ale ať o nich víš

**6. Styl carouselu.** Grafika ročníku 2027 přijde až na konci roku 2026. Postavím ho
tedy na paletě 2024 jako zbytek webu, nebo na něj počkáme?
→ **Doporučuju stavět teď** — barvy jsou v paletě, překlopení je patnáct polí.

**7. Co s polem „Barva" u kategorie?**
Při opravě barev (5. 11.) se všech devět polí vyprázdnilo a zdrojem barvy je nově
tabulka v `program.css` odvozená z palety. Pole tím zůstalo v administraci jako mrtvé
místo — a hůř, **kdo do něj něco napíše, přebije paletu a rozbije příští rebranding**,
aniž by mu to kdokoli řekl.

Tři cesty:

| | Co to znamená | Rebranding projde | Tým si barvu změní sám |
|---|---|---|---|
| nechat prázdné (dnešní stav) | pole svádí k chybě | ✅ | ❌ |
| **pole zrušit** | o jedno políčko v administraci míň | ✅ | ❌ |
| **pole přepsat na výběr slotu palety** (`palette1`…`palette15`) | ~2 h: změna typu pole + překlad slotu na barvu ve výpisu | ✅ | ✅ |

→ **Doporučuju výběr slotu.** Drží obě vlastnosti najednou a je to jediná varianta,
kde „rebranding = patnáct polí" platí bez výjimky i ve chvíli, kdy někdo barvu změní.
