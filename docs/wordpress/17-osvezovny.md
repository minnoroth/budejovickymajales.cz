# 17. Osvěžovny jako data

> **5. 11. 2026.** Tenhle dokument **obrací rozhodnutí z
> [02-moduly-detail.md](02-moduly-detail.md)**, které osvěžovnám vlastní typ obsahu
> výslovně nedoporučovalo. Důvody jsou níž; námitky z 02 platí a model je musí unést.

---

## Co se změnilo

| | Dřív | Teď |
|---|---|---|
| Kde je nabídka | text stránky `/osvezovny/` | typ obsahu **Osvěžovny**, devět příspěvků |
| Jak se mění | přepsáním HTML v editoru | vyplněním políček |
| Vzhled | `<style>` blok **uvnitř obsahu stránky** | `bm-osvezovny/osvezovny.css` z palety |
| Co drží strukturu | zvyk | datový model |
| Filtr podle druhu | není | *Vše / Nealko / Alko / Jídlo* |

Stránka `/osvezovny/` má nově jen nadpis, úvodní větu a zkratku `[bm_osvezovny]`.

## Proč

Rozhodovalo tohle, ne vzhled:

**Starý web měl barvy zadrátované v obsahu stránky.** V těle stránky byl `<style>`
blok a v něm osmkrát `#0000ff` — loňská majálesová modrá. Rebranding na nový ročník
je u nás „přepsání patnácti polí v Customizeru"; osvěžovny by o té změně nevěděly
a zůstaly modré. To samo stačilo.

**Nabídku přepisuje každý rok někdo jiný, a přepisoval ji jako HTML.** Devět nadpisů
a 24 odrážek, ve kterých je kromě textu i odkaz na mapu, tučné ceny a kurzíva.
Jeden špatně zavřený `</ul>` rozsype zbytek stránky a nic na to neupozorní. Tohle je
přesně ten případ, kdy „převzatelné cizím člověkem za jeden večer" mluví pro model,
ne proti němu: vyplnit políčko *Cena* umí každý, zalomit HTML ne.

**Ceny nešly porovnat.** Byly vlepené do věty („…hruškové pyré a prosecco 155 Kč“).
Teď stojí v pravém sloupci pod sebou.

## Námitky z 02-moduly-detail.md a co se s nimi stalo

Dokument 02 říkal doslova: *„Data NEJSOU uniformní… Rigidní struktura (repeater) by to
rozbila"* a *„Mění se jednou ročně jedním člověkem. Vlastní datový model by se tu nikdy
nevyplatil."* První námitka je věcná a model ji musí unést. Druhá se ukázala jako
špatně odhadnutá.

| Námitka z 02 | Jak to model řeší |
|---|---|
| Klika má jen „Sleva 20 % na vše (heslo: Majáles)", žádnou cenu | **Cena je text a smí být prázdná.** Když je prázdná, prvek s cenou se vůbec nevypíše. Hlídá to test. |
| Jedna položka má dlouhý popis ingrediencí | **Popis je samostatné nepovinné pole** pod názvem, menším písmem. |
| Široko se dělí na „Alko:" a „Nealko:", ostatní ne | **Druh je nepovinný** a nadpisy skupin se objeví jen tam, kde jsou aspoň dva různé. Ze skutečných dat je dostanou tři podniky z devíti — stejně jako dnes. |
| Nabídka se mění jednou ročně | Platí. Jenže ta změna dnes znamená psát HTML, a to je ta drahá část — ne četnost. |

## Rozhodnutí, která z toho plynou

**Druh položky je nepovinný a nikdy se nehádá.** „Květnový infarkt" (yerba maté,
mangový džus, espresso a trochu zlata) nezařadí nikdo. Položka bez druhu se vypíše
normálně, jen se neobjeví pod filtrem. Při plnění dat se vyplnil druh jen tam, kde ho
říká předloha (Široko) nebo samotný název (Kofola, Budvar, gyros, buchty).

**Kartička, která se nedělí aspoň na dva pojmenované druhy, drží pořadí řádků
z editoru.** Nadpis „Nealko" nad třemi drinky nic nerozlišuje a přeskládání podle druhu
by tým mátlo, protože v editoru vidí řádky v jiném pořadí než na webu.

**Žádná vazba na taxonomii Ročník.** U akcí se ukázalo, že tým ročník běžně nezaškrtne
(viz `bm-carousel`), a osvěžovny by se tím vyprázdnily. Devět podniků se jednou ročně
přepíše; historii nabídek nikdo nechce.

**Podniky nemají vlastní stránku** (`public: false`), ze stejného důvodu jako králové:
devět prázdných adres `/osvezovna/<nazev>/` se jmény podniků v Googlu nikdo nechce.

**Podniky stojí ve sloupcích, ne v mřížce** (6. 11. 2026). Mřížka srovnává kartičky
do řad, takže pod tou nejkratší zůstane prázdno až do konce řady — Masné krámy s jednou
položkou vedle Široka se sedmi nechaly přes půl obrazovky bílou. Sloupce (CSS multicol)
pouštějí kartičku rovnou pod předchozí. Daň je pořadí čtení: shora dolů prvním sloupcem,
pak druhým, ne po řadách. Napsané je to i v návodu u pole *Pořadí*. Skutečná mřížka bez
děr (`masonry`) by zachovala čtení po řadách, ale dnes ji drží jen část prohlížečů.

**Filtr je jen vylepšení.** Tlačítka staví JavaScript. Když se nenačte, je vidět celá
nabídka — a žádná mrtvá tlačítka, protože bez skriptu nevzniknou vůbec.

## Kde to žije

| Soubor | K čemu |
|---|---|
| `wordpress/mu-plugins/bm-osvezovny-nacti.php` | zavaděč (mu-plugins nečte podsložky) |
| `wordpress/mu-plugins/bm-osvezovny/bm-osvezovny.php` | typ obsahu, pole, zkratka |
| `wordpress/mu-plugins/bm-osvezovny/osvezovny.css` | vzhled kartiček, barvy z palety |
| `wordpress/mu-plugins/bm-osvezovny/osvezovny.js` | filtr nad výpisem |
| `wordpress/mu-plugins/bm-osvezovny/overeni.php` | testy nad skutečnými daty, běží bez WordPressu |
| `wordpress/vzhled/osvezovny.py` | překlopí stránku 90 |
| `wordpress/vzhled/osvezovny-data.py` | jednorázové naplnění daty z dnešního webu |

Návod pro tým je v [06-navod-pro-tym.md § 7](06-navod-pro-tym.md#7-přidat-osvěžovnu).

## Odchylka, o které je potřeba vědět

**Typ obsahu i pole se registrují v PHP, ne importem do Secure Custom Fields.**
Králové vznikli dřív a jsou v `wordpress/scf-export/bm-scf-all.json`, odkud se
jednou rukou naimportovali v administraci. Osvěžovny takhle nevznikly, protože ten
import se nedá vynutit ani zopakovat skriptem — a co se dělá rukou, to se jednou
udělat zapomene.

Prakticky to znamená:

- Osvěžovny **nejsou vidět** v **SCF → Post Types** ani v **SCF → Field Groups**
  a nedají se tam upravit. Kdo tam půjde hledat, najde Krále a osvěžovny ne.
- Měnit pole se musí v `bm-osvezovny.php` a nahrát přes FTP.
- Zato se nemůže stát, že se definice v repozitáři rozejde s tím, co běží na webu.

Je to **nejednotnost se zbytkem webu** a stojí za to ji jednou srovnat — buď převést
Krále taky do PHP, nebo osvěžovny do exportu. Dokud se to nestane, je to napsané tady
i v [`scf-export/README.md`](../../wordpress/scf-export/README.md).

## Co se tím zahodilo

Původní obsah stránky 90 (devět nadpisů s odrážkami). **Zůstává v revizích stránky
ve WordPressu** — Stránky → Osvěžovny → box *Příspěvek* → *Revize*.

## Dvě pasti, na které se došláplo

**`[hidden]` prohrává s `:is(#primary, body)`.** Filtr schovává položky přes vlastnost
`hidden`. Prohlížeč na ni má pravidlo `[hidden] { display: none }` s vahou jedné třídy,
jenže každý selektor v našich stylech začíná `:is(#primary, body)` kvůli Kadence — takže
`display: flex` tu UA hodnotu přebije a schované položky zůstanou vidět. V DOM je
`hidden = true` a na obrazovce se nic nestane. Řeší to vlastní pravidlo se stejným
prefixem.

**`overeni.php` šlo spustit z prohlížeče.** Testovací soubor se nahrává na server spolu
se zbytkem složky a `https://…/wp-content/mu-plugins/bm-kralove/overeni.php` vracel
celý výpis testů. Oba soubory teď začínají kontrolou `PHP_SAPI`.
