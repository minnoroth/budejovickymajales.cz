# Budějovický Majáles — pokyny pro práci v repozitáři

Nezisková organizace, studentský festival v Českých Budějovicích. Repozitář drží **analýzu
a stavbu nového webu na WordPressu**, ne aplikaci. Přehled je v [README.md](README.md).

| | |
|---|---|
| Rozestavěný web | <https://novy.budejovickymajales.cz> |
| Starý web | <https://budejovickymajales.cz> |
| Hlavní větev | `main` — práce jde přes pull requesty, větve se po merge mažou |

---

## Jediné kritérium, které rozhoduje spory

Web drží sestava lidí rok, nejvýš dva, a pak se předává dál. Kritérium není „udržovatelné
vývojářem", ale **převzatelné cizím člověkem za jeden večer**.

Když se rozhoduješ mezi elegantnějším řešením a tím, které příští sestava pochopí bez tebe,
ber to druhé. Platí to i pro dokumentaci v tomhle repu.

Prakticky:

- **Žádný vlastní kód nad rámec nutného.** Jediná výjimka je
  [wordpress/mu-plugins/](wordpress/mu-plugins/) a je tam vysvětlené proč.
- **Žádné úpravy šablon pluginů** — zastarají při první aktualizaci.
- **Barvy nikdy natvrdo.** Vždy `var(--global-paletteN)` z palety Kadence.
- Co jde naklikat v Customizeru, patří tam, ne do CSS.
- Když přidáváš vlastní kód, napiš k němu **proč existuje**, ne co dělá.

## Jazyk

**Dokumentace, komentáře a obsah webu jsou česky.** Čte to studentský tým, ne vývojáři.
Zprávy commitů taky česky, bez diakritiky v předmětu (kvůli terminálům), bez jakékoli
atribuce na Claude nebo AI.

## Techstack

WordPress + **Kadence** (téma) + **The Events Calendar**, **Event Tickets**,
**Secure Custom Fields**, **Members**. Všechno ve verzi zdarma.
Hosting **Wedos**, sdílený, PHP 8.3.

Dřívější verze na Next.js + Strapi je smazaná. Když na ni narazíš v historii gitu nebo
ve starších dokumentech, je to slepá větev — nestav na ní.

## Přístupy

V repozitáři nejsou a nebudou — **je veřejný**. Skripty čtou přihlašovací údaje ze souboru,
na který ukazuje proměnná prostředí `BM_WP`:

```
export BM_WP=~/bm-wordpress/staging/wp.txt
```

Soubor má tvar `klíč = hodnota` a obsahuje `web`, `user`, `pass` (aplikační heslo
z Uživatelé → Profil) a `adminpass` (jen pro Customizer). Do repa nepatří ani soubory
fontů s nejasnou licencí — `.gitignore` je blokuje.

---

## Struktura, na kterou míříme

```
docs/wordpress/     číslované dokumenty, 00 je rozhodnutí, vyšší čísla novější
wordpress/
├── vzhled/         skripty, které postaví vzhled od nuly, a zdrojové bm.css
├── mu-plugins/     jediný vlastní PHP kód, každý soubor s odůvodněním
├── branding/       loga a paleta ročníku
├── scf-export/     definice typů obsahu a polí
├── nasazeni/       nahrávání na hosting přes FTPS
└── archiv/         stažení starého webu do statické kopie
```

Pravidla:

- **Nové poznatky jdou do číslovaného dokumentu**, starší se neopravují na místě — doplní se
  do nich odkaz na opravu. Čísla drží časovou osu projektu.
- **`wordpress/vzhled/bm.css` je zdroj pravdy pro CSS.** Když někdo upraví CSS přímo
  v administraci, musí změnu vrátit i sem, jinak ji příští běh `nastaveni.py` přepíše.
- **Vzhled musí jít postavit od nuly** jedním během skriptů ve `wordpress/vzhled/`.
  Když přidáš nastavení ručně v Customizeru, dopiš ho do `nastaveni.py`.
- Delší analýzy a návrhy dělej **ve vlastním worktree na vlastní větvi** a slučuj přes PR.

---

## Gotchas

Všechno níž je vykrvácené v praxi, ne převzaté z dokumentace.

### Customizer a Kadence

- **`customize_save` chce plochou mapu** `{"id_nastaveni": hodnota}`. Obalení do
  `{"value": …}` shodí Kadence na `wp_kses(Array)` a vrátí HTTP 500 bez vysvětlení.
- **Responzivní ovládání Kadence má svůj tvar.** `logo_width` a podobné chtějí
  `{"size": {"mobile": …, "tablet": …, "desktop": …}, "unit": {…}}`. Plochý
  `{"size": 44, "unit": "px"}` se uloží, `setting_validities` vrátí `true` — a **nic
  se nestane**. Validita neznamená, že to zabralo; vždy ověř výsledek na webu.
- **Barevný pás titulku Kadence vykreslí jen v rozvržení `above`.** V `normal` se použije
  jen barva písma, takže bílý nadpis skončí na bílém pozadí. Platí zvlášť pro
  `page_title_layout` a `post_title_layout` — nastav obojí.
- **Kadence hostuje jen ty řezy písma, které něco používá.** Když snížíš váhu nadpisů
  ze 800 na 700, soubor s osmistovkou ze serveru zmizí a prohlížeč začne tučnit falešně.
- Stránka nastavení The Events Calendar se **vykresluje JavaScriptem** — v HTML odpovědi
  pole nejsou, nedá se scrapovat.

### Bloky a CSS

- **WordPress centruje obsah „constrained" skupiny přes `margin-left: auto !important`.**
  Zúžený odstavec se bez `!important` nedá ukotvit vlevo. Lepší řešení: dej maximální šířku
  obalové skupině a jednotlivé bloky nech být — pak nadpisy i odstavce začínají na stejné
  svislici samy.
- **Skupina Kadence má uvnitř ještě `.wp-block-group__inner-container`.** Selektor
  `.moje-skupina > p` tedy nefunguje, musí se počítat s tím mezikrokem.
- **`className` v atributech bloku nestačí.** WordPress vykresluje uloženou HTML, ne atributy,
  takže třída musí být i v samotném `<p class="…">`.
- **`[hidden]` prohrává s `:is(#primary, body)`.** Prohlížeč má na vlastnost `hidden`
  pravidlo `[hidden] { display: none }` s vahou jedné třídy. Protože kvůli Kadence každý
  selektor v našich stylech začíná `:is(#primary, body)`, přebije ho jakékoli naše
  `display: flex`. Prvek je v DOM `hidden = true` a na obrazovce se nic nestane —
  vypadá to, jako by skript nefungoval. Potřebuje vlastní pravidlo se stejným prefixem.

- **Kadence přepisuje hlavičku selektory s víc prvky.** Rozbalovátko si drží
  `.header-navigation .header-menu-container ul ul.sub-menu` — tři třídy a dva prvky.
  Kratší zápis `.site-header .primary-menu-container .sub-menu` má tříd stejně, ale
  prvků míň, takže prohraje a pravidlo se neprojeví. Na hlavičku kopíruj tvar
  selektoru od Kadence a přidej `.site-header` navíc.
- **Zálohu proměnné piš do `var()`, ne jako vlastní deklaraci.** Když má prvek sám
  `--x: záloha`, je to pro něj silnější než hodnota zděděná od rodiče, takže
  zděděná barva nikdy nedorazí. Správně je `var(--x, záloha)` na místě použití.
- **Hlavička je samostatná vrstva, takže `z-index` rozbalovátka platí jen uvnitř ní.**
  Kadence jí dává `#masthead { z-index: 11 }` a `position: relative`; tisícovka
  u `.sub-menu` se pak navenek chová jako jedenáctka a cokoli na stránce s vyšším
  číslem (lepkavá lišta Programu má 60) rozbalovátko překryje. Řeší se zvednutím
  hlavičky, ne rozbalovátka — a selektorem na `#masthead`, aby přebil ten od Kadence.
- **Položka menu, která nemá být odkaz, se dělá sundáním `href`**, ne přes
  `pointer-events` ani JavaScript. `<a>` bez adresy není odkaz: nemá ruku, klepnutí
  nic neudělá, tabulátor ho přeskočí — a `li:hover` pořád funguje, takže
  rozbalovátko Kadence se otevírá dál. V menu se taková položka zadá jako vlastní
  odkaz `#` (prázdnou adresu REST odmítne) a `href` sundá `bm-menu-rozbalovatko.php`.

### Doplňky a vývoj

- **mu-plugins se nenačítají z podsložek.** WordPress bere z `wp-content/mu-plugins/`
  jen soubory `.php` ležící přímo v ní. Doplněk, který má vedle PHP i skripty a styly,
  proto potřebuje v kořeni malý zavaděč — viz `bm-program-nacti.php`. Ten má navíc
  `file_exists`, aby smazání složky neshodilo web na fatální chybu.
- **Všechno ve složce doplňku je na webu veřejné.** `wp-content/mu-plugins/` je pod
  kořenem webu, takže `…/bm-kralove/overeni.php` šlo spustit z prohlížeče a vypsalo celý
  test. Testovací soubory proto začínají `if ( 'cli' !== PHP_SAPI ) { exit; }` a na server
  se nenahrávají.
- **PHP na stroji není, ale Docker ano.** Syntaxi ověříš ve stejné verzi, jaká běží
  na serveru:
  ```
  docker run --rm -v "$PWD/wordpress/mu-plugins:/kod:ro" php:8.3-cli \
    sh -c 'for f in $(find /kod -name "*.php"); do php -l "$f"; done'
  ```
  Dělej to vždycky před nahráním. Syntaktická chyba v mu-pluginu shodí i administraci.
- **Každý dotaz na Wedos stojí přes vteřinu.** Ne kvůli objemu dat — je to režie
  sdíleného hostingu. Při návrhu počítej s počtem koleček, ne s velikostí odpovědi.

### The Events Calendar a Event Tickets

- **REST API ukládá taxonomie až po `save_post`.** Hook na `save_post` tedy při zadávání
  přes API rubriky ještě nevidí. Používej `set_object_terms` (viz
  [wordpress/mu-plugins/bm-rubriky.php](wordpress/mu-plugins/bm-rubriky.php)).
- **Rodičovská rubrika se nezaškrtne sama.** Barva akce v programu se bere z kategorie,
  ne ze žánru — proto ten doplněk výš. Bez něj končí akce bezbarvé.
- **`tribe/events/v1/venues` chce `show_map` jako řetězec `"true"`**, ne jako boolean.
  Boolean skončí na `rest_invalid_param`.
- **Lístky nejsou v `wp/v2/tribe_rsvp_tickets`** (vrací prázdno). Čti je z
  `tribe/tickets/v1/tickets`.
- **Kolekce `wp/v2/tribe_events` skrývá minulé akce.** Na archiv se stovkou vystoupení
  vrátí prázdno a filtr `?rocnik=` na tom nic nezmění. Jednotlivé akce přes
  `wp/v2/tribe_events/<id>` fungují normálně. Na výpis se musí `tribe/events/v1/events`,
  kde je `per_page` zastropované na **50**.
- **RSVP běží od Event Tickets 5.30 na Tickets Commerce**, ne na staré větvi. Lístek má
  `provider: tc` a `type: tc-rsvp`, účastník je `tec_tc_attendee` pod objednávkou
  `tec_tc_order`. Hooky staré větve (`event_tickets_rsvp_*`) se nespustí.
- **Zkratka `[tribe_events]` je jen v placené verzi.** Stránka Program proto na kalendář
  jen odkazuje.
- **Formulář akce předvyplní „Start sale" na zítřek.** Rezervace se pak dnes nikomu
  neukáže a nic na to neupozorní.
- **Textové pole data zůstává v americkém pořadí** i při české lokalizaci: `11/4/2026`
  znamená 4. listopadu. Kalendář sám je česky a začíná pondělím.
- **Časové pásmo webu nastav globálně** (`Europe/Prague`). Když je prázdné, TEC se na pásmo
  ptá u každé akce zvlášť.

### Hosting Wedos

- **Žádné SSH, žádné WP-CLI, žádný vzdálený přístup k MySQL.** Jen FTP, administrace
  a phpMyAdmin na `pma.wedos.net`.
- **FTPS vyžaduje znovupoužití TLS session** (vsftpd `require_ssl_reuse`). `ftplib` to samo
  neumí — hotové řešení je ve [wordpress/nasazeni/upload.py](wordpress/nasazeni/upload.py).
- **Kořen webu je `/www/subdom/novy`**, ne `/subdom/novy`. FTP účet přistává o patro výš.
- **PHP verze je společná pro celý webhosting**, nejde ji přepnout jen pro subdoménu.
- `wp_cron` běží jen při návštěvě stránky — na naplánované úlohy se nedá spolehnout.

---

## Než něco prohlásíš za hotové

Ověř to na živém webu, ne podle toho, že zápis vrátil úspěch. Nejrychleji přes REST
(`/wp-json/wp/v2/…`, `/wp-json/tribe/…`) nebo prohlížečem s parametrem proti cache —
stránky se cachují a vrátí starý stav.
