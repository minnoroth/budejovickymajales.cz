# Přechod na WordPress — analýza

Stav k 21. 9. 2026. Podklad pro schůzku s organizačním týmem Budějovického Majálesu.

- [01-otazky-na-schuzku.md](01-otazky-na-schuzku.md) — co je potřeba rozhodnout, seřazeno podle dopadu
- [02-moduly-detail.md](02-moduly-detail.md) — detailní návrh všech sedmi modulů
- [03-overena-fakta.md](03-overena-fakta.md) — 25 ověřených tvrzení o pluginech, s odkazy na zdroje

---

## Rozhodnutí

Web přejde na **WordPress** s The Events Calendar, Event Tickets a Secure Custom Fields — vše zdarma.
Důvodem není technologie, ale předání: web drží sestava lidí rok, nejvýš dva, a pak se předává dál.
Kritérium proto není „udržovatelné vývojářem", ale **převzatelné cizím člověkem za jeden večer**.

Rozpracovaný Next.js + Strapi se zahazuje. Datový model z něj se přenáší, kód ne.

**Doporučený rozsah: ~45–50 hodin vývoje. Provoz: ~1 750 Kč ročně.**

Práce je pro-bono, takže rozsah řídí jediné kritérium: co nejméně hodin a co nejdřív hotovo.
Úplný seznam přání zadavatele vychází na 193–312 h (viz [02-moduly-detail.md](02-moduly-detail.md)),
ale to není cena za přechod na WordPress — to je cena za nový web se všemi novými funkcemi.
Doporučený rozsah níže dává funkční web a zbytek se doobjednává po položkách.

---

## Co se od minulé verze analýzy změnilo

Původní odhad 49–66 hodin **neplatí**. Vznikl dřív, než byly k dispozici screenshoty BM Adminu
a požadavky od content managera. Po jejich zapracování odhad vyskočil na 193–312 h. Důvody:

| Co jsem podcenil | Dopad |
|---|---|
| Rezervační systém — aktivně používaný, s kapacitou, okny, povinnou rezervací a poznámkami | +24–44 h |
| Editovatelný vzhled — zadavatel chce sám měnit barvy, písma a obsah všech stránek | +42–62 h |
| Dvouúrovňová taxonomie žánrů s barvami, 159 žánrů v datech | součást +42–64 h |
| Přestavba navigace a 4 nové stránky | součást modulu vzhled |
| Převod účtů, role, migrace přes 6 typů obsahu | +26–42 h |

Naopak **odpadlo** to, co jsem dřív považoval za jedinou vlastní věc: hlasování o krále.
Podle FAQ neprobíhá online — body se sbírají offline (studentské jamy, královská hra, výzvy)
a admin je zapisuje ručně. Web jen zobrazuje pořadí.

---

## Výchozí stav

Web běží na **PHP frameworku Nette** na sdíleném hostingu Wedos, s vlastní administrací BM Admin
na `/admin`. Stránka `/lineup` je samostatná aplikace v **AngularJS 1.5.3**, což je dnes už
roky po konci podpory. Galerie jedou na jQuery 3.6 a PhotoSwipe. Eshop je oddělený na Shoptetu
a přechodu se netýká.

Administrace pokrývá Aktuality, Krále, Kartičky, Interprety (Vystoupení / Žánry / Místa),
Rezervace a Nastavení (Počasí, Konání festivalu). Nepokrývá partnery, osvěžovny, FAQ ani statické
stránky — ty jsou v šablonách.

**Data, která se přenášejí:** 102 akcí ročníku 2026, 49 míst, 159 žánrů v 9 kategoriích,
20 kandidátů na krále, 46 log partnerů v 8 sekcích, 24 položek menu osvěžoven, 12 FAQ,
21 ročníků historie (2004–2024), 47 fotek, 15 statických stránek.

> **Pozor na rozpor se zadáním.** Zadání mluví o 6 kategoriích a 52 logách ve 3 úrovních.
> V reálných datech je 9 kategorií a 46 log v 8 sekcích. Je potřeba to na schůzce srovnat.

---

## Cílový stack

Všechno níže je zdarma a ověřené proti oficiálním zdrojům — viz [03-overena-fakta.md](03-overena-fakta.md).

| Vrstva | Volba | Proč |
|---|---|---|
| Hosting | Wedos NoLimit, ~121 Kč/měs | už tam jsou, jen se převede účet na spolek |
| Téma | Kadence (free) | globální paleta a typografie měnitelná klientem v Customizeru |
| Editor | nativní blokový editor + Kadence Blocks | žádný page builder, žádný lock-in shortcodů |
| Program a místa | The Events Calendar (free) | rozsah datum+čas, místa jako záznamy, hierarchická taxonomie, ICS export |
| Rezervace | Event Tickets (free), režim RSVP | kapacita, okna otevření/uzavření, seznam účastníků, export |
| Vlastní pole | **Secure Custom Fields** | viz níže — zásadní nález |
| Role | Members (free) | celé GPL, žádná placená verze neexistuje |
| Přesměrování | Redirection (free) | 301 ze starých anglických adres |
| Obrázky | Imsanity (free) | zmenšování při nahrání, aby fotky z foťáku nezahltily hosting |
| Zálohy | Duplicator Lite (free) | ruční záloha před a po ročníku |

### Dva nálezy, které mění rozpočet

**Secure Custom Fields místo ACF.** SCF je oficiální fork ACF hostovaný přímo na WordPress.org.
Obsahuje Repeater, Options Pages, Flexible Content, Gallery i registraci vlastních typů obsahu
a taxonomií z administrace — **a nemá žádnou placenou verzi**. Totéž v ACF stojí 49 USD ročně
(~1 100 Kč). U webu, který se každý rok předává, je opakovaný nákup přesně ta položka, kterou
jednou nikdo neprodlouží a plugin přestane dostávat aktualizace.

**Event Tickets Plus se už samostatně neprodává.** Liquid Web v roce 2026 sloučil prémiovou řadu
do balíčků Essentials 259 USD/rok, Pro 399 USD/rok a Elite 599 USD/rok. Kterýkoli z nich sám
překračuje celý roční rozpočet. Ve free verzi proto **nejsou** dvě věci:

1. vlastní pole u rezervace (poznámka) — free sbírá jen jméno a e-mail,
2. zrušení rezervace návštěvníkem.

Obojí jde dopsat vlastním kódem (15 h + 12 h), ale je to křehké — sahá to do interního API
pluginu a po každé jeho aktualizaci se to musí otestovat. **Doporučuji obojí nedělat**, dokud
se nepotvrdí, že to někdo reálně používá. Viz první dvě otázky v [01-otazky-na-schuzku.md](01-otazky-na-schuzku.md).

### Ročník řešíme taxonomií, ne dvojicí dat

Analýza vyprodukovala na stejný požadavek dva různé návrhy a v [02-moduly-detail.md](02-moduly-detail.md)
jsou oba: modul Program navrhuje dvě nastavení s datem začátku a konce, modul Homepage taxonomii
`rocnik` s poli u termínu. **Platí taxonomie.** Jeden termín na ročník znamená archiv zdarma,
žádnou datovou aritmetiku a srozumitelné chování pro toho, kdo web převezme:
starý obsah nezmizí, jen se přestane zobrazovat ve výpisech. Datum začátku a konce festivalu
jsou pole u toho termínu, takže se nastavují na jednom místě jako dnes v BM Adminu.

---

## Rozsah po modulech — plný seznam přání

Tahle tabulka je **úplný seznam přání zadavatele**, ne doporučený rozsah. Slouží k odjednávání
po položkách. Co z toho doporučuju udělat teď, je v kapitole [Doporučený rozsah](#doporučený-rozsah).

| Modul | Hodin | Klíčové rozhodnutí |
|---|---|---|
| Program, lineup, žánry, místa | 42–64 | TEC jako datová vrstva, ale vlastní shortcode na frontend — tím padá placený Filter Bar |
| Navigace, stránky, vzhled | 42–62 | Kadence + Customizer, klient si mění barvy sám; žádný page builder |
| Aktuality, homepage, ročník | 30–50 | ročník jako taxonomie s poli, ne jako nastavení; skrývání minulých ročníků dvěma háčky |
| Role, účty, hosting, migrace | 26–42 | převod z Gmailu na spolek jako první krok; import se staví lokálně, Wedos nedává SSH |
| Rezervace | 24–44 | Event Tickets RSVP; rozptyl 20 h závisí jen na odpovědi ohledně zrušení a poznámek |
| Partneři, osvěžovny, galerie | 15–26 | jen stránky z core bloků, žádné vlastní typy obsahu |
| Králové | 14–24 | CPT + Repeater na segmenty bodů se součtem |
| **Celkem** | **193–312** | |

Detailní rozpad včetně mapování polí, seznamu vlastního kódu a poznámek pro toho, kdo web převezme,
je v [02-moduly-detail.md](02-moduly-detail.md).

---

## Doporučený rozsah

Číslo 193–312 h je součet sedmi nezávislých odhadů a má dvě známé slabiny. **Migrace je započtená
dvakrát** — moduly Program (13 h) a Provoz (14+6 h) popisují tentýž importní pipeline, reálně je to
~25 h místo 45 h. A **odhady nikdo nevaliduje**: každý modul odhadoval jiný agent s instrukcí
„počítej, že se WordPress učíš za pochodu". Ber to jako horní hranici, ne jako rozpočet.

Je tu ale zásadnější problém. Ten rozsah obsahuje **161 hodin vlastního kódu**, což je v přímém
rozporu se smyslem celého projektu. Kdyby se to napsalo, příští sestava zdědí custom WordPress —
jen o málo lepší past než dnešní custom Nette. Pravidlo „žádné úpravy nad rámec nutného"
z kapitoly o předání platí i pro tenhle dokument.

### Jedno rozhodnutí, které srazí polovinu: nemigrovat minulost

Ročník 2026 proběhl. Program 2027 se bude zadávat od nuly. Migrace 102 loňských akcí je čistě
archivní práce s nulovou provozní hodnotou.

Řešení: starý web zamrazit na `2026.budejovickymajales.cz` jako archiv — přesně jak už to dělají
s `2019.budejovickymajales.cz`. Do nového webu se přenesou jen věci, které se opakují každý rok:
**místa, žánry s kategoriemi a partneři**.

| Co tím odpadne | Hodin |
|---|---|
| Importní skripty a transformace dat | ~25 |
| Skrývání minulých ročníků (v novém webu žádné nejsou) | ~12 |
| Migrace historie, fotek a králů | ~10 |
| Zrušení rezervace návštěvníkem + poznámky u rezervací | 27 |
| Vlastní časová osa lineupu (první verze = nativní denní výpis) | ~10 |
| Carousely, segmenty bodů, fotogalerie | ~15 |

### Co zbyde

| Práce | Hodin |
|---|---|
| Instalace a konfigurace pluginů | 8 |
| Vzhled: paleta, typografie, hlavička, patička v Customizeru | 8 |
| Mřížka programu v jejich kartičkovém vzhledu (shortcode) | 14 |
| Stránka Králové | 5 |
| Homepage | 6 |
| Plnění obsahu (místa, žánry, partneři, texty stránek) — přes MCP, viz níže | 8 |
| Přesměrování starých URL a ostrý přechod | 4 |
| Testování a opravy na mobilu | 12 |
| **Celkem** | **~65 h, reálně 45–50 h** |

Rozptyl je v tom, kolik obsahu se povede nahrát agentem místo ručně.

Do konce ledna 2027 je to zhruba **3 hodiny týdně**. To se vedle práce na úvazek zvládnout dá.

### Co se tím obětuje

Lineup v první verzi bude nativní výpis po dnech, ne vlastní časová osa. Rezervace bez zrušení
návštěvníkem a bez poznámek. Žádné carousely. Body králů jako jedno číslo, ne rozpad po segmentech.
Program 2026 a historie zůstanou na archivní subdoméně.

Nic z toho není kritické a všechno se dá přidat později. Jestli se to nikdy nepřidá, bude to proto,
že to nikdo nepotřeboval.

**Jak to odjednat na schůzce:** nechoď tam s číslem 193–312. Choď s větou „za zhruba 50 hodin
dostanete funkční web; každé další přání ze seznamu má cenovku v hodinách a vyberete si, které
si koupíte". Rozpad po položkách je v [02-moduly-detail.md](02-moduly-detail.md).

---

## Náklady

| Položka | Ročně |
|---|---|
| Wedos NoLimit (hosting) | ~1 450 Kč |
| Doména .cz | ~300 Kč |
| Všechny pluginy | 0 Kč |
| **Celkem** | **~1 750 Kč** |

Žádná jednorázová investice. Eshop na Shoptetu má vlastní tarif a v téhle kalkulaci není.

Práce je pro-bono. Kdyby se platila, doporučený rozsah 45–50 hodin odpovídá v běžné sazbě zhruba
50–100 tisícům Kč, plný seznam přání 200–450 tisícům. To patří do žádostí o dotace a do jednání
s partnery — je to skutečný věcný dar a má smysl ho takhle vyčíslit.

---

## Migrace dat

**V doporučeném rozsahu se minulost nemigruje.** Starý web zamrzne na `2026.budejovickymajales.cz`
jako archiv a do nového se přenesou jen věci, které se opakují každý rok: **49 míst, žánry
s kategoriemi a partneři**. To je práce na pár hodin přes MCP nebo REST API, ne na importní skript.

Máme sice přístup do databáze (`d66215_bm19pr` na `wm56.wedos.net`) i na FTP, takže plná migrace
přes SQL dump by šla — ale u loňského programu se nevyplatí. Archivní subdoménu už používají
pro ročník 2019, takže je to zavedený postup, ne improvizace.

Jedno omezení, pokud by se plná migrace přece dělala: Wedos na sdíleném hostingu nedává SSH,
takže WP-CLI nejde spustit na serveru. Web by se musel postavit **lokálně** (DDEV nebo Local)
a nahrát celý včetně databáze.

Součástí migrace je **13 pravidel přesměrování 301** ze starých anglických adres
(`/aboutUs`, `/faqs`, `/partners`, `/program/689`) na nové české. Na staré adresy vedou odkazy
z plakátů, Instagramu i z webů partnerů.

---

## WordPress se dá ovládat přímo z Claude Code

Od WordPressu 6.9 je v jádře **Abilities API** — registr toho, co web umí. Nad ním běží
**MCP Adapter**, oficiální balíček WordPressu, který ten registr vystaví jako MCP server.
Ověřeno na živém webu dikyzemuzem.cz, který tyhle endpointy má:

```
/wp-json/mcp/mcp-adapter-default-server
/wp-json/mcp/mcp-oauth-server
/wp-json/wp-abilities/v1/abilities/{name}/run
```

Jádro samo registruje jen tři schopnosti a všechny jsou jen pro čtení. Zápis přidává plugin
**Agent Abilities for MCP** (zdarma, bez placené verze) se 179 schopnostmi: příspěvky, stránky
a vlastní typy obsahu včetně zakládání a úprav, taxonomie, média, menu — a k tomu 13 nástrojů pro
The Events Calendar, 3 pro Event Tickets a 7 pro ACF, tedy přesně pro pluginy, které jsme vybrali.
Připojení přes OAuth nebo aplikační heslo vyhrazeného uživatele.

**Proč to sedí zrovna sem:** Wedos nedává SSH, takže WP-CLI nejde spustit na serveru.
MCP přes HTTPS tenhle problém obchází.

**Co to ušetří:** plnění obsahu. Místa, žánry, partneři a texty stránek se nahrají agentem
místo ručního klikání nebo psaní importních skriptů — odhadem 25 h práce na 8 h.

**Co neušetří:** shortcode s mřížkou programu, CSS, nastavení vzhledu, rozhodnutí o designu
a testování na mobilu. To zůstává ruční prací.

### Dvě výhrady

**Ověřit hands-on.** Číslo 179 schopností je z popisu pluginu, ne z vlastního testu. Oficiální
MCP Adapter je záměrně jen pro čtení. Hodina zkoušení na lokální instalaci to potvrdí nebo vyvrátí,
než na tom postavíme plán.

**MCP nesmí být součást předání.** Je to nástroj pro stavbu, ne náhrada použitelné administrace.
Příští sestava nemusí mít Claude ani vědět, co MCP je — web musí jít spravovat klikáním.
Po spuštění nechat plugin v režimu „jen pro čtení", nebo ho vypnout a zapínat ad hoc.
Plugin má přepínač read-only, allowlisty po rolích a audit log, takže se to dá držet pod kontrolou.

---

## Role a účty

Zadání chce dvě role a WordPress je umí bez jediné řádky kódu, přes plugin Members:

- **Admin 1** — nativní Administrator. Vedení a marketing, přístup ke všemu.
- **Admin 2** — vlastní role „Dramaturgie". Pouze program, králové a rezervace.
  Nedostane se ke stránkám, vzhledu, pluginům ani uživatelům.

**Účty jsou dnes největší riziko celého projektu.** Hosting, doména, databáze i FTP visí na
osobním Gmailu `webmajales@gmail.com` jedné osoby. MX záznamy navíc míří na Google Workspace,
takže i e-mail má stejný jednobodový selhávací uzel. Převod na spolek (IČO, bankovní účet,
telefon statutára kvůli ověřovacím SMS) je **krok číslo jedna, ne poslední**.

---

## Vzhled a branding — co potřebuju od vás

V repu leží `docs/design-system.md` — kompletní design systém (tmavý, zlatá a korálová).
**Nepovažujte ho za rozhodnutý.** Vznikl pro zahozenou Next.js verzi a neodpovídá tomu,
co jste popsali (grafika 2024, majálesový font).

Současný web jede na modré `#0000D3` a růžové `#ff0055`. Ze složky na Disku mám jen názvy
(`font_archiv`, `logo_bm`, `plakáty`) — paletu z toho nevyčtu.

Potřebuju:

1. **Font.** Kdo koupil licenci a je to *webfont* licence, nebo jen desktopová pro grafika?
   Pokud webfont licenci nemáme, navrhuji náhradu z Google Fonts (Poppins, Outfit, Figtree) —
   vyměníme licenční riziko za možnost měnit si font sami z rozbalovátka.
   Bonus: vyřeší to i dnešní GDPR problém, kdy se Space Mono tahá z Google CDN.
2. **Paletu**, ideálně definovanou tak, aby vydržela několik ročníků. Branding jednotlivého
   ročníku se pak mění nad ní.
3. **Texty pro 4 nové stránky** — Pro návštěvníky, Přístupnost, Doprava, Fotogalerie.
   Tohle je nejčastější důvod skluzu. Kdo je napíše a do kdy?

---

## Rizika

| Riziko | Dopad | Ošetření |
|---|---|---|
| Účty zůstanou na osobním Gmailu | ztráta přístupu k webu i e-mailu | převod na spolek jako první krok |
| Předání nové sestavě po roce či dvou | web zamrzne | předávací protokol, screencasty, standardní platforma |
| Rezervace se nepřenesou 1:1 | nefunkční rezervace před festivalem | otestovat na loňských datech do března |
| Rozsah 193–312 h vedle práce na úvazek | nestihne se do ledna | zúžit MVP, předat migraci obsahu týmu |
| Vlastní kód nad Event Tickets | rozbije se při aktualizaci pluginu | nedělat ho, dokud se nepotvrdí, že to někdo používá |
| Licence majálesového fontu | právní riziko | dohledat fakturu, nebo nahradit OFL fontem |
| Přibývání pluginů | křehkost | strop dvanáct pluginů, nový schvaluje administrátor |

---

## Podmínky předání

Web se dá postavit. Rozhoduje se ale jinde: sestava, která ho převezme za rok nebo dva,
ho buď zvládne, nebo na něj přestane sahat.

1. **Všechny účty na spolkový e-mail.** Doména, hosting, DNS, WordPress, zálohy, Google Workspace.
2. **Tři role, ne jedna.** Dva administrátoři z vedení, kteří se nemění s každou sestavou.
3. **Předávací protokol.** Jedna A4: kde co běží, jaké účty existují a kdo je vlastní,
   co se dělá před festivalem, na koho volat.
4. **Screencasty místo dokumentace.** Pět krátkých videí: přidání akce, nahrání partnera,
   spuštění rezervací, založení nového ročníku, nahrání galerie.
5. **Checklist „co dělat v březnu"** — ročníkový reset na jednu stránku.
6. **Školení na dvě hodiny** s lidmi, kteří budou obsah opravdu plnit.
7. **Žádné vlastní úpravy nad rámec nutného.** Současný web je ukázka toho, co se stane,
   když se tohle pravidlo deset let porušuje.

---

## Poznámka k repozitáři

`AGENTS.md` v kořeni je zastaralý — tvrdí, že web běží na WordPressu (neběží, běží na Nette)
a předepisuje Next.js + Strapi, což se touto analýzou opouští. Před začátkem práce ho přepsat.
