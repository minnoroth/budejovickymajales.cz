# Struktura webu a architektura programu — otevřená rozhodnutí

Zápis z 4. 11. 2026. **Nic z toho zatím není nasazené.** Je to zadavatelova zpětná vazba
na první verzi webu, moje námitky k ní a to, co z toho zbylo jako návrh.

---

## A. Menu

Dnešní menu zdědilo hierarchii starého webu: šest rozbalovátek, devatenáct položek.
Zadavatel: *„V menu je momentálně hrozně akcí a je to nepřehledné. Na mobilu to bude
úplné peklo."*

### Návrh

> **Program · Lineup · Osvěžovny · Králové · Praktické info · O festivalu**
> \+ tlačítko **Podpoř nás**

| Dnes | Potom |
|---|---|
| 6 rozbalovátek, 19 položek | 6 položek, 1 rozbalovátko |
| Osvěžovny schované pod Partnery | samostatná sekce |
| Lineup schovaný pod Programem | samostatně |
| Program = slepá mezistránka | Program = rovnou výpis akcí |
| Tlačítko E-shop | Tlačítko Podpoř nás |

Jediné rozbalovátko zůstává u **O festivalu**: O nás, Historie, Dobrovolníci, Fotogalerie,
Pro média, Kontakt, Partneři.

### Rozhodnuto

- **`/program/prehled` se smaže.** Stránka „Přehled programů" je pozůstatek starého webu
  a nikdo neumí říct, co na ní má být.
- **Osvěžovny jdou do hlavní úrovně.** Pod Partnery byly nesmysl — je to informace
  pro návštěvníka, ne pro sponzora.
- **E-shop ustupuje tlačítku Podpoř nás.** Pro neziskovku je to silnější výzva.
  Na stránce Podpoř nás bude Donio a pod ním odkaz na e-shop. Vědomý ústupek: e-shop
  jde o jeden klik dál.

### Námitky, které zůstaly otevřené

**Partneři nemůžou zmizet úplně.** Zadavatel chtěl celou sekci zrušit. Loga partnerů
na webu ale bývají součástí smlouvy o partnerství — zrušením sekce se může porušit dohoda,
o které nevíme. Návrh: stránka zůstane, jen vypadne z hlavního menu (patička + O festivalu).
**Ověřit u vedení.**

**Praktické info nepatří pod O festivalu.** Zadavatel to tam chtěl přesunout. Je to ale
stejná chyba jako Osvěžovny pod Partnery: *Praktické info* je pro návštěvníka (doprava,
přístupnost, parkování), *O festivalu* je o organizaci. Co zadavatele skutečně štve, je
rozbalovátko se čtyřmi položkami. Návrh: **sloučit ty čtyři podstránky do jedné stránky
s kotvami.** Zmizí rozbalovátko, nic se nezahrabe.

**Donio jako iframe ne.** Dnešní vložení má natvrdo výšku 4000 px a na mobilu je rozbité
(viz [02-moduly-detail.md](02-moduly-detail.md)). Návrh: velká karta s odkazem a stavem
sbírky, iframe jen pokud Donio nabízí vložení s rozumnou výškou. **Ověřit.**

---

## B. Architektura programu

Zadavatel po prvním proklikání: *„Events je docela pekelnej. Nedá se něčím nahradit?
Custom se mi to fakt dělat nechce."* A vzápětí návrh, který to celé otáčí.

### Dvojí klikání

Cesta úvod → **Prohlédnout program** → `/program/` → **Otevřít kalendář** → `/events/`.
Dva kliky k tomu, aby člověk uviděl program. Na `/program/` přitom není nic než nadpis
a tlačítko.

**Řešení: přestěhovat výpis akcí na `/program/`.** V The Events Calendar se dá přepsat
adresa archivu i jednotlivé akce:

- `/program/` = rovnou výpis
- `/program/mesic/`, `/program/den/` = pohledy
- `/akce/<nazev>/` místo `/event/<nazev>/`

Podmínka: nejdřív smazat prázdnou stránku `/program/`, jinak kolidují.
V [05-kostra.md](05-kostra.md) je tahle kolize vedená jako riziko — platilo to pro
nechtěný přesun, ne pro vědomý.

### Sjednocení Programu a Lineupu

Zadavatelův návrh, a je dobrý:

> Jedna stránka, vždy **jeden konkrétní den**. Nahoře přepínač dnů, vedle něj přepínač
> zobrazení **Lineup** (časová osa s paralelními scénami) nebo **Seznam** (akce pod sebou).
> Filtry platí pro obojí. Výchozí stav je aktuální den.

Proč to dává smysl: lineup a program nejsou dvě různé informace, jsou to dva pohledy
na tutéž věc. Omezení na jeden den navíc řeší půlku problému s mobilem — sedm scén
v ose je ještě únosné, dvacet osm ne.

**Nevyřešené:** 11 měsíců v roce žádný „aktuální den festivalu" neexistuje. Co se ukáže
mimo festivalový týden, musí návrh dořešit.

### Co s The Events Calendar

Zadavatel nechce mapu („stačí proklik do Google Maps") ani kalendářové pohledy a chce
vlastní filtraci. Otázka tedy je, jestli plugin ještě něco přináší.

**Tři cesty:**

| | Co to je | Co se získá | Co se ztratí |
|---|---|---|---|
| **1. Zůstat** | Přestylovat CSS, žít s šablonami | Nic nového | Pořád cizí šablony a žádná vlastní filtrace |
| **2. Hybrid** | TEC jako databáze a administrace, vlastní stránka programu | Plná kontrola nad vzhledem a filtry, administrace a místa fungují dál | Nic podstatného |
| **3. Odejít** | Vlastní typ obsahu, žádný kalendářový plugin | Nejlehčí web, nejvíc volnosti | Administrace akcí, správa míst, SEO značkování, export do kalendáře, rezervace |

**Doporučení: cesta 2.** Dává zadavateli všechno, co chce, a nebere nic. Akce dál žijí
v The Events Calendar (datum, místo, rubriky, administrace, kterou tým už zná), ale
návštěvník vidí jen naši vlastní stránku. Vlastní pohledy pluginu se skryjí.

Cesta 3 navíc znamená postavit znovu i to, co dnes funguje: formulář pro zadání akce,
typ obsahu pro místa, značkování pro vyhledávače, export do kalendáře. A hlavně —
**je to okamžik, kdy projekt přechází ze „sestavený" na „naprogramovaný"**, čemuž se
celé zadání od začátku vyhýbá.

### Kde je hranice

Vlastní stránka programu je obhajitelná: je to **jedna stránka, jeden soubor dat, jedna
komponenta**. Když se rozbije, data jsou v pořádku a pohledy pluginu existují jako záloha.

Vlastní rezervační systém je o patro výš — drží osobní údaje, posílá e-maily a selhává
tiše. Tohle rozhodnutí by nemělo padnout mimochodem při řešení vzhledu.

---

## Na co se čeká

1. **Tým CSS** ukáže, kolik z ošklivosti výpisu byl jen styl. To je nejlevnější odpověď
   na otázku „je Events pekelný".
2. **Tým lineupu** dostal sjednocení do zadání a dořeší výchozí den.
3. **Tým rezervací** dostal do zadání i variantu bez The Events Calendar a řekne, co
   která cesta stojí.

Teprve nad těmi třemi výsledky má smysl rozhodnout o architektuře.
