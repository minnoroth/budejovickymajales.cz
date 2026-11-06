# Návod pro tým — jak plnit web Majálesu

Tenhle návod je pro lidi z organizačního týmu, kteří budou na webu Budějovického Majálesu
přidávat program, krále, aktuality a texty. **Nemusíš umět nic technického.** Stačí číst
a klikat podle kroků.

Web běží na WordPressu. To je systém, kde se obsah píše do formulářů a on z toho sám
poskládá stránky. Nic se nepřepisuje v kódu.

> **Jak číst názvy tlačítek.** Administrace se právě překlápí do češtiny. Aby návod fungoval
> před i po, píšeme u každé položky menu český název a za ním v závorce anglický originál —
> třeba „Akce (Events)". Klikej na to, co vidíš na obrazovce; jedno z těch dvou to bude.

> **Značka [jen Admin]** u kroku znamená, že na to má práva jen správce webu. Když to v menu
> nevidíš, nic jsi nerozbil/a — jen na to nemáš přístup. Napiš správci.

---

## Obsah

- [Nejčastější úkoly — rychlý rozcestník](#nejčastější-úkoly--rychlý-rozcestník)
- [1. Přihlášení a orientace](#1-přihlášení-a-orientace)
- [2. Přidat akci do programu](#2-přidat-akci-do-programu)
- [3. Přidat místo konání](#3-přidat-místo-konání)
- [4. Žánry a kategorie](#4-žánry-a-kategorie)
- [5. Zapnout rezervace na akci](#5-zapnout-rezervace-na-akci)
- [6. Přidat krále a zapsat body](#6-přidat-krále-a-zapsat-body)
- [7. Přidat osvěžovnu](#7-přidat-osvěžovnu)
- [8. Napsat aktualitu](#8-napsat-aktualitu)
- [9. Upravit text stránky](#9-upravit-text-stránky)
- [10. Nahrát obrázky](#10-nahrát-obrázky)
- [11. Nastavit nový ročník](#11-nastavit-nový-ročník)
- [Co nedělat](#co-nedělat)
- [Když něco nefunguje](#když-něco-nefunguje)

---

## Nejčastější úkoly — rychlý rozcestník

| Potřebuju… | Kam jít | Sekce |
|---|---|---|
| přidat koncert, divadlo, workshop | Akce (Events) → Přidat akci (Add New Event) | [2](#2-přidat-akci-do-programu) |
| opravit čas nebo místo u akce | Akce (Events) → Akce (Events) → klik na název | [2](#2-přidat-akci-do-programu) |
| zrušit akci, ale nechat ji na webu | v akci box **Events Status** | [2.7](#27-když-se-akce-ruší-nebo-přesouvá) |
| přidat novou scénu nebo sál | Akce (Events) → Místa konání (Venues) | [3](#3-přidat-místo-konání) |
| založit nový žánr | Akce (Events) → Kategorie akcí (Event Categories) | [4](#4-žánry-a-kategorie) |
| pustit rezervace na akci | v akci box **Tickets** → **New RSVP** | [5](#5-zapnout-rezervace-na-akci) |
| podívat se, kdo je přihlášený | Vstupenky (Tickets) → Přihlášení (Attendees) | [5.4](#54-kde-se-čtou-přihlášení) |
| přidat kandidáta na krále | Králové → Přidat krále | [6](#6-přidat-krále-a-zapsat-body) |
| zapsat body králi | Králové → Všichni králové → klik na jméno | [6.3](#63-zapsat-body) |
| zjistit, kdo vede | Králové → Všichni králové, sloupec **Body celkem** | [6.5](#65-kde-se-pořadí-ukáže) |
| přidat podnik do osvěžoven | Osvěžovny → Přidat osvěžovnu | [7](#7-přidat-osvěžovnu) |
| opravit cenu v osvěžovnách | Osvěžovny → klik na název podniku | [7.3](#73-upravit-nebo-smazat-podnik) |
| napsat novinku | Příspěvky (Posts) → Přidat příspěvek (Add Post) | [8](#8-napsat-aktualitu) |
| opravit text na stránce | Stránky (Pages) → Všechny stránky (All Pages) | [9](#9-upravit-text-stránky) |
| nahrát fotky | Média (Media) → Přidat soubor (Add Media File) | [10](#10-nahrát-obrázky) |
| nastavit datumy nového ročníku | Akce (Events) → Ročníky | [11](#11-nastavit-nový-ročník) |

---

## 1. Přihlášení a orientace

### 1.1 Jak se přihlásit

1. Otevři adresu webu a za ni připiš `/wp-admin`. Tedy třeba
   `https://budejovickymajales.cz/wp-admin`.
2. Vyplň **uživatelské jméno** (Username or Email Address) a **heslo** (Password).
3. Klikni na **Přihlásit se (Log In)**.

Pokud si na svém počítači přihlášení chceš pamatovat, zaškrtni **Pamatovat si mě
(Remember Me)**. Na sdíleném nebo cizím počítači to nedělej.

Heslo ti dá správce webu. **Nikdy ho neposílej dál a nesdílej jeden účet s víc lidmi** —
podle toho, kdo je pod kterou změnou podepsaný, se pak hledá, kde vznikla chyba.

### 1.2 Co kde je

Po přihlášení uvidíš **tmavé menu vlevo**. To je hlavní rozcestník. Shora dolů:

| Položka menu | K čemu to je |
|---|---|
| **Nástěnka (Dashboard)** | úvodní obrazovka, nic důležitého |
| **Příspěvky (Posts)** | aktuality a novinky |
| **Akce (Events)** | **program festivalu** — tady se tráví nejvíc času |
| **Vstupenky (Tickets)** | rezervace a seznam přihlášených |
| **Média (Media)** | všechny nahrané obrázky a soubory |
| **Stránky (Pages)** | pevné stránky webu (Program, Kontakt, GDPR…) |
| **Komentáře (Comments)** | nepoužíváme |
| **Králové** | kandidáti na krále Majálesu |

Pod tím je ještě **Vzhled (Appearance)**, **Pluginy (Plugins)**, **Uživatelé (Users)**,
**Nástroje (Tools)**, **Nastavení (Settings)**, **SCF** a **Members**. Do těch nechoď,
jsou popsané v sekci [Co nedělat](#co-nedělat).

Nahoře je tmavý pruh. Z něj se hodí jen jedna věc: najeď myší na **název webu** vlevo
nahoře (vedle ikonky domečku) a v nabídce klikni na **Zobrazit web (Visit Site)**.
Otevře se veřejný web, ať vidíš výsledek.

Pod **Akce (Events)** se rozbalí podmenu. Tohle jsou položky, které budeš potřebovat:

- **Akce (Events)** — seznam všeho, co je v programu
- **Přidat akci (Add New Event)** — formulář pro novou akci
- **Ročníky** — který ročník festivalu
- **Kategorie akcí (Event Categories)** — kategorie a žánry
- **Místa konání (Venues)** — scény, sály, kluby

Ostatní položky (Tags, Organizers, Import, Calendar Embeds, Settings, Help,
Troubleshooting, Event Add-Ons, Setup Guide) nepoužíváme.

### 1.3 Role — kdo co smí

Na webu jsou dvě role, které tě zajímají.

**Dramaturgie** — role pro většinu týmu. Smí:

- zakládat a upravovat **akce** v programu
- zakládat a upravovat **místa konání**
- zakládat a upravovat **kategorie a žánry**
- zakládat a upravovat **krále** a zapisovat jim body
- pracovat s **rezervacemi** a vidět přihlášené
- nahrávat **obrázky**
- psát **aktuality**

**Nesmí:** upravovat stránky, zakládat nový ročník, exportovat seznam přihlášených do
tabulky, měnit vzhled, instalovat pluginy, zakládat uživatele, sahat na nastavení.

**Správce (Administrator)** — vedení webu. Smí všechno. Tyhle účty by měly být maximálně
dva, ideálně jeden aktivní.

Kroky označené **[jen Admin]** zvládne jen správce.

### 1.4 Dvě pravidla, která platí všude

- **Nic není venku, dokud neklikneš na Publikovat (Publish) nebo Aktualizovat (Update).**
  Modré tlačítko vpravo nahoře. Dokud na něj neklikneš, tvoje práce existuje jen v tom
  formuláři a odchodem ze stránky se ztratí.
- **Koncept (Draft) je rozepsaná věc, kterou na webu nikdo nevidí.** Když si nejsi jistý/á,
  klikni **Uložit koncept (Save Draft)** a vrať se k tomu později. Tohle je bezpečná
  varianta: nic se nerozbije.

Na některých obrazovkách uvidíš boxy, které **ignoruj**: *Content Permissions (Members)*,
*Hide From Event Listings*, *Sticky in Month View*, *Tags*, *Slug*, *Author*, *Excerpt*.
Nejsou pro nás nastavené a sahat na ně není potřeba.

---

## 2. Přidat akci do programu

Akce = jedna položka programu. Jeden koncert, jedno divadlo, jeden workshop.
Když stejná kapela hraje dvakrát, jsou to **dvě akce**.

> **Pozor, tenhle formulář vypadá jinak než zbytek webu.** Akce se píšou ve starším
> editoru — klasický formulář s boxy pod sebou, ne stavebnice z bloků jako u stránek.
> Je to v pořádku, tak to má být.

### 2.1 Otevři formulář

1. V levém menu najeď na **Akce (Events)**.
2. Klikni na **Přidat akci (Add New Event)**.

> Pokud tě to místo formuláře hodí na obrazovku **TEC Setup Guide** („First-time setup"),
> klikni na **Přidat akci (Add New Event)** ještě jednou. Podruhé už to formulář otevře.

### 2.2 Název a popis

3. Do velkého pole nahoře — **Název (Add title)** — napiš **název akce**. To je to, co lidi uvidí
   v programu. Piš to, jak se to má jmenovat — ne „koncert kapely XY", ale rovnou „XY".
4. Pod názvem je velké textové pole. Do něj napiš **popis akce** — pár vět o tom,
   co to je. Nad polem jsou záložky **Visual** a **Code**. Zůstaň na **Visual**,
   tam se píše normálně jako ve Wordu (tučné, kurzíva, odrážky).

### 2.3 Datum a čas

Najdi box **The Events Calendar**, část **TIME & DATE**.

5. Klikni do prvního políčka u **Start/End:**. Vyskočí **kalendář** — vyber v něm den.
   Kalendář je česky a začíná pondělím, takže se v něm dá klikat bez přemýšlení.
   **Datum ale klikej, nepiš.** V políčku zůstane americký zápis: `11/4/2026` znamená
   **4. listopadu**, ne 11. dubna. Ručně napsané „30. 5." systém pochopí špatně.

   > Festival je skoro rok daleko, takže se kalendář otevře na aktuálním měsíci a musíš
   > se proklikat dopředu. Šipka vpravo nahoře posouvá po měsících, rozbalovátka
   > **měsíc** a **rok** skočí rovnou.
6. Vedle data je políčko s časem. Klikni do něj a vyber čas ze seznamu.
7. Stejně vyplň **čas konce** a **datum konce**. U akce, která trvá jeden večer,
   je datum konce stejné jako datum začátku.
8. Pokud akce trvá celý den a nemá přesný čas (třeba výstava), zaškrtni
   **All Day Event**. Políčka s časem pak zmizí.

Pod poli se průběžně vypisuje věta typu *„This event is from 6:00pm to 6:45pm on
May 30, 2027."* **Přečti si ji.** Je to nejrychlejší kontrola, že jsi datum zadal/a dobře.

### 2.4 Místo konání

V boxu **The Events Calendar** sjeď na část **LOCATION**.

9. U **Venue:** rozbal seznam a vyber **existující místo**. Většina scén už je
   v systému založená.
10. Adresa, město a další údaje se doplní samy. Nepřepisuj je.

Když místo v seznamu není, přeruš a založ ho podle [sekce 3](#3-přidat-místo-konání).
Teprve pak se vrať sem.

Zaškrtávátka **Show map** a **Show map link** nech, jak jsou.

Části **ORGANIZERS** a **EVENT WEBSITE** nech prázdné. Nepoužíváme je.

### 2.5 Žánr a kategorie

Vpravo (na menším displeji dole) je box **Kategorie akcí (Event Categories)**.

11. Najdi v seznamu **žánr** akce a zaškrtni ho. Žánry jsou odsazené pod svou kategorií —
    třeba *punk* je odsazený pod *Hudba*.
12. **Kategorii nad ním (tedy *Hudba*) zaškrtávat nemusíš** — doplní se sama při uložení.
    Z kategorie se bere barva, kterou akce dostane v programu, a zapomenout ji bývala
    nejčastější chyba. Proto to teď hlídá web za tebe.
13. Když akce sedí pod víc žánrů, zaškrtni jich víc. Nic se nerozbije.

Podrobnosti o tom, co je kategorie a co žánr, jsou v [sekci 4](#4-žánry-a-kategorie).

Ještě níž je box **Ročníky**. Zaškrtni ten letošní (třeba **2027**). Bez toho se akce
nemusí ukázat v programu aktuálního ročníku.

### 2.6 Obrázek, „Top představení" a bezbariérovost

**Obrázek:**

14. Najdi box **Náhledový obrázek (Featured image)** vpravo.
15. Klikni na **Nastavit náhledový obrázek (Set featured image)**.
16. Buď vyber obrázek z knihovny, nebo přetáhni nový. Pokud je fotka velká a nenahraje
    se, přečti si [sekci 10](#10-nahrát-obrázky).
17. Klikni na **Nastavit náhledový obrázek (Set featured image)** v pravém dolním rohu okna.

> Pod tlačítkem je poznámka **„We recommend a 16:9 aspect ratio for featured images."**
> Znamená to: **obrázek na šířku**, zhruba v poměru stran jako televize. Fotka na výšku
> se v programu ořízne a většinou špatně.

**„Top představení" — dvě různá zaškrtávátka.** Tohle plete lidi každý rok, tak pozor:

| Kde | Co dělá |
|---|---|
| box **Event Options** → **Feature Event** | označí akci jako **doporučenou / top představení** v programu |
| box **Akce – nastavení webu** → **V carouselu** | pustí akci do **otáčecí galerie na úvodní stránce** |

18. Jsou to **dvě nezávislé věci**. Chceš-li akci zvýraznit v programu, zaškrtni
    **Feature Event**. Chceš-li ji dostat na homepage, přepni **V carouselu** na **Ano**.
    Chceš-li obojí, nastav obojí.

> Carousel na homepage nese jen pár akcí. Nedávej do něj všechno, ztratí smysl.

**Bezbariérovost:**

19. V boxu **Akce – nastavení webu** je **Bezbariérovost – výjimka**. Má tři volby:
    - **Zdědit z místa** — výchozí, nech to tak. Bezbariérovost se vezme z místa konání.
    - **Ano – bezbariérová** — ručně zapnout, i když místo bezbariérové obecně není.
    - **Ne – není bezbariérová** — ručně vypnout, i když místo bezbariérové je.

    Používej jen tu výjimku. Když je celá scéna bezbariérová, nastav to **jednou u místa**
    ([sekce 3](#3-přidat-místo-konání)), ne u každé akce zvlášť.

### 2.6b Jak se akce dostane na úvodní stránku

Sekce **Nepřehlédni** na úvodu se skládá sama z akcí, které mají zaškrtnuté
**Top představení** (v boxu *Event Options* u akce). Nic se nikam nepřepisuje ručně.

- Ukáže se **nejvýš osm** akcí, seřazených podle data. Když jich označíš víc, pozdější
  se neukážou — proto označuj jen to, co má opravdu zaujmout.
- Akce musí padnout do **termínu ročníku** (Akce → Ročníky). Mimo něj se neukáže.
- **Bez fotky** se místo obrázku ukáže barevná plocha podle kategorie. Vypadá to
  záměrně, ale fotka je lepší.
- Když nemá *Top představení* **žádná** akce, sekce Nepřehlédni na úvodu prostě není.
  Není to chyba.

Totéž zaškrtávátko čte i stránka Program, kde akci označí štítkem **Top**.

### 2.7 Když se akce ruší nebo přesouvá

V boxu **Events Status** se dá akci nastavit stav. Hodí se to během festivalu:

- **Scheduled** — normální stav, koná se.
- **Canceled** — zrušeno. Akce zůstane v programu, ale bude označená jako zrušená.
- **Postponed** — odloženo.

Do pole **Reason (optional)** můžeš napsat krátké vysvětlení („kvůli dešti").

**Akci, která se nekonala, radši označ jako zrušenou než ji smaž.** Lidi se na ni budou
dívat a prázdná stránka je horší než jasná informace.

### 2.8 Publikovat

20. Vpravo nahoře klikni na **Náhled (Preview)** a zkontroluj, jak to vypadá.
21. Pak klikni na modré **Publikovat (Publish)**.

Hotovo. Akce je v programu.

### 2.9 Jak akci později najít a opravit

1. **Akce (Events)** → **Akce (Events)**.
2. V tabulce najdi název a klikni na něj.
3. Oprav, co potřebuješ, a klikni na **Aktualizovat (Update)**.

V tabulce vidíš u každé akce kategorie, ročník, počet přihlášených (**Attendees**)
a datum začátku a konce. Sloupec **Attendees** je nejrychlejší způsob, jak zjistit,
jestli se někdo hlásí.

---

## 3. Přidat místo konání

**Nejdřív si ověř, že místo opravdu chybí.** V systému je 28 míst a duplicita
(dvakrát „Zlatý most") program rozbije — akce se pak rozdělí mezi dva záznamy.

### 3.1 Kdy se zakládá nové místo

| Situace | Co s tím |
|---|---|
| Nová scéna, klub nebo sál, který na Majálesu ještě nebyl | **založ nové místo** |
| Stejná scéna jako loni, jen má jiný program | použij **existující** místo |
| Stejná budova, ale jiná místnost (sál A × sál B) | **založ nové místo** pro tu druhou místnost |
| Místo se přejmenovalo | **uprav existující** místo, nezakládej druhé |

### 3.2 Jak ho založit

1. **Akce (Events)** → **Místa konání (Venues)**.
2. **Nejdřív projdi seznam**, jestli tam místo opravdu není. Zkus i jiný zápis názvu.
3. Klikni na **Přidat místo konání (Add New Venue)** nahoře.
4. Do pole nahoře napiš **název místa**, jak ho lidi znají („Zlatý most", ne
   „ZM — hlavní scéna 2027").
5. V boxu **Venue Information** vyplň:
   - **Address:** — ulice a číslo
   - **City:** — České Budějovice
   - **Country:** — Czech Republic
   - **State or Province:**, **Postal Code:**, **Phone:**, **Website:** — nepovinné,
     vyplň, co víš
6. **Show Map** a **Show Map Link** nech zaškrtnuté.
7. V boxu **Místo – bezbariérovost** přepni **Bezbariérové místo** na **Ano**, pokud se
   tam dostane člověk na vozíku. Jinak nech **Ne**.
8. Klikni na **Publikovat (Publish)**.

Místo se pak objeví v nabídce u akcí.

> **Bezbariérovost se vyplňuje jednou za místo, ne u každé akce.** Je to pár desítek
> přepnutí místo stovky. Všechny akce na tom místě ji podědí automaticky.

### 3.3 Upravit existující místo

1. **Akce (Events)** → **Místa konání (Venues)**.
2. Klikni na název.
3. Oprav a klikni na **Aktualizovat (Update)**.

Změna se projeví u **všech** akcí na tom místě naráz. To je výhoda i riziko — při
přejmenování se ujisti, že měníš opravdu to, co chceš.

---

## 4. Žánry a kategorie

### 4.1 Jak to funguje

Jsou to dvě patra jednoho seznamu:

- **Kategorie** je široká škatulka: *Divadlo*, *Filmy*, *Hudba*, *Online*,
  *Program pro nejmenší*, *Studentské promítání – Beseda s autory*, *Venkovní scéna*,
  *Vnitřní scéna*, *Volba krále*. Je jich devět.
- **Žánr** je upřesnění uvnitř kategorie: pod *Hudbou* je *punk*, *Rap*, *Folk*,
  *disco*… Žánrů je přes čtyřicet.

V administraci jsou v jednom seznamu. Žánry jsou **odsazené** pod svou kategorií.

### 4.2 Jak akci zařadit

Popsáno v [kroku 11–13](#25-žánr-a-kategorie). Ve zkratce: v boxu **Kategorie akcí
(Event Categories)** zaškrtni **žánr i kategorii nad ním**.

> **Pozor na dvojí „Workshop".** Žánr *Workshop* existuje dvakrát — jednou pod
> *Venkovní scéna*, jednou pod *Vnitřní scéna*. Podívej se na odsazení a vyber ten,
> který odpovídá scéně.

### 4.3 Odkud se bere barva kategorie

V programu se akce barevně odlišují podle kategorie, takže návštěvník na první pohled
pozná hudbu od divadla. Šest největších kategorií má svou barvu, zbytek je neutrálně
šedý.

| Kategorie | Barva |
|---|---|
| Hudba | zelenomodrá |
| Divadlo | levandulová |
| Filmy | žlutá |
| Volba krále | korálová |
| Venkovní scéna | svěží zelená |
| Vnitřní scéna | tlumená růžová |
| Online, Studentské promítání, Program pro nejmenší | šedá |

**Barvu nenastavuješ ty a nenajdeš ji u kategorie.** U kategorie sice je pole *Barva*,
ale je prázdné a má zůstat prázdné. Barvy se počítají z palety ročníku (Vzhled →
Přizpůsobit → Barvy), takže když se příští ročník přebarví web, přebarví se program
s ním. Kdyby barva byla uložená u kategorie, zůstal by program v loňských barvách
a nikdo by nevěděl proč.

Změnit barvu jedné kategorie proto znamená zásah do souboru `program.css` — to už je
práce pro někoho, kdo umí CSS. Přebarvit **celý web** naopak zvládneš sám: je to patnáct
polí ve Vzhled → Přizpůsobit → Barvy a program se přebarví s ním.

Žánry barvu nemají a mít nebudou — kdyby ji měl každý zvlášť, bylo by na stránce přes
čtyřicet barev a z barevného systému by se stal zmatek. **Žánr dědí barvu po své
kategorii.**

### 4.4 Kdy založit nový žánr

**Většinou nezakládej.** Čtyřicet dva žánrů pokryje skoro všechno. Čím víc žánrů, tím
hůř se program filtruje a tím víc práce má tým, který web převezme po tobě.

Nový žánr založ, jen když:

- žádný existující opravdu nesedí (ne „sedí přibližně" — opravdu nesedí), **a**
- bude pod ním **víc než jedna akce**

Jedna akce bez vhodného žánru? Dej jí jen kategorii, to stačí.

### 4.5 Jak nový žánr založit

1. **Akce (Events)** → **Kategorie akcí (Event Categories)**.
2. Vlevo je formulář **Přidat kategorii akcí (Add New Event Category)**.
3. **Name** — název žánru, jak ho uvidí návštěvník.
4. **Slug** — nech prázdné, systém ho doplní sám.
5. **Parent Event Category** — **tohle je nejdůležitější pole.** Vyber kategorii, pod
   kterou žánr patří (třeba *Hudba*). Když necháš **None**, vytvoříš omylem novou
   kategorii, ne žánr.
6. **Description** — nech prázdné.
7. Klikni na **Přidat kategorii akcí (Add New Event Category)** dole.

Políčka **Category Colors**, **Category Priority** a **Hide category from legend**
**nevyplňuj** — to je vestavěná funkce pluginu, kterou tenhle web nepoužívá.

### 4.6 Nastavit barvu kategorie [jen Admin]

Barvy devíti kategorií jsou už nastavené a **měnit se nemají**. Kdyby to přesto bylo
potřeba:

1. **Akce (Events)** → **Kategorie akcí (Event Categories)**.
2. Klikni na název **kategorie** (ne žánru).
3. Sjeď úplně dolů na pole **Barva** s tlačítkem **Select Color**.
4. Vyber barvu a klikni na **Aktualizovat (Update)**.

> Na té obrazovce je **ještě jeden** blok barev — **Category Colors** s poli
> *Primary Color*, *Background Color* a *Text Color*. **Ten nepoužíváme.** Je to funkce
> pluginu, kterou web nečte. Platí jen pole **Barva** úplně dole.

---

## 5. Zapnout rezervace na akci

Rezervace znamená, že si lidi dopředu zamluví místo. Používá se tam, kde je omezená
kapacita — workshopy, besedy, malé sály.

Rezervace jsou **zdarma**. Nic se neplatí, nikdo nezadává kartu.

> **Nejdřív akci ulož (Publikovat nebo Uložit koncept), teprve potom přidávej rezervace.**
> Na nové, neuložené akci se rezervace nemá k čemu přilepit.

### 5.1 Vytvořit rezervaci

1. Otevři akci: **Akce (Events)** → **Akce (Events)** → klik na název.
2. Sjeď na box **Tickets**.
3. Klikni na **New RSVP**.

> V boxu je i tlačítko **New ticket** a možná hláška o platební bráně
> („no payment gateway configured", „Set up Tickets Commerce"). **Ignoruj je.**
> Placené vstupenky neprodáváme. Potřebuješ vždycky **New RSVP**.

### 5.2 Vyplnit

4. **Name:** — název rezervace, který uvidí návštěvník. Napiš něco srozumitelného, třeba
   **Rezervace místa**. Ne „RSVP1".
5. **Description:** — nepovinné. Sem patří podmínky: *„Kapacita je omezená, přijďte
   prosím 10 minut předem."*
6. **Show description on frontend ticket form.** — zaškrtni, pokud chceš, aby se popis
   návštěvníkovi ukázal.
7. **Start sale:** — **den a čas, kdy se rezervace otevřou.** Vlevo datum, vpravo čas.
   Datum **klikej z kalendáře**, nepiš ručně.
8. **End sale:** — **den a čas, kdy se rezervace zavřou.** Obvykle pár hodin
   před začátkem akce nebo v den akce ráno.

   > **Tohle si zkontroluj dvakrát.** Formulář si sám předvyplní dnešek a zítřek.
   > Když to nepřepíšeš, rezervace se zítra samy zavřou a nikdo se nepřihlásí.

9. **Capacity:** — **kolik lidí se vejde.** Napiš číslo. Když necháš prázdné, je to
   neomezeně — což u rezervací většinou nedává smysl.
10. **Can't Go:** — zaškrtni **Enable „Can't Go" responses**, pokud chceš, aby lidi mohli
    odpovědět i „nepřijdu". U běžného workshopu to zapínat nemusíš.
11. Klikni na **Save RSVP**.
12. Nakonec klikni na **Aktualizovat (Update)** u celé akce.

### 5.3 Povinná rezervace

**Systém neumí rezervaci vynutit.** Neexistuje přepínač „bez rezervace nevstoupíš".

Když je rezervace povinná, **napiš to lidem do textu**:

- do **popisu akce** (velké pole pod názvem), třeba tučně: **Vstup pouze s rezervací.**
- a ještě jednou do pole **Description:** u samotné rezervace

Na místě si to pak hlídá pořadatel podle seznamu přihlášených.

### 5.4 Kde se čtou přihlášení

**Rychlá kontrola — kolik lidí se přihlásilo:**

1. **Akce (Events)** → **Akce (Events)**.
2. V tabulce je sloupec **Attendees** s číslem u každé akce.

**Celý seznam jmen:**

1. V levém menu **Vstupenky (Tickets)** → **Přihlášení (Attendees)**.
2. Uvidíš tabulku se jmény, e-maily, akcí (**Associated Post**), stavem (**Status**)
   a odbavením (**Check in**).
3. Nahoře je **vyhledávání** — dá se filtrovat podle jména, e-mailu nebo akce.

Jakmile má akce založené rezervace, odkaz na její přihlášené najdeš i přímo v akci
v boxu **Tickets**.

**[jen Admin] Stažení seznamu do tabulky** — tlačítko **Export** nahoře na obrazovce
Přihlášení (Attendees). Role Dramaturgie na něj nedosáhne; požádej správce.

> **Seznam přihlášených jsou osobní údaje.** Nestahuj si ho do telefonu, neposílej
> přes WhatsApp a po festivalu smaž, co jsi stáhl/a. Co s tím smíte dělat, je ve
> zpracování osobních údajů na stránce GDPR.

---

## 6. Přidat krále a zapsat body

Králové jsou kandidáti na krále Majálesu. Každý má jméno, školu, téma, fotku a body.

### 6.1 Založit kandidáta

1. V levém menu **Králové** → **Přidat krále**.
2. Do velkého pole nahoře (**Jméno kandidáta**) napiš **jméno a příjmení**.
3. V boxu **Král – detail kandidáta** vyplň:
   - **Škola** — škola, kterou kandidát reprezentuje
   - **Téma** — téma, se kterým soutěží
4. Vpravo v boxu **Fotka kandidáta** klikni na **Nastavit fotku kandidáta** a nahraj
   portrét. Domluvte se v týmu na jedné orientaci (na výšku nebo na šířku) a držte ji
   u všech kandidátů — jinak bude přehled králů vypadat rozházeně.
5. V boxu **Ročníky** zaškrtni letošní ročník (třeba **2027**).
6. Klikni na **Publikovat (Publish)**.

> **U krále není pole na delší text.** Je to záměr — ukazuje se jen jméno, škola, téma,
> fotka a body. Delší bio se nikam nevejde.

> **Kandidát nemá vlastní stránku.** Ukazuje se jen v žebříčku na `/kralove/`. Tlačítko
> **Zobrazit krále** v editoru proto nikam nevede — ignoruj ho.

> V boxu **Pořadí krále** je pole **Order** s číslem. Na webu se kandidáti řadí podle
> bodů, takže tohle pole rozhoduje jen při **shodě bodů** — nižší číslo je výš. To se
> hodí hlavně na začátku ročníku, kdy mají všichni nulu. Když to necháš na nule, řadí
> se při shodě podle abecedy. Většinou to řešit nemusíš.

### 6.2 Co jsou segmenty

Body se nesčítají do jednoho čísla. Zapisují se po **segmentech** — po disciplínách:

| Segment | Kdy se zapisuje |
|---|---|
| **Studentské jamy** | body z jamů |
| **Královská hra** | body z královské hry |
| **Plnění výzev** | body za splněné výzvy |
| **Ostatní** | cokoliv, co se nevejde do předchozích |

Na webu se každá disciplína ukáže **vlastní barvou** v pruhu kandidáta, takže je na
první pohled vidět, odkud body přišly. Nad výpisem je legenda, která barvy pojmenovává.

| Disciplína | Barva v pruhu |
|---|---|
| Studentské jamy | zelenomodrá |
| Královská hra | korálová |
| Plnění výzev | levandulová |
| Ostatní | šedá |

Barvy jsou z globální palety, takže se při změně vzhledu ročníku přebarví samy.
**Pod pruhem jsou navíc přesné body** za každou disciplínu, obarvené stejnou tečkou.

**Když řádek necháš bez vybrané disciplíny**, body se neztratí — spadnou do *Ostatní*.
**Dva řádky se stejnou disciplínou** se sečtou do jednoho dílku, takže se nic
nerozdvojí; pořád ale platí, že čistší je mít jeden řádek na disciplínu.

### 6.3 Zapsat body

1. **Králové** → **Všichni králové**.
2. Klikni na jméno kandidáta.
3. V boxu **Král – detail kandidáta** sjeď na **Body**.
4. Klikni na **Přidat bodování**. Přibude řádek.
5. Ve sloupci **Segment** vyber z nabídky disciplínu.
6. Do sloupce **Počet** napiš číslo.
7. Pro další disciplínu klikni znovu na **Přidat bodování**.
8. Klikni na **Aktualizovat (Update)**.

**Jeden segment = jeden řádek.** Když kandidát získá body ve stejné disciplíně podruhé,
**neudělej nový řádek** — **přepiš číslo** v tom existujícím na nový součet. Dva řádky
se stejným segmentem by vypadaly jako chyba.

Řádek smažeš tlačítkem s minusem na jeho konci.

### 6.4 Během festivalu

Body se dají přepisovat průběžně, klidně několikrát denně. Jen nezapomeň po každé změně
kliknout na **Aktualizovat (Update)** — jinak se nic neuloží.

### 6.5 Kde se pořadí ukáže

Na stránce **[/kralove/](https://novy.budejovickymajales.cz/kralove/)**. Nic tam
nevkládáš — výpis se vykreslí sám ze zadaných kandidátů.

**Co stránka dělá sama:**

| | |
|---|---|
| Pořadí | podle součtu bodů, nejvíc nahoře. Při shodě rozhoduje pole **Order**, pak abeceda. |
| Pruh | délka je poměr k vedoucímu kandidátovi, ne k součtu všech bodů. Vedoucí má vždy plný. |
| Barvy v pruhu | jednotlivé disciplíny, v pořadí podle tabulky v 6.2 — u všech kandidátů stejném, aby šly pruhy porovnat. |
| Čísla pod pruhem | přesné body za každou disciplínu, barva tečky odpovídá barvě v pruhu. Disciplína s nulou se nevypisuje. |
| Ročník | ukáže se nejnovější ročník, **který má kandidáty**. Rok je napsaný nad výpisem. |
| Chybějící fotka | místo ní se ukáže první písmeno jména. Řádek se nerozsype, ale je to vidět. |

**Proč ročník „který má kandidáty" a ne prostě letošní.** Ročník 2028 v Akcích vznikne
dřív, než školy nahlásí kandidáty. Kdyby se výpis držel letošního ročníku, byla by
stránka od ledna do března prázdná. Takhle na ní zůstane loňské konečné pořadí a rok
nad výpisem řekne, čí je.

> **Pozor na pole Škola.** Z názvu školy se skládá adresa kotvy, na kterou odkazují
> weby škol a Instagram — `/kralove#gymji`. Kotva vznikne z názvu bez diakritiky,
> malými písmeny, s pomlčkami místo mezer. Když školu pojmenuješ jinak než loni,
> **starý odkaz přestane fungovat**: `Česká` dá `ceska`, ale `Gymnázium Česká` dá
> `gymnazium-ceska`. Dva kandidáti ze stejné školy jsou v pořádku — druhý dostane
> kotvu s dvojkou na konci.

**Názvy škol, na které se odkazuje ze starého webu.** Piš je přesně takhle, jinak
odkazy přestanou mířit na kandidáta:

| Napiš do pole Škola | Vznikne kotva |
|---|---|
| GYMJI | `gymji` |
| GJVJ | `gjvj` |
| ČAG | `cag` |
| BIGY | `bigy` |
| ČRG | `crg` |
| Zdrávka | `zdravka` |
| Dukelská | `dukelska` |
| Vakantis | `vakantis` |
| OACB | `oacb` |
| Stavárna | `stavarna` |
| SŠIPS | `ssips` |
| SPŠAUTO | `spsauto` |
| Česká | `ceska` |
| Husova 9 | `husova-9` |

Nová škola v seznamu chybět může — kotvu dostane taky, jen na ni zatím nikdo
neodkazuje.

**V administraci** má seznam **Králové** sloupec **Body celkem** se součtem. Dá se podle
něj kliknutím seřadit, takže je hned vidět, kdo vede.

---

## 7. Přidat osvěžovnu

Osvěžovny jsou budějovické podniky, které na týden festivalu nabízejí zvýhodněné menu.
Každý podnik je samostatný záznam s adresou a seznamem položek. Vypisují se na stránce
[/osvezovny/](https://novy.budejovickymajales.cz/osvezovny/) jako kartičky.

> **Nepiš nabídku přímo do stránky.** Stránka Osvěžovny je skoro prázdná — je na ní
> jen nadpis, úvodní věta a značka `[bm_osvezovny]`. Tu značku nesmaž, tím zmizí
> všechny podniky. Texty se mění tady v Osvěžovnách, ne v té stránce.

### 7.1 Založit podnik

1. V levém menu **Osvěžovny** → **Přidat osvěžovnu**.
2. Do velkého pole nahoře napiš **název podniku** tak, jak ho lidé znají — „Široko",
   ne „Kavárna Široko s.r.o.".
3. V boxu **Osvěžovna – adresa a nabídka** vyplň:
   - **Adresa** — ulice a číslo, třeba `Široká 443/19`. Když ji necháš prázdnou,
     napíše se na webu jen „Zobrazit na mapě".
   - **Odkaz na mapu** — zkrácený odkaz z Map Google (v mapě **Sdílet → Kopírovat
     odkaz**). **Nepovinné**: bez něj se podnik na mapě vyhledá podle názvu.
4. Přidej položky (viz [7.2](#72-vyplnit-nabídku)).
5. Klikni na **Publikovat (Publish)**.

> **Podnik nemá vlastní stránku.** Ukazuje se jen ve výpisu na `/osvezovny/`, stejně
> jako králové. Tlačítko **Zobrazit** v editoru proto nikam nevede.

> V boxu **Pořadí osvěžovny** je pole **Order** s číslem. **Nižší číslo je na webu
> dřív.** Tím se dá dát dopředu podnik, na kterém vám záleží nejvíc. Když tam necháš
> nulu u všech, seřadí se podniky podle abecedy. Tip: čísluj po desítkách (10, 20,
> 30…), ať jde mezi ně později něco vložit bez přepisování zbytku.
>
> Na počítači stojí podniky ve třech sloupcích a **čtou se shora dolů, ne po
> řadách** — první tři tedy skončí pod sebou v levém sloupci, ne vedle sebe
> nahoře. Je to proto, aby pod krátkou nabídkou nezůstala prázdná díra.

### 7.2 Vyplnit nabídku

V boxu **Nabídka** klikni na **Přidat položku**. Každý řádek je jedna položka menu
a má čtyři pole:

| Pole | Co do něj | Povinné |
|---|---|---|
| **Název** | Jak se položka jmenuje — `Okurkový Fizz`, `Majálesový Gyros` | ano |
| **Popis** | Složení nebo doplněk — `Borovička s tonicem`. Na webu je menším písmem pod názvem. | ne |
| **Cena** | Včetně „Kč" — `80 Kč`. Je to obyčejný text, takže sem jde napsat i `Sleva 20 %` nebo nechat prázdno. | ne |
| **Druh** | `Nealko`, `Alko`, nebo `Jídlo` | ne |

**Pořadí řádků je pořadí na webu.** Řádky se přetahují za tečkovaný úchyt vlevo.

> **Název a cenu nepiš do jednoho pole.** Na starém webu byly ceny vlepené do věty
> („…a prosecco 155 Kč") a nešly porovnat. Teď stojí v pravém sloupci pod sebou —
> ale jen když je cena ve svém vlastním políčku.

### 7.2b K čemu je „Druh"

Druh dělá dvě věci a **obě fungují i bez něj**, takže se nemusíš trápit tím, co kam patří:

1. **Rozdělí kartičku na skupiny.** Když má podnik aspoň dva různé vyplněné druhy,
   objeví se nad položkami nadpisy **NEALKO** a **ALKO** — přesně jako je mělo Široko
   na starém webu. Když má podnik jen jeden druh (nebo žádný), žádné nadpisy nejsou
   a je to prostý seznam.
2. **Pohání filtr** nad výpisem — tlačítka *Vše / Nealko / Alko / Jídlo*. Tlačítko
   se objeví jen pro druh, který aspoň jedna položka opravdu má.

> **Když nevíš, nech prázdno.** Položka se vypíše úplně normálně, jen se neukáže pod
> filtrem. Je to lepší než ji zařadit špatně. U drinku jako „Květnový infarkt"
> (yerba maté, mangový džus, espresso a trochu zlata) to stejně nikdo nepozná.

### 7.3 Upravit nebo smazat podnik

- **Opravit cenu nebo text:** **Osvěžovny** → **Všechny osvěžovny** → klik na název →
  oprav → **Aktualizovat (Update)**.
- **Smazat jeden řádek nabídky:** u řádku vpravo je **✕** (nebo *Odstranit řádek*).
- **Podnik letos nebude:** otevři ho a dej **Přesunout do koše (Move to Trash)**.
  Z webu zmizí hned. V koši zůstane třicet dní, takže se dá vrátit.

### 7.4 Co dělat na začátku ročníku

Nabídky se mění každý rok. Projdi **Všechny osvěžovny** a u každého podniku:

1. Zkontroluj, že s vámi letos jde do spolupráce. Když ne → koš.
2. Přepiš položky a ceny.
3. Podniky, které přibyly, založ podle [7.1](#71-založit-podnik).

Nic jiného se dělat nemusí — stránka Osvěžovny se o sebe postará sama.

---

## 8. Napsat aktualitu

Aktualita = krátká novinka. „Přidali jsme do lineupu…", „Rezervace na workshopy jsou
otevřené", „Kvůli dešti přesouváme…".

> **Než napíšeš první aktualitu, zeptej se správce, kde se novinky na webu vypisují.**
> Seznam novinek není nutně v hlavním menu a může se k němu chodit jinudy. Potřebuješ
> vědět, kam lidem poslat odkaz.

### 8.1 Jak na to

1. V levém menu **Příspěvky (Posts)** → **Přidat příspěvek (Add Post)**.
2. Nahoře napiš **nadpis**. Krátký a konkrétní. Ne „Novinky", ale
   „Rezervace na workshopy jsou otevřené".
3. Klikni pod nadpis a piš text. Tenhle editor se skládá z **bloků** — jak to funguje,
   je v [sekci 9](#9-upravit-text-stránky).
4. Vpravo je panel se záložkami **Příspěvek (Post)** a **Blok (Block)**. Zůstaň na
   **Příspěvek (Post)**.

   > Panel nevidíš? Zapni ho ikonkou s postranním panelem vpravo nahoře, vedle tlačítka
   > **Publikovat (Publish)**.

5. V tom panelu klikni na **Nastavit náhledový obrázek (Set featured image)** a nahraj
   obrázek. To je hlavní obrázek novinky.
6. Níž v panelu rozbal **Ročníky** a zaškrtni letošní ročník.
7. Sekce **Kategorie (Categories)**, **Štítky (Tags)** a **Content Permissions (Members)**
   nech být, nepoužíváme je.
8. Klikni na **Publikovat (Publish)** vpravo nahoře a potvrď.

### 8.2 Pár rad k psaní

- Nejdůležitější informace dej do **první věty**. Většina lidí dál nečte.
- **Konkrétní datum a čas**, ne „brzy" a „příští týden". Novinku si někdo přečte za měsíc.
- Když odkazuješ na akci nebo stránku, **vlož odkaz** (postup v [sekci 9.6](#96-odkaz)).
- Odstavce po třech až čtyřech řádcích. Zeď textu na mobilu nikdo nepřečte.

### 8.3 Opravit nebo smazat

1. **Příspěvky (Posts)** → **Všechny příspěvky (All Posts)**.
2. Klikni na nadpis, oprav, klikni na **Aktualizovat (Update)**.

Chybnou novinku raději **oprav**, než smaž — někdo už na ni mohl poslat odkaz.

---

## 9. Upravit text stránky

**[jen Admin]** — role Dramaturgie na stránky nedosáhne.

Stránky jsou pevné části webu: Program, Králové, Osvěžovny, Praktické info, Partneři,
Kontakt a další. **Strukturu nemáš měnit** — jen texty uvnitř. Menu se podle stránek
neskládá samo; mění se skriptem `wordpress/vzhled/menu.py`.

> **Pořadí v menu určuje i barvy.** Od 6. 11. 2026 má každá položka hlavní úrovně
> svou barvu — Program žlutou, Králové korálovou, Osvěžovny levandulovou,
> O festivalu zelenomodrou. Tatáž barva podtrhává nadpis stránky a dědí ji
> i podstránky. Když se pořadí položek v menu změní, barvy se posunou s nimi.
> Stránka, která v menu není, zůstane černobílá.

> **Stránka „O nás“ je v konceptech.** Její text se 6. 11. 2026 přenesl na stránku
> *O festivalu*, na kterou vede položka v menu. Kdyby ji někdo hledal, je ve
> **Stránky (Pages) → Koncepty (Drafts)**.

### 9.1 Otevřít stránku

1. V levém menu **Stránky (Pages)** → **Všechny stránky (All Pages)**.
2. Klikni na název stránky.
3. Pokud vyskočí uvítací okénko **„Welcome to the editor"**, zavři ho křížkem.

### 9.2 Jak editor funguje

Text je poskládaný z **bloků**. Každý odstavec, nadpis, seznam nebo obrázek je
samostatný blok. Kliknutím do bloku ho vybereš a nad ním se objeví malá lišta s nástroji.

**Nový blok přidáš** modrým tlačítkem **+** vlevo nahoře, nebo tak, že na konci odstavce
zmáčkneš **Enter** a napíšeš `/` (lomítko) — vyskočí nabídka bloků.

**Zpět** se vrátíš šipkou doleva vlevo nahoře, nebo klávesami **Ctrl+Z**
(na Macu **Cmd+Z**). Platí to i na smazaný blok.

### 9.3 Odstavec

Prostě klikni a piš. **Enter** udělá nový odstavec, **Shift+Enter** jen zalomí řádek
uvnitř toho stávajícího.

Tučné: označ text a zmáčkni **Ctrl+B** (**Cmd+B**). Kurzíva: **Ctrl+I** (**Cmd+I**).
Nebo použij tlačítka **B** a **I** v liště nad blokem.

### 9.4 Nadpis

1. Zmáčkni **Enter** na konci odstavce.
2. Napiš `/nadpis` a vyber **Nadpis (Heading)**.
3. Napiš text nadpisu.
4. V liště nad blokem se dá přepnout úroveň: **H2**, **H3**, **H4**.

**Jak volit úroveň:** hlavní nadpis stránky je její název, ten už existuje. Sekce v textu
dělej jako **H2**. Podsekci uvnitř sekce jako **H3**. Nepřeskakuj z H2 rovnou na H4 —
lidi se čtečkou obrazovky se podle toho orientují.

### 9.5 Seznam

1. Zmáčkni **Enter**, napiš `/seznam` a vyber **Seznam (List)**.
2. Napiš první položku, zmáčkni **Enter**, napiš druhou.
3. Seznam ukončíš dvojím zmáčknutím **Enter**.
4. V liště nad blokem se přepíná mezi odrážkami a číslováním.

**Tab** udělá odsazenou podpoložku, **Shift+Tab** ji vrátí zpátky.

### 9.6 Odkaz

1. Označ text, ze kterého má být odkaz.
2. Zmáčkni **Ctrl+K** (**Cmd+K**), nebo klikni na ikonku řetízku v liště.
3. **Odkaz na stránku našeho webu:** začni psát její název a vyber ji z nabídky,
   která vyskočí. Tohle je lepší než vkládat adresu — odkaz přežije přejmenování.
4. **Odkaz jinam:** vlož celou adresu včetně `https://`.
5. Zmáčkni **Enter**.

Na cizí web nech **Otevřít v novém panelu (Open in new tab)** zapnuté. Na vlastní
stránky vypnuté.

**Jako text odkazu napiš, kam vede.** Ne „klikni sem", ale „program festivalu".

### 9.7 Obrázek

1. Zmáčkni **Enter**, napiš `/obrázek` a vyber **Obrázek (Image)**.
2. Klikni na **Mediální knihovna (Media Library)** a vyber obrázek, nebo ho přetáhni
   z počítače.
3. **Vyplň Alternativní text (Alt text)** v panelu vpravo — jednou větou popiš, co je
   na obrázku. Čtou to nevidomí návštěvníci a vyhledávače.
4. Velikost a zarovnání se nastaví v liště nad blokem.

### 9.8 Smazat blok

Klikni do bloku, pak na **tři tečky** v liště nad ním a vyber **Odstranit (Delete)**.
Nebo označ blok a zmáčkni **Delete**.

### 9.9 Uložit

1. Vpravo nahoře klikni na **Náhled (Preview)** a podívej se, jak to vypadá —
   ideálně i v mobilní variantě.
2. Klikni na modré **Uložit (Save)** nebo **Aktualizovat (Update)**.

**Dokud neuložíš, změny na webu nejsou.** A naopak: jakmile uložíš, jsou tam hned.

> **Když se něco pokazí:** dokud jsi na stránce, vrať se zpět klávesami **Ctrl+Z**
> (**Cmd+Z**). Když už jsi uložil/a, podívej se v pravém panelu na záložce
> **Stránka (Page)** na položku **Revize (Revisions)** — WordPress si pamatuje starší
> verze textu a dá se k nim vrátit. (U stránky, která se ještě nikdy neupravovala,
> tam ta položka není — žádná starší verze zatím neexistuje.) Nepanikař a nepřepisuj
> to ručně.

---

## 10. Nahrát obrázky

### 10.1 Nejdůležitější věc: fotky z foťáku jsou moc velké

Fotka z foťáku nebo nového telefonu má běžně **5 až 15 MB**. Web tak velké soubory
**nepřijme** — nahrávání se zasekne nebo vypíše chybu o velikosti.

**Než fotky nahraješ, zmenši je.**

Kolik to přesně je, si ověříš takhle:

1. **Média (Media)** → **Přidat soubor (Add Media File)**.
2. Pod přetahovacím polem je věta **„Maximum upload file size: … MB"**. To je strop.

### 10.2 Jak fotku zmenšit

**Na čem** nezáleží. Cíl je vždycky stejný:

- **delší strana zhruba 2000 pixelů** — to bohatě stačí i na obrázek přes celou šířku
- **formát JPG** (ne PNG, ne HEIC z iPhonu, ne RAW)
- **velikost souboru pod strop** z kroku výš

Čím to zmenšit:

- **Mac:** otevři fotku v Náhledu (Preview) → Nástroje → Upravit velikost
- **Windows:** otevři ve Fotkách → tři tečky → Změnit velikost
- **Telefon:** při sdílení vyber menší rozlišení, nebo použij aplikaci na zmenšení
- **Online:** jakákoliv služba na zmenšení obrázků. **Nenahrávej tam ale fotky lidí,
  které ještě nebyly zveřejněné.**

> Nezmenšené fotky nejsou jen technický problém. Stránka s nimi se na mobilu v davu
> na festivalu načítá pomalu nebo vůbec — a to je přesně okamžik, kdy ji lidi potřebují.

### 10.3 Fotka na úvodní stránce

Velký pás nahoře na úvodu může mít místo zelené barvy fotku. Mění se jako kterákoli
jiná fotka na webu:

1. **Stránky → Úvod** (klikni na název).
2. Vpravo v boxu **Náhledový obrázek** klikni na **Nastavit náhledový obrázek**.
3. Vyber nebo nahraj fotku a ulož stránku tlačítkem **Aktualizovat**.

Hotovo — fotka se objeví za nadpisem. Když ji odebereš, pás se vrátí k zelené.

> **Fotku vybírej na šířku a spíš tmavší.** Text přes ni jde bílý. Přes fotku se sice
> automaticky překládá tmavý závoj, aby byl text čitelný, ale na úplně bílé obloze to
> stejně nebude ono. Ideální šířka je kolem 2000 px — větší jen zpomalí načítání
> (viz [10.1](#101-nejdůležitější-věc-fotky-z-foťáku-jsou-moc-velké)).

> **Důležité je, co je uprostřed.** Fotka se ořízne na výšku pásu a na mobilu je z ní
> vidět užší výřez. Co je na kraji, nemusí být vidět.

### 10.4 Nahrát do knihovny

1. **Média (Media)** → **Přidat soubor (Add Media File)**.
2. Přetáhni soubory do pole, nebo klikni na **Vybrat soubory (Select Files)**.
3. Počkej, až doběhne proužek.

Obrázky se dají nahrát i rovnou při psaní — z boxu **Náhledový obrázek (Featured image)**
nebo z bloku **Obrázek (Image)**. Skončí ve stejné knihovně.

### 10.5 Pojmenuj soubory rozumně

`IMG_4821.jpg` nikomu nic neřekne. Za rok nikdo nepozná, co to je.

Pojmenuj je radši `kral-jan-novak-2027.jpg` nebo `zlaty-most-vecer.jpg`.
Bez diakritiky, bez mezer, malá písmena, slova spojená pomlčkou.

### 10.6 Alternativní text

Po nahrání klikni na obrázek v knihovně a vyplň vpravo **Alternativní text (Alt text)**.
Jedna věta o tom, co je na obrázku.

Čtou to lidé, kteří na obrázek nevidí, a vyhledávače. U fotek z festivalu to zabere
pět vteřin a je to součást toho, co slibuje stránka o přístupnosti.

### 10.7 Co nemazat

**Nemaž obrázky z knihovny jen proto, že se ti nezdají.** Mohou být použité na stránce,
o které nevíš — pak tam zůstane prázdné místo. Když něco opravdu překáží, zeptej se
správce.

---

## 11. Nastavit nový ročník

**[jen Admin]** — role Dramaturgie na ročníky nedosáhne.

Ročník drží pohromadě všechno, co k danému roku patří: akce, krále, aktuality.
A nese datumy festivalu pro úvodní stránku.

**Tohle se dělá jednou za rok.** Je to první věc, kterou nová sestava na webu udělá.

### 11.1 Založit ročník

1. V levém menu **Akce (Events)** → **Ročníky**.
   (Stejná obrazovka je i pod **Příspěvky (Posts)** → **Ročníky** a pod
   **Králové** → **Ročníky**. Je to jedno a totéž.)
2. Vlevo je formulář **Přidat ročník**.
3. **Name** — jen rok, například **2028**. Nic víc, žádné „Majáles 2028".
4. **Slug** — nech prázdné.
5. **Parent Category** — nech **None**. Ročníky nejsou vnořené.
6. **Description** — nepovinné.
7. **Datum od** — klikni do pole, vyskočí kalendář, vyber **první den festivalu**.
8. **Datum do** — stejně vyber **poslední den festivalu**.
9. **Text na homepage** — volný text s termínem, který se vypisuje na úvodní stránce
   nad nadpisem a v běžícím pásu. Napiš ho přesně tak, jak to mají lidi vidět,
   i s mezerami a pomlčkou: `30. 5. – 5. 6. 2027`

   > Když ho necháš prázdný, web si termín poskládá z **Datum od** a **Datum do**
   > sám. Vyplň ho jen tehdy, když chceš jiné znění.
10. Klikni na **Přidat ročník**.

> **Zakládej ročník jen jednou.** Když se překlepneš, **uprav existující** termín —
> nedělej druhý. Dva ročníky „2028" by rozdělily program na dvě poloviny.

### 11.2 Opravit datumy

1. **Akce (Events)** → **Ročníky**.
2. Klikni na rok.
3. Oprav **Datum od**, **Datum do** nebo **Text na homepage**.
4. Klikni na **Aktualizovat (Update)**.

Datum se tu zobrazuje česky (`30. 05. 2027`), ale i tak ho **vybírej z kalendáře**,
nepiš ručně.

### 11.3 Co udělat hned potom

Nový ročník sám o sobě nic neudělá. Potom je potřeba:

1. **Zaškrtnout nový ročník u každé nové akce** ([sekce 2.5](#25-žánr-a-kategorie)).
2. **Zaškrtnout ho u nových králů** ([sekce 6.1](#61-založit-kandidáta)).
3. **Zaškrtnout ho u nových aktualit** ([sekce 8.1](#81-jak-na-to)).
4. **Projít místa konání** — které scény letos nejsou, které přibyly
   ([sekce 3](#3-přidat-místo-konání)).
5. **Aktualizovat úvodní stránku** — text a obrázky z loňska
   ([sekce 9](#9-upravit-text-stránky)).

**Loňské akce a krále nemaž.** Mají svůj ročník, podle něj se odfiltrují. Archiv je
hodnota, ne smetí — a smazat se dá vždycky, vrátit ne.

---

## Co nedělat

Tenhle web přebírá **každý rok nebo dva nová sestava**. Nikdo z nich nezná historii, nikdo
nebude mít čas luštit, proč je něco udělané jinak, než popisuje návod. Každá úprava navíc
je dluh, který splatí někdo, kdo tu ještě není.

Proto:

### Neinstaluj pluginy

Pluginy jsou doplňky. Vypadá to lákavě („přidám galerii, bude to hezčí"), ale každý
nový plugin může rozbít ty stávající, zpomalit web nebo otevřít díru, kterou se někdo
dostane dovnitř. Weby se nejčastěji hackují přes zapomenutý plugin.

Navíc: plugin, který nainstaluješ ty, budou spravovat lidé po tobě. A nebudou vědět proč.

**Potřebuješ něco, co web neumí? Napiš správci.** Je lepší to probrat než to zkusit.

### Neměň nastavení

Celá sekce **Nastavení (Settings)**, dále **SCF**, **Members** a **Agent Abilities for
MCP** jsou pro tebe zakázané území. Je tam adresa webu, formát datumů, způsob tvorby
adres stránek a definice všech polí, která používáš.

Jedna změna na špatném místě (třeba v **Trvalé odkazy / Permalinks**) **shodí najednou
všechny adresy webu**. Všechny odkazy, které kdokoliv někam poslal, přestanou fungovat.

### Nemaž stránky

Jednadvacet stránek má **hotovou navigaci** — menu na ně odkazuje. Smazaná stránka
znamená mrtvý odkaz v menu a chybovou stránku pro návštěvníka.

Některé stránky navíc **musí existovat ze zákona nebo kvůli slibu návštěvníkům** —
GDPR, Přístupnost, Kontakt.

Stránka, která se letos nehodí? **Nech ji a přepiš text.** Mazání neřeš.

### Nesahej na vzhled

Sekce **Vzhled (Appearance)** — Témata (Themes), Editor, Fonty (Fonts), Přizpůsobit
(Customize). Tady se mění, jak celý web vypadá.

V Editoru se dá jedním kliknutím změnit šablona pro **všechny** stránky naráz. Vrátit
se to zpátky dá hůř, než se to rozbije. A barvy a písma jsou navržené tak, aby na sebe
seděly — jedna „jen trochu jiná" barva rozbije celek.

### Nezakládej uživatele a nesdílej hesla

Sekce **Uživatelé (Users)** je jen pro správce. Nový člověk v týmu = nový účet s rolí
**Dramaturgie**, který založí správce. Ne tvoje heslo poslané na Messengeru.

Po festivalu se účty lidí, kteří odcházejí, ruší. To je normální, ne nedůvěra.

### Neklikej na nabídky k nákupu

Pluginy ve správě ukazují reklamu na placené verze — „Events Calendar Pro",
„Event Tickets Plus", „Set up Tickets Commerce". **Nic z toho nekupuj a nespouštěj.**
Platební brána pro festival zdarma nedává smysl.

### Shrnutí jednou větou

**Co není v tomhle návodu, nedělej — zeptej se.** Dotaz stojí pět minut, oprava rozbitého
webu večer před festivalem stojí celý večer.

---

## Když něco nefunguje

### Nejdřív zkus tohle

Zabere to minutu a vyřeší to většinu problémů.

1. **Uložil/a jsi to?** Nejčastější „nefunguje to" je nekliknuté
   **Publikovat (Publish)** nebo **Aktualizovat (Update)**.
2. **Je to publikované, nebo je to koncept?** V seznamu akcí nebo stránek se u konceptu
   píše **Koncept (Draft)**. Koncept na webu nikdo nevidí.
3. **Obnov stránku.** **Ctrl+Shift+R** (na Macu **Cmd+Shift+R**). Tím se načte čerstvá
   verze, ne uložená kopie z prohlížeče.
4. **Odhlas se a přihlas znovu.** Po pár hodinách nečinnosti WordPress odhlásí
   a formulář pak tiše neuloží.
5. **Zkus jiný prohlížeč** nebo anonymní okno. Když to tam funguje, problém je
   v nastavení tvého prohlížeče, ne na webu.
6. **Podívej se na to, co měníš, na veřejném webu** — ne v administraci. Je to jiná
   obrazovka a občas se liší.

### Časté potíže

| Co se děje | Čím to nejspíš je |
|---|---|
| Akce není v programu | Není zaškrtnutý **Ročník**, nebo je akce **koncept** |
| Akce nemá barvu | Je zaškrtnutý jen žánr, ne **kategorie** nad ním ([4.2](#42-jak-akci-zařadit)) |
| Datum je úplně jinde | Datum bylo **napsané ručně**; vyber ho z kalendáře ([2.3](#23-datum-a-čas)) |
| Obrázek se nenahraje | Je **moc velký**, zmenši ho ([10.1](#101-nejdůležitější-věc-fotky-z-foťáku-jsou-moc-velké)) |
| Nikdo se nehlásí na rezervaci | Špatné **Start sale / End sale**; formulář si předvyplní zítřek ([5.2](#52-vyplnit)) |
| Položka v menu mi chybí | Na tu část **nemáš práva** — je to **[jen Admin]** |
| Smazal/a jsem text ve stránce | **Ctrl+Z**, nebo **Revize (Revisions)** ([9.9](#99-uložit)) |

### Koho se zeptat

> **[Doplní zadavatel — bez tohohle je návod neúplný]**
>
> | Role | Jméno | Telefon | E-mail |
> |---|---|---|---|
> | Správce webu | | | |
> | Zástup | | | |
>
> Kam psát dotazy: _(název chatu / kanálu / skupiny)_
>
> Reakce o festivalovém víkendu: _(do kolika hodin, případně nouzové číslo)_

### Než někomu napíšeš

Ušetříš oběma čas, když rovnou pošleš:

1. **Co jsi dělal/a** — „zakládala jsem akci Foo a klikla na Publikovat"
2. **Co se stalo** — doslovné znění chybové hlášky, ne „hodilo to chybu"
3. **Snímek obrazovky** — na Macu **Cmd+Shift+4**, na Windows klávesa **PrtScn**
   nebo **Win+Shift+S**
4. **Adresu stránky**, na které jsi byl/a (zkopíruj z adresního řádku)

### A jedna věc, kterou nikdy nedělej

**Nesnaž se to opravit sám/sama experimentem v nastavení.** Když něco nefunguje,
hledání cesty klikáním po administraci to nejčastěji zhorší — a z pěti minut práce
správce se stane večer.

Napiš. Opravdu to nikoho neštve.

---

*Tenhle návod popisuje web tak, jak stojí. Když narazíš na obrazovku, která vypadá
jinak, než je tu napsané, dej vědět správci — návod je potřeba udržovat, ať slouží
i sestavě po tobě.*
