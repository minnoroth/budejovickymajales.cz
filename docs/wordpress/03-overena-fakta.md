# Ověřená fakta o pluginech

25 tvrzení ověřeno proti oficiálním zdrojům (wordpress.org, dokumentace výrobců), 1 vyvráceno. Ověřovací agenti měli za úkol tvrzení **vyvrátit**, ne potvrdit.

Ceny a verze platí k datu analýzy. Před nákupem čehokoli ověř znovu.

---

## Program, lineup, žánry, místa

### ✅ The Events Calendar

**Verdikt:** POTVRZENO

**Ověřované tvrzení:** Free verze The Events Calendar 6.x obsahuje podle stránky na wordpress.org „Saved venues & organizers", „Events Taxonomies (Categories & Tags)" a „Google Calendar and iCal exporting"; zkratky (shortcodes), opakující se události a vlastní pole jsou označeny jako Pro.

**Co umí free verze:**

Co je zdarma a projekt to pokrývá: vlastní typ obsahu událost s rozsahem Start/End (datum i čas, případně celodenní), místa (Venues) a pořadatelé (Organizers) jako samostatné znovupoužitelné záznamy s výběrem přes rozbalovací seznam s vyhledáváním přímo v administraci, hierarchické Event Categories (potvrzeno i ve zdrojáku: 'hierarchical' => true) plus ploché Tags, zaškrtávátko Feature Event, export do Google Calendar a iCal/ICS, REST API endpointy a template tagy/hooky. Na požadovaný datový model a administraci tedy stačí free verze a nic z toho není omezené.

Co je placené (relevantní upozornění): opakující se události a série, shortcodes, další zobrazení kalendáře, pokročilé widgety, Additional Fields (vlastní pole událostí) a více míst u jedné události. Pozor na cenu — tvrzení mluví o „Pro", ale samostatná licence Events Calendar Pro se už neprodává; Liquid Web sloučil prémiovou nabídku do balíčků a nejlevnější, který Pro funkce odemyká, je Essentials za 259 USD/rok (Pro 399 USD/rok, Elite 599 USD/rok). To je řádově tisíce Kč ročně, ne stovky. Pro rozpočet studentského festivalu to znamená: pokud návrh zůstane u výše uvedených free funkcí, je to v pořádku; jakmile by kdokoli v budoucnu potřeboval Additional Fields nebo opakující se události, je to skok na 259 USD/rok, ne drobný doplatek.

Tři praktické háčky, které nejsou licenční, ale zdrží implementaci:
1) Vlastní pole jsou placená. Jakákoli metadata k vystoupení navíc (odkaz na kapelu, Spotify, odkaz na vstupenky) je nutné řešit přes ACF nebo CMB2 (zdarma) nebo vlastním meta boxem. Neplánujte je na TEC Additional Fields.
2) ICS/Google export je zdarma, ale generuje se jako frontendové odkazy (parametr ?ical=1 nad výpisy a „add to calendar" odkazy u jednotlivé události, navěšené na template_redirect). Když se frontend pluginu nepoužívá, endpoint dál funguje, ale odkazy si musíte poskládat sami, případně si ICS vygenerovat z dat pluginu. Licenčně zdarma, automaticky to ale nepřijde.
3) Jedno místo na událost je zdarma, více míst u jedné události je Pro (od 6.2.0). Pro festivalové vystoupení obvykle nevadí.

Doplňková poznámka k udržitelnosti: TEC 6.x je poměrně těžký plugin (od 6.0 vlastní databázové tabulky pro série, upsell prvky a telemetrie v administraci). Pokud se frontend pluginu skutečně nepoužívá, stojí za zvážení, jestli prostý vlastní typ obsahu + ACF (obojí zdarma, výrazně méně pohyblivých částí při každoročním předávání webu) není levnější na údržbu než celý TEC. To ale nemění nic na verdiktu — tvrzení o rozdělení free/placené je přesné.

**Náhrada:** Tvrzení platí, náhrada není nutná. Pouze pro vlastní pole u vystoupení použijte ACF nebo CMB2 (zdarma) místo placených TEC Additional Fields.

**Zdroj:** https://wordpress.org/plugins/the-events-calendar/

---

### ✅ The Events Calendar (free, wordpress.org) — Featured Event / Featured Events

**Verdikt:** POTVRZENO

**Ověřované tvrzení:** Checkbox „Feature Event" v sekci Event Options je ve free verzi TEC 6.x; free widget Events List má volbu „Limit to featured events only". Události lze dotázat přes tribe_events()->where('featured', true) nebo meta klíč _tribe_featured = 1.

**Co umí free verze:**

ZDARMA (ověřeno v kódu 6.17.5): checkbox „Feature Event" v Event Options, meta `_tribe_featured`, zvýraznění featured událostí ve výchozích views (Month/List/Day), widget Events List včetně „Limit to featured events only", ORM `tribe_events()->where('featured', true)`, REST parametr `featured`, CSV import sloupce, Elementor query control „Only Featured Events". Pro účel projektu („Top představení" bez vlastního kódu) je tedy potřeba přesně nula korun.

PLACENÉ (a pro tento účel nepotřebné): filtr featured událostí v kalendáři přes add-on Filter Bar; shortcode `[tribe_events]` a pokročilé widgety (Events Calendar Pro); opakující se události, další views. Ceník (liquidweb.com/software/the-events-calendar/, převzato od Liquid Web): Essentials 259 USD/rok, Pro 399 USD/rok, Elite 599 USD/rok. Starší údaj „89 USD" koluje jen ve fórových vláknech z let 2016-2018, neplatí.

JEDINÁ REÁLNÁ PAST v tvrzení (netýká se free/placené, ale formulace „blok na homepage"): Events List je klasický WP widget, ne Gutenberg blok. Nelze ho vložit do obsahu stránky v editoru — patří do widget area (Vzhled → Widgety, v blokových šablonách přes blok „Legacy Widget" / „Starší widget"). Free shortcode ani nativní blok pro výpis událostí v TEC neexistuje (shortcody jsou Pro). Pokud má být sekce „Top představení" přímo v obsahu homepage, bude to buď widget area v šabloně, nebo pár řádků vlastního kódu s ORM — což tvrzení ostatně samo předpokládá.

**Náhrada:** Náhrada není potřeba, tvrzení platí. Doporučení k realizaci homepage bloku: (1) nejjednodušší bez kódu — widget area/„Legacy Widget" s Events List a zaškrtnutým „Limit to featured events only" (limit 3-5); (2) když musí být sekce přímo v obsahu — vlastní blok/šablona s `tribe_events()->where('featured', true)->where('starts_after', 'now')->order_by('event_date')->per_page(4)->all()`; (3) headless/JS varianta — free REST endpoint `/wp-json/tec/v1/events?featured=1`. Do předávací dokumentace pro dalšího správce festivalu stačí věta: „Top představení = zaškrtnout Feature Event v pravém panelu editace události."

**Zdroj:** https://plugins.svn.wordpress.org/the-events-calendar/tags/6.17.5/

---

### ✅ The Events Calendar (free, wordpress.org) – ICS / Add to calendar, verze 6.17.5

**Verdikt:** POTVRZENO

**Ověřované tvrzení:** Free TEC 6.x zobrazuje na detailu události odkazy „Add to calendar" (Google / iCalendar / Outlook) a URL detailu s parametrem ?ical=1 vrací soubor .ics s korektním DTSTART/DTEND a časovou zónou.

**Co umí free verze:**

FREE (0 Kč) pokrývá celý požadavek „přidání do kalendáře" bez jediné řádky kódu. Nic z toho není v placené verzi.

Co přesně dostanete zdarma na detailu události:
- Tlačítko/dropdown „Add to calendar" s položkami: Google Calendar, iCalendar, Outlook 365, Outlook Live (všechny defaultně zapnuté)
- URL `https://web.cz/event/nazev-akce/?ical=1` vrací platný .ics s `Content-type: text/calendar`, `DTSTART;TZID=Europe/Prague:...`, `DTEND;TZID=...` a VTIMEZONE blokem

Tři praktické zádrhely, o kterých je dobré vědět (žádný z nich tvrzení nevyvrací):
1. Položka „Add to iCalendar" v dropdownu je **subscribe odkaz ve schématu `webcal://...?ical=1`**, ne přímé stažení (Link_Abstract.php ~ř. 335 přepisuje https → webcal). Na macOS/iOS a desktop Outlooku funguje, na Androidu nebo v prohlížeči bez registrovaného webcal handleru nemusí udělat nic. Prosté `https://...?ical=1` ale .ics vrátí vždy – takže když chcete jistotu, dá se odkaz vložit ručně do šablony/textu.
2. Položka „Export .ics file" (přímé stažení) je **na detailu události defaultně skrytá** (iCalendar_Export.php: `return ! is_single();`) – je určená pro přehledové views (měsíc/seznam). Zobrazit ji na detailu chce jednořádkový filtr. Na přehledu kalendáře ale funguje zdarma bez čehokoliv.
3. TZID se bere z timezone WordPressu (nebo per-event, pokud je ten režim zapnutý). Pro web, který každý rok přebírá někdo jiný: v Nastavení → Obecné musí být časové pásmo nastavené jako **Europe/Prague**, ne jako UTC+1/UTC+2. Při číselném offsetu dostanou klienti numerické TZID a akce po změně letního času „ujede". To je konfigurační past, ne chyba pluginu.

Cena placené verze (jen pro kontext, není potřeba): oficiální produktová stránka (theeventscalendar.com redirectuje na liquidweb.com, StellarWP/Liquid Web) uvádí tier **Pro za 399 USD/rok**; existuje i levnější tier „Essentials", jehož cenu se mi z oficiálního zdroje nepodařilo přečíst (výsledky vyhledávání uváděly 149/199 USD, to jsem neověřil a necituji jako fakt). Placené add-ony (Events Calendar Pro, Event Aggregator, Event Tickets) přidávají opakující se události, další views, shortcody, vlastní pole, importy z Meetupu/Eventbrite a prodej vstupenek – **nic z toho nesouvisí s exportem do kalendáře**.

**Náhrada:** Není potřeba – tvrzení platí. Kdyby v budoucnu bylo nutné mít na detailu události přímé stažení .ics místo webcal odkazu, řeší to jeden filtr `tec_views_v2_subscribe_link_ics_visibility` (vrátit true), případně ruční odkaz na `?ical=1`. Placená verze v tom nepomůže.

**Zdroj:** https://wordpress.org/plugins/the-events-calendar/ | https://plugins.svn.wordpress.org/the-events-calendar/tags/6.17.5/src/Tribe/iCal.php | https://plugins.svn.wordpress.org/the-events-calendar/tags/6.17.5/src/Tribe/Views/V2/iCalendar/iCalendar_Handler.php | https://plugins.svn.wordpress.org/the-events-calendar/tags/6.17.5/src/Tribe/Views/V2/iCalendar/Links/ | https://plugins.svn.wordpress.org/the-events-calendar/tags/6.17.5/src/views/single-event.php | https://plugins.svn.wordpress.org/the-events-calendar/tags/6.17.5/src/Tribe/Editor/Blocks/Event_Links.php | https://www.liquidweb.com/software/the-events-calendar/wordpress-events-calendar/

---

### ✅ The Events Calendar — Filter Bar

**Verdikt:** POTVRZENO (s jednou korekcí v označení "samostatný")

**Ověřované tvrzení:** Filter Bar je samostatný placený doplněk a free TEC neumí ve svých vlastních výpisech filtrovat podle kategorie a místa současně — proto se nativní výpisy nepoužijí a vykresluje se vlastní mřížka.

**Co umí free verze:**

ZDARMA (The Events Calendar 6.17.5 z wordpress.org):
- Pohledy Měsíc, Seznam, Den (+ Latest Past)
- Kategorie a štítky událostí (taxonomie) vč. archivních URL typu /events/category/<slug>/ — tj. filtr podle JEDNÉ kategorie přes URL
- Místa a organizátoři jako datové entity (uložené, opakovaně použitelné), zobrazení na detailu události
- Lišta nad výpisem: pouze fulltextové hledání + výběr data + přepínač pohledu
- Widget "Nadcházející události", REST API, iCal/Google export, JSON-LD, blokový editor
- Vývojářské ORM `tribe_events()` s filtry category, venue, organizer, date — kombinovatelnými (toto je klíč pro vlastní shortcode)

PLACENÉ (tarif Essentials 259 USD/rok, Pro 399 USD/rok, Elite 599 USD/rok — zdroj liquidweb.com):
- Filter Bar: front-end filtry pro návštěvníka (kategorie, místo, organizátor, štítek, cena, den v týdnu, denní doba, město/stát, datum), volitelně kombinovatelné
- Shortcode kalendáře / výpisů pro vkládání na libovolnou stránku
- Pohledy Týden, Foto, Mapa, stránky Místa a Organizátora
- Opakující se události, vlastní pole

POZOR — Filter Bar se od roku 2026 neprodává samostatně; je součástí tarifů. Nejlevnější cesta k němu je tedy 259 USD/rok (cca 5 500–6 000 Kč ročně), což je pro studentský festival s minimálním rozpočtem neúnosné, navíc s rizikem, že nástupce předplatné neobnoví a filtrování přestane fungovat.

**Náhrada:** Tvrzení není potřeba vyvracet, jen upřesnit formulaci na: "Filter Bar je placená funkce dostupná pouze v tarifech The Events Calendar (od 259 USD/rok; samostatně se od roku 2026 neprodává) a free TEC neumí ve svých vlastních výpisech filtrovat podle kategorie a místa současně — proto se nativní výpisy nepoužijí a vykresluje se vlastní mřížka."

Zvolené řešení (vlastní shortcode) je pro tento projekt správné a doporučuji u něj zůstat — s jednou poznámkou pro udržitelnost: vlastní mřížka by měla data tahat přes oficiální free ORM `tribe_events()->where('category', ...)->where('venue', ...)` (viz src/Tribe/Repositories/Event.php), ne přes ruční WP_Query s meta_query. ORM je veřejné, dokumentované API free verze, přežije upgrady pluginu a další správce webu ho dohledá v dokumentaci.

Pokud by v budoucnu někdo chtěl hotové vícenásobné filtrování bez psaní kódu a bez placení, existuje plně zdarma plugin Events Manager (wordpress.org/plugins/events-manager/), který má vestavěný vyhledávací formulář s filtry podle kategorie i lokality — znamenalo by to ale migraci pryč od TEC, což u fungujícího webu nedoporučuji.

**Zdroj:** https://www.liquidweb.com/software/the-events-calendar/

---

### ✅ Advanced Custom Fields (ACF®) – free, verze 6.8.10 (WP Engine)

**Verdikt:** POTVRZENO (se dvěma upřesněními v detailech)

**Ověřované tvrzení:** ACF free umí navěsit skupinu polí na termíny taxonomie (pravidlo umístění „Taxonomy Term"), obsahuje typ pole Color Picker a podporuje Local JSON sync přes složku acf-json uvnitř pluginu. Repeater, Options Pages, Gallery, Flexible Content a Clone jsou pouze v PRO.

**Co umí free verze:**

ZDARMA (ověřeno v kódu 6.8.10): 30+ typů polí včetně Color Picker, True/False, Select, Link, Image, Date Picker, Group, Tab, Accordion, Taxonomy. Location rules včetně Taxonomy (termíny), Post Type, User, Comment, Attachment. Local JSON (verzovatelné .json definice polí v gitu). Registrace custom post types a taxonomií přes UI. Export do PHP.

PLACENÉ (ACF PRO, ceny z https://www.advancedcustomfields.com/pro/, roční, bez DPH): Personal 49 USD/rok (1 web), Freelancer 149 USD/rok (10 webů), Agency 249 USD/rok (neomezeně). Za ty peníze: Repeater, Flexible Content, Gallery, Clone, Options Pages, ACF Blocks.

KONKRÉTNĚ PRO VÁŠ PROJEKT: Barva u termínů, přepínače „V carouselu" a „Bezbariérovost", select „Země původu" — všechno free, bez kompromisu. POZOR na „doplňkové odkazy" v množném čísle: pokud jde o proměnlivý počet odkazů, to je učebnicový případ pro Repeater = PRO za 49 USD/rok. Free řešení bez placení: (a) pevný počet polí typu Link, např. „Odkaz 1–3", případně zabalené do pole Group — postačí ve většině případů; (b) jedno Textarea, kde je na řádek „popisek | URL", a parsování v šabloně; (c) pokud je opravdu potřeba plnohodnotné opakování zdarma, použijte místo ACF plugin Meta Box (free verze umí cloneable/repeatable pole) nebo Carbon Fields (knihovna zdarma pod MIT, má Complex Field = ekvivalent Repeateru, včetně umístění na termíny taxonomie). Pro web, který každý rok přebírá někdo jiný, ale doporučuji spíš variantu (a): tři pevná pole Link pochopí i nováček, kdežto míchání dvou field frameworků je dlouhodobě dražší než 49 USD.

**Náhrada:** Tvrzení nebylo vyvráceno, náhrada pluginu není potřeba. Nutné úpravy tvrzení: (1) pravidlo se jmenuje „Taxonomy", ne „Taxonomy Term"; (2) acf-json je výchozí v šabloně, do vlastního pluginu ho dostanete až filtry acf/settings/save_json + acf/settings/load_json. Jediné reálné riziko: „doplňkové odkazy" s proměnným počtem = Repeater = PRO 49 USD/rok — řešte pevným počtem polí Link, nebo sáhněte po Carbon Fields / Meta Box, kde je opakovatelné pole zdarma.

**Zdroj:** https://wordpress.org/plugins/advanced-custom-fields/ ; https://downloads.wordpress.org/plugin/advanced-custom-fields.6.8.10.zip (inspekce zdrojového kódu) ; https://www.advancedcustomfields.com/pro/ ; https://www.advancedcustomfields.com/resources/local-json/

---

## Králové a body ze soutěží

### ✅ Secure Custom Fields (SCF)

**Verdikt:** POTVRZENO

**Ověřované tvrzení:** Secure Custom Fields 6.9.x (oficiální fork ACF hostovaný na WordPress.org) umí na čisté instalaci přidat pole typu Repeater a Options Page bez jakékoli licence — ověřit vytvořením field groupy s Repeaterem přímo v UI; naproti tomu v ACF free z advancedcustomfields.com je Repeater placená funkce (PRO).

**Co umí free verze:**

SCF nemá žádnou placenou verzi — neexistuje "SCF Pro", žádný licenční klíč, žádný upsell. Vše včetně Repeateru, Options Pages, Flexible Content, Gallery a Clone je v jediném balíku na wordpress.org zdarma pod GPL. Pro váš use case navíc sedí i zbytek: stránka pluginu explicitně uvádí "Post type and taxonomy registration directly from interface", takže vlastní typ obsahu Král i taxonomii Ročník nakliká správce v administraci bez řádky kódu, stejně jako Repeater na segmenty bodů.

Pro srovnání, co stojí totéž u ACF: ACF free (advancedcustomfields.com i wordpress.org) Repeater ani Options Pages nemá. ACF PRO stojí 49 USD/rok (Personal, 1 web), 149 USD/rok (Freelancer, 10 webů), 249 USD/rok (Agency). 49 USD ≈ 1 100 Kč ročně — přesně ten typ opakovaného nákladu, který u každoročně přebíraného festivalového webu zpravidla jednou nikdo neprodlouží a plugin přestane dostávat aktualizace.

Dvě poznámky, které verdikt nemění, ale patří do rozhodnutí: (a) SCF je fork, který WordPress.org převzal v říjnu 2024 v rámci sporu s WP Engine — není to produkt původních autorů ACF, ale je udržovaný (poslední vydání srpen 2026) a distribuovaný přímo z wordpress.org, takže aktualizace chodí standardní cestou bez licence. (b) SCF a ACF PRO nejsou licenčně zaměnitelné (nelze z SCF "upgradovat" na ACF PRO), ale data ukládají do stejných postmeta klíčů, takže obsah není uzamčený a případný pozdější přechod na ACF PRO nebo jiné řešení je proveditelný.

**Zdroj:** https://wordpress.org/plugins/secure-custom-fields/

---

### ✅ Members – Membership & User Role Editor Plugin (v3.2.26, wordpress.org/plugins/members/)

**Verdikt:** POTVRZENO (s jednou technickou podmínkou na straně CPT, viz evidence)

**Ověřované tvrzení:** Members (free) umí v UI vytvořit novou roli a zaškrtat jí capabilities vlastního CPT registrovaného s capability_type='kral' (edit_krals, publish_krals, delete_krals…) bez zásahu do PHP — tyto capability se v Members → Add New Role objeví v seznamu.

**Co umí free verze:**

Tady není co vyvracet — Members nemá placenou verzi. Plugin je GPL-2.0, celý zdarma, žádný pro/premium tier, žádný feature gate na role editoru. Readme (řádek 41) doslova: „Members now includes ALL of it's add-ons completely free of charge!“ V balíku je 12 dřív samostatných add-onů, všechny aktivní zdarma: members-acf-integration, members-admin-access, members-block-permissions, members-category-and-tag-caps, members-core-create-caps, members-edd-integration, members-givewp-integration, members-meta-box-integration, members-privacy-caps, members-role-hierarchy, members-role-levels, members-woocommerce-integration.

Cena k uvedení neexistuje — žádná. Placený je jen MemberPress (samostatný produkt, memberpress.com/plans/pricing/), na který readme odkazuje jako na upsell pro placená členství a platby. Pro roli „Admin 2 (dramaturgie)“ ho nepotřebuješ ani náhodou — role editor, více rolí na uživatele, klonování rolí, import/export rolí i content permissions jsou ve free.

Pro festival s nulovým rozpočtem a rotující správou je to tedy bezpečná volba, jen pozor na jedno riziko jiného druhu než cena: plugin drží komerční firma (Caseproof) jako akvizici a živí jím upsell. Export rolí (admin/class-role-export.php) je ve free — doporučuji si exportovaný JSON rolí uložit do repa webu, aby příští ročník neztratil konfiguraci, kdyby se licenční politika změnila.

**Náhrada:** Náhrada není potřeba, tvrzení platí. Dvě konkrétní akce místo hledání jiného pluginu:

1) Pokud `register_post_type('kral', ...)` nemá `'map_meta_cap' => true`, přidej ten jeden řádek — bez něj bude role mít přístup k výpisu, ale ne k editaci konkrétního krále. Když PHP opravdu nechceš sahat, existuje čistě UI obchvat: Members má na obrazovce role box „Custom Capability“ (admin/class-meta-box-custom-cap.php, textové pole + tlačítko Add New), kam ručně dopíšeš edit_kral, read_kral a delete_kral. Funguje to, ale je to ošklivější a hůř dohledatelné pro příští správce než ten jeden řádek v PHP.

2) Zkontroluj capability_type u CPT `program` a `rezervace`. Pokud jsou na defaultu 'post', Members pro ně tab nezobrazí a role je nepůjde oddělit od Příspěvků — tam budeš potřebovat vlastní capability_type stejně jako u krále.

3) Při zakládání role nezapomeň v tabu General zaškrtnout `read`, jinak se uživatel do wp-adminu vůbec nedostane. Přístup do administrace jako takový pak případně doladíš bundled add-onem Admin Access (taky zdarma).

**Zdroj:** https://wordpress.org/plugins/members/ (oficiální stránka pluginu); ověřeno proti staženému balíku https://downloads.wordpress.org/plugin/members.latest-stable.zip v3.2.26 — soubory inc/functions-cap-groups.php, admin/class-role-new.php:360, inc/functions-options.php:141, readme.txt; a proti WP core https://raw.githubusercontent.com/WordPress/WordPress/master/wp-includes/post.php (get_post_type_capabilities) + https://developer.wordpress.org/reference/functions/get_post_type_capabilities/

---

## Rezervační systém

### ✅ Event Tickets (Event Tickets and Registration), theeventscalendar / Liquid Web

**Verdikt:** POTVRZENO

**Ověřované tvrzení:** Event Tickets free 5.29.5 (vyžaduje WP 6.8+ a PHP 7.4+): RSVP lístek má pole Capacity (prázdné = neomezeno), Start sale a End sale s datem i časem a Description; formulář vyplní nepřihlášený návštěvník pouze jménem a e-mailem, bez registrace.

**Co umí free verze:**

ZDARMA POKRYJE CELÉ VAŠE ZADÁNÍ. Ověřil jsem i ty části účelu, které tvrzení samo nezmiňuje:
- RSVP s kapacitou a časovým oknem u každé akce programu — zdarma (viz evidence).
- Seznam účastníků v administraci — zdarma, „Generate sales and attendee reports" v readme.
- Export CSV — zdarma, `src/Tribe/Attendees.php` obsahuje `maybe_generate_csv()` (řádek 476) a nápovědu „…for print and for the CSV export" (řádek 417). Není to funkce Plus.
- Potvrzovací e-mail — zdarma, šablony `src/views/emails/rsvp.php` a `rsvp-not-going.php`; readme FAQ: „Event Tickets automatically sends an email confirmation after attendees register or RSVP for an event."
- Bonus: RSVP funguje i bez pluginu The Events Calendar, na běžných stránkách a příspěvcích.

CO JE AŽ ZA PENÍZE: vlastní pole u účastníka (fieldsets, tj. dotazy typu „na který workshop" nebo dieta), QR check-in, PDF lístky a Apple/Google Wallet, WooCommerce brány, mobilní aplikace pro odbavení.

CENA: Event Tickets Plus se od roku 2026 neprodává samostatně. Liquid Web ho sloučil do balíčků Essentials 259 USD/rok, Pro 399 USD/rok, Elite 599 USD/rok. V porovnávací tabulce na https://www.liquidweb.com/software/the-events-calendar/ má řádek „Event Tickets Plus (full ticketing)" křížek u Essentials a fajfku až u Pro — nejlevnější cesta k funkcím Plus je tedy 399 USD/rok (cca 9 000 Kč). Pro váš rozpočet je to fakticky nedostupné, což je další důvod zůstat na free a nenavrhovat řešení, které by Plus vyžadovalo.

ČTYŘI VĚCI, KTERÉ VÁS ŠTVOU AŽ V PROVOZU (žádná nebourá tvrzení, ale plánujte s nimi):
1. Bez přihlášení není deduplikace ani ověření e-mailu. Kdokoli může odeslat RSVP opakovaně s vymyšlenými adresami a sníst kapacitu. Jediná vestavěná obrana je „Require users to log in before they RSVP", což je přesně ta registrace, kterou nechcete. Že máte RSVP zvlášť u každé akce programu, škodu aspoň lokalizuje. Počítejte s ručním pročištěním seznamu před akcí.
2. Potvrzovací e-maily jdou přes `wp_mail`. Na levném sdíleném hostingu to typicky končí ve spamu nebo se neodešle vůbec — připočtěte SMTP plugin (WP Mail SMTP zdarma) a odesílání přes ověřenou doménu.
3. WP 6.8+ není detail u webu, který každý rok přebírá někdo jiný. Je to závazek držet WordPress aktuální; zapněte automatické aktualizace jádra i pluginu, jinak za dva roky nástupce narazí na nekompatibilitu.
4. Export CSV umí základní data (jméno, e-mail, stav). Export vlastních polí je funkce Plus — ale ta pole stejně ve free nevytvoříte, takže vás to omezí jen tehdy, kdyby vám name+email přestalo stačit.

**Zdroj:** https://wordpress.org/plugins/event-tickets/

---

### ✅ Event Tickets („Event Tickets and Registration", theeventscalendar/StellarWP) — free verze, stable tag 5.29.5, report Attendees (Tickets → Attendees, page=tickets-attendees)

**Verdikt:** POTVRZENO

**Ověřované tvrzení:** V reportu Attendees free verze existuje tlačítko Export do CSV a admin může účastníka smazat, čímž se obsazená kapacita vrátí zpět do volných míst.

**Co umí free verze:**

ZDARMA (ověřeno v kódu free pluginu 5.29.5): report Attendees, tlačítko Export (CSV), Print, E-mail seznamu; sloupce nastavitelné přes Screen Options (promítnou se i do CSV); řádková i hromadná akce Delete; manuální Check in / Undo Check in; Move attendees; RSVP i Tickets Commerce (Stripe/PayPal/Paystack) včetně kapacit a stock countdownu; u obou se po smazání účastníka kapacita vrací mezi volná místa.

DŮLEŽITÉ VÝHRADY PRO PROJEKT (tvrzení nevyvracejí, ale mění očekávání):
- „Zrušení rezervace" = TVRDÉ SMAZÁNÍ. Účastník zmizí z reportu i z budoucích CSV, nezůstává stav „zrušeno". Sloupec Status v tabulce je jen read-only zobrazení (Attendees_Table.php `column_status`) — admin v něm nemůže přepnout RSVP na „Not Going". Pokud chcete auditní stopu, exportujte CSV před mazáním.
- Zda plugin při smazání posílá účastníkovi e-mail o zrušení jsem NEOVĚŘOVAL — neslibujte to.
- Tlačítko Export se vykresluje jen když má akce aspoň jednoho účastníka (`has_items()` check) — u prázdné akce chybí, není to chyba.
- Export smí admin/editor (capability `publish_pages`); běžný „pořadatel" s nižší rolí ho neuvidí, dokud mu roli nezvýšíte nebo nepoužijete filtr.

PLACENÉ (netýká se tohoto požadavku): vlastní registrační pole, mobilní QR check-in appka, Apple Wallet / PDF tickety, WooCommerce, editace údajů účastníkem. Výrobce dnes prodává jen balíčky — oficiální ceník (liquidweb.com/software/the-events-calendar/event-tickets/) uvádí Essentials 259 USD/rok, Pro 399 USD/rok, Elite 599 USD/rok, tj. cca 6 000 Kč/rok a výš. Pro festival s nulovým rozpočtem je dobrá zpráva, že na seznam rezervací + export + zrušení adminem nic z toho není potřeba.

**Náhrada:** N/A — tvrzení potvrzeno, náhrada není potřeba. Doporučení k nastavení: pro „rezervaci" použijte typ RSVP (ne Tickets Commerce) — je bez platební brány a vrácení kapacity při mazání je u něj v kódu explicitně řešené. MVP „zrušení rezervace" pořadatelem = Attendees → řádková akce Delete (nebo hromadně přes Bulk Actions). Pokud později budete chtít měkké zrušení se zachovanou historií, znamená to buď vlastní kód nad hookem `tribe_events_tickets_attendees_table_bulk_actions`, nebo jiný plugin — ne upgrade na Plus, ten tuhle funkci taky nepřidává.

**Zdroj:** https://wordpress.org/plugins/event-tickets/ (ověřeno proti zdrojovému kódu: https://plugins.svn.wordpress.org/event-tickets/tags/5.29.5/src/Tribe/ — Attendees.php, Attendees_Table.php, RSVP.php; a https://plugins.svn.wordpress.org/event-tickets/tags/5.29.5/src/Tickets/Commerce/Hooks.php)

---

### ✅ Event Tickets (The Events Calendar) — placené balíčky

**Verdikt:** POTVRZENO (s jednou drobnou korekcí formulace o URL)

**Ověřované tvrzení:** Oficiální produktová URL Event Tickets Plus přesměrovává na balíčky Essentials $259/rok, Pro $399/rok a Elite $599/rok; samostatná cena Event Tickets Plus nebyla ověřena. 'Custom registration forms' jsou na stránce uvedeny až u tarifu Pro. Kterýkoli z těchto tarifů sám překračuje roční rozpočet 5000 Kč.

**Co umí free verze:**

Free verze Event Tickets (wordpress.org/plugins/event-tickets/) umí: RSVP, prodej lístků přes Tickets Commerce (Stripe, PayPal, Paystack), lístky na příspěvky/stránky/CPT, reporting prodejů a účastníků, potvrzovací e-maily, odpočet skladu, napojení na The Events Calendar.

Obě funkce, které projekt potřebuje, jsou ve free verzi NEDOSTUPNÉ — což ověřuje oficiální dokumentace (docs.nexcess.com, dřívější theeventscalendar.com/knowledgebase/):

1. Vlastní pole u rezervace — free sbírá jen jméno a e-mail. Dokumentace doslova: "If you have Event Tickets Plus, you will also see an option here for collecting attendee information. This feature allows you to request additional information beyond the name and email of the person RSVPing."

2. Zrušení/úprava rezervace návštěvníkem — dokumentace doslova: "Beginning with Event Tickets Plus version 4.2, you can allow users who have made RSVPs for your events to log in and edit their RSVP responses." Ve free verzi tato možnost popsána není. Potvrzuje to i oficiální snippet od vývojáře (gist cliffordp/5bf5a283b2b3ffaf64c119d86613ced0), nazvaný "Event Tickets Plus: Disable attendees' ability to modify their attendee information, their RSVP Going/Not Going status…", který cílí na šablonu tickets-plus/orders-edit-meta — tedy šablonu patřící výhradně Plus verzi.

CENA: Event Tickets Plus se samostatně už neprodává. Je pouze součástí balíčků. Essentials ($259/rok ≈ 5 485 Kč) custom registration forms NEOBSAHUJE, takže reálné minimum pro tento projekt je Pro $399/rok ≈ 8 449 Kč, tj. asi 1,7násobek ročního rozpočtu 5 000 Kč. Elite $599/rok ≈ 12 684 Kč.

Poznámka k citlivosti: u Essentials je odstup od rozpočtu jen ~10 %, takže je závislý na kurzu — pro rozhodnutí je to ale bezpředmětné, protože Essentials potřebnou funkci neodemyká. U Pro je odstup tak velký, že kurzové výkyvy ani případná sleva výsledek nezmění.

ZÁVĚR PRO PROJEKT: zdůvodnění vlastního kódu obstojí. Obě požadované funkce jsou skutečně placené a nejlevnější tarif, který je obsahuje, překračuje rozpočet zhruba 1,7×. Navíc jde o opakovaný roční náklad na webu, který každý rok přebírá někdo jiný — vypršelá licence by znamenala ztrátu funkčnosti rezervací.

**Náhrada:** Nehledáno — tvrzení bylo potvrzeno, krok 3 zadání je podmíněn nepřesností. Jediná doporučená úprava je textová: v dokumentaci rozhodnutí uvést, že přesměrování na balíčky vrací URL základního Event Tickets, zatímco samostatná URL Event Tickets Plus vrací 404, protože produkt už samostatně neexistuje.

**Zdroj:** https://www.liquidweb.com/software/the-events-calendar/event-tickets/

---

### ✅ The Events Calendar (+ Event Tickets)

**Verdikt:** POTVRZENO

**Ověřované tvrzení:** RSVP lístek z Event Tickets lze přidat k akci typu tribe_events; bez The Events Calendar jde RSVP připojit i k běžné stránce nebo příspěvku, takže moduly nejsou natvrdo svázané.

**Co umí free verze:**

ZDARMA (Event Tickets, free na wordpress.org): RSVP lístky na tribe_events i na běžné stránky/příspěvky/vlastní post typy, kapacitní limity (stock), jméno + e-mail účastníka, potvrzovací e-mail s lístkem, přehled účastníků (attendee report), volitelně veřejný seznam účastníků, více typů lístků. Zdarma je i Tickets Commerce (PayPal/Stripe) pro placené lístky — pro RSVP zdarma není potřeba. The Events Calendar je také zdarma (kalendář, měsíc/seznam/den, kategorie akcí).

POZOR — provozní detaily, které dokumentace uvádí a dají se snadno přehlédnout:
- Běžné stránky/příspěvky se musí ručně zaškrtnout v Tickets → Settings → General → "Post types that can have tickets". Typ Event (tribe_events) se do seznamu přidá automaticky, jakmile je The Events Calendar aktivní.
- U RSVP na stránce/příspěvku je pole "Start sale" (začátek přihlašování) POVINNÉ. U akce tribe_events ne — tam se přihlašování spustí publikováním. Klasický zdroj "proč se RSVP nezobrazuje" při použití mimo akce.

PLACENÉ (Event Tickets Plus) — co konkrétně by Program modul nedostal zdarma:
- vlastní registrační pole u přihlášky (např. výběr workshopu, dieta, tričko) — jen základní jméno a e-mail zdarma,
- možnost, aby si účastník svoji RSVP odpověď dodatečně upravil,
- vkládání lístků/RSVP shortcodem na jiné stránky (embed mimo detail akce),
- QR check-in / mobilní skenování na místě, PDF lístky, Apple Wallet,
- napojení na WooCommerce, integrace Zoom.
Dále Events Calendar Pro (placený) přidává opakující se akce a série, další pohledy kalendáře a widgety.

CENA: samostatná cena Event Tickets Plus není na oficiální stránce uvedena; výrobce (Liquid Web) tam nabízí jen tarify od 259 USD/rok (Essentials), 399 USD/rok (Pro), 599 USD/rok (Elite), tj. zhruba od 6 000 Kč ročně. Pro rozpočet studentského festivalu je to reálně mimo hru — free verze zde není "preferovaná", ale jediná varianta.

DOPORUČENÍ K NÁVRHU: Program stavět na tribe_events s RSVP (ne na obyčejných stránkách) — odpadne povinné pole "Start sale", get propojení je automatické a kalendářové views přijdou zdarma. Pokud by festival potřeboval u přihlášky sbírat víc než jméno a e-mail (výběr workshopu apod.), to zdarma NENÍ; řešit buď samostatným free formulářem (např. Forminator / Contact Form 7) vedle RSVP, nebo rovnou zvážit jiné řešení přihlášek, ne upgrade na Plus.

**Zdroj:** https://wordpress.org/plugins/event-tickets/

---

### ✅ Loco Translate

**Verdikt:** POTVRZENO (s podmínkami, které je nutné znát)

**Ověřované tvrzení:** Loco Translate free umí vytvořit vlastní překlad textové domény event-tickets, uložit ho mimo adresář pluginu a tím přežít jeho aktualizaci.

**Co umí free verze:**

Plugin Loco Translate na wordpress.org je zdarma v plném rozsahu — neexistuje "Pro" verze pluginu. Zdarma umíte: skenovat zdroják pluginu a vytáhnout řetězce, vytvořit/upravit .po, zkompilovat .mo i JSON, a hlavně zvolit umístění souboru včetně chráněného custom adresáře wp-content/languages/loco/plugins/. Nic z toho nevyžaduje registraci ani účet.

PLACENÉ JE NĚCO JINÉHO, NEŽ VYPADÁ: na localise.biz najdete ceník FREE / Pro £4,95 / Business £14,95 / Agency £24,95 měsíčně + VAT (free plán do 2 000 překladů, 2 privátní projekty, 10 jazyků). Tyto plány se týkají SaaS platformy Loco pro správu překladů (localise.biz), NE WordPress pluginu. Pro váš případ — ruční přejmenování popisků v RSVP — nepotřebujete z toho nic. Nekupovat.
https://localise.biz/plans

Placené/omezené jsou dále strojové překlady (DeepL, Google, Microsoft, OpenAI) — Loco je umí zavolat, ale API klíč a kredit si platíte u daného poskytovatele. Pro pár popisků irelevantní, přeložíte je ručně.

REALITA ČEŠTINY U EVENT TICKETS: na translate.wordpress.org je český překlad event-tickets hotový zhruba z 19 % (stable). Počítejte s tím, že "doladění češtiny" bude spíš psaní překladu od nuly než opravování existujícího — je to zdarma, ale je to práce, ne pár kliknutí. (Nepodařilo se mi ověřit, zda WordPress pro tuto úroveň vůbec generuje a rozesílá český jazykový balíček; netvrdím tedy, že vám systémový soubor updaty přepíšou — custom umístění je proti tomu tak jako tak imunní.)

**Náhrada:** Tvrzení platí, náhrada tedy není nutná. Ale protože web každý rok přebírá někdo jiný a Loco musí zůstat aktivní, zvažte jako ZÁLOŽNÍ nebo doplňkovou variantu pro 2–3 popisky mu-plugin s filtrem gettext (soubor v wp-content/mu-plugins/, nelze ho omylem deaktivovat, nulová závislost na dalším pluginu, přežije update pluginu i WordPressu):

add_filter('gettext', function($translated, $original, $domain){
    if ($domain === 'event-tickets' && $original === 'Number of tickets') {
        return 'Počet míst';
    }
    return $translated;
}, 10, 3);

Tenhle postup mimochodem doporučuje sám výrobce Event Tickets jako první variantu, ještě před Loco Translate (https://docs.nexcess.com/software/the-events-calendar/custom-wording/) — funguje pro domény začínající "tribe-", "the-events-" i "event-", takže řeší i problém s tribe-common.

Doporučená kombinace: Loco Translate pro širší překlad češtiny (pohodlné GUI, ukládat do Custom umístění) + mu-plugin s gettext filtrem pro těch pár kritických popisků, které musí fungovat i kdyby Loco někdo vypnul. Třetí varianta zmiňovaná výrobcem je plugin Say What (také zdarma), ale přidává další závislost bez výhody oproti mu-pluginu.

**Zdroj:** https://wordpress.org/plugins/loco-translate/

---

## Aktuality, homepage a nastavení ročníku

### ✅ Advanced Custom Fields (ACF) – free, wordpress.org slug `advanced-custom-fields`, verze 6.8.10 (WP Engine, aktualizace 10. 9. 2026)

**Verdikt:** POTVRZENO

**Ověřované tvrzení:** ACF free 6.1+ umí (a) zaregistrovat vlastní taxonomii v adminu včetně show_admin_column, (b) přiřadit skupinu polí pravidlem umístění „Taxonomy Term" konkrétní taxonomii s hodnotami čtenými přes get_field('zacatek', 'rocnik_' . $term_id), a (c) nabídnout pole typu „Taxonomy" s jedním výběrem.

**Co umí free verze:**

Free verze (6.8.10, GPLv2, wordpress.org) pokrývá celý popsaný scénář bez placení:
- Registrace taxonomie `rocnik` přes UI ACF > Taxonomy, včetně Show Admin Column (záložka Visibility, nutno zapnout přepínač „Advanced Configuration" — jinak se pokročilá nastavení nezobrazí). Klíč taxonomie má limit 20 znaků, `rocnik` projde.
- Pravidlo umístění „Taxonomy Term" = Rovná se = rocnik → termová pole (datumy, textarea) u každého ročníku, čtení přes get_field(..., 'rocnik_' . $term_id).
- Pole typu Taxonomy s Appearance = Select nebo Radio Buttons → jedna hodnota (WP_Term nebo term ID podle Return Value).

POZOR, kde je placená hranice a kde na ni tenhle projekt může šlápnout:
1. Options Pages jsou PRO ($49/rok). Zadání má „Aktivní ročník" na stránce Úvod — dokud je to skupina polí s pravidlem Page = Úvod, je to zdarma. Ve chvíli, kdy to někdo příští rok „uklidí" do ACF Options Page (globální nastavení webu), je potřeba licence. Držte to na konkrétní stránce, nebo použijte vlastní admin stránku / theme mod.
2. Dalšími PRO funkcemi jsou Repeater, Flexible Content, Gallery, Clone a ACF Blocks. Pokud by se datumy ročníku někdy řešily jako opakovatelný seznam (program, více termínů), je Repeater placený — řešte to raději dalšími termy taxonomie nebo vlastním CPT.
3. Požadavky verze 6.8.10: WordPress 6.2+ a PHP 7.4+ (testováno do WP 7.1.1).
4. Pro předávání webu každý rok: definice taxonomie i skupin polí leží v DB. Použijte Local JSON nebo PHP export (ACF > Tools) a commitněte do šablony; pozor, po exportu do PHP už taxonomii podle dokumentace nelze spravovat v ACF adminu. Jinak nový správce po migraci DB přijde o strukturu.
5. Pozn. k identitě pluginu: slug `advanced-custom-fields` na wordpress.org dnes patří ACF od WP Engine (6.8.10) — ověřoval jsem proti němu. Vedle toho existuje samostatný fork `secure-custom-fields` (Secure Custom Fields, autor WordPress.org, 6.9.5, 20. 8. 2026). Oba jsou zdarma a GPL a oba tyto funkce mají; instalujte vědomě jeden z nich, ať se ročníková dokumentace pro nástupce shoduje s tím, co je na webu.
6. Doporučená drobná úprava kódu: místo `'rocnik_' . $term_id` raději `'term_' . $term_id` nebo rovnou objekt termu (`get_field('zacatek', $term)`, resp. `get_queried_object()` v taxonomy.php). Oba formáty jsou dokumentované a funkční, ale `term_` je novější a nerozbije se, kdyby se klíč taxonomie někdy přejmenoval.

**Zdroj:** https://www.advancedcustomfields.com/resources/registering-a-custom-taxonomy/

---

### ✅ Advanced Custom Fields (ACF®) — free, wordpress.org, v6.8.10 (autor WP Engine)

**Verdikt:** POTVRZENO

**Ověřované tvrzení:** Existuje-li zapisovatelná složka `acf-json` uvnitř aktivního pluginu nebo motivu, ACF free do ní automaticky ukládá skupiny polí i UI-registrované typy obsahu a taxonomie jako JSON a v adminu nabízí tlačítko „Sync" pro jejich načtení do databáze na jiné instalaci.

**Co umí free verze:**

Local JSON včetně Sync je kompletně ve free verzi, žádný placený doplněk k tomuto účelu není potřeba. Free umí: skupiny polí, registraci custom post types a taxonomií přes UI (od ACF 6.1), jejich ukládání do `acf-json` a Sync. PLACENÉ (ACF PRO) jsou jen: Repeater, Flexible Content, Gallery, Clone, ACF Blocks a Options Pages — ceník advancedcustomfields.com/pro/: 49 USD/rok (1 web), 149 USD/rok (10 webů), 249 USD/rok (neomezeně), bez daně. To se projeví i v local JSON: free `local-json.php` nezná typ `acf-ui-options-page` (grep = 0 výskytů), protože Options Pages jsou PRO. Pro popsaný účel (definice polí a taxonomií na disku) ale PRO NENÍ potřeba ani korunu. DŮLEŽITÉ OMEZENÍ K ÚČELU „nezmizely s databází": JSON obsahuje jen DEFINICE (schéma polí, nastavení CPT a taxonomií). Vyplněné HODNOTY polí, samotné příspěvky a termy taxonomií zůstávají výhradně v databázi — local JSON tedy NENAHRAZUJE zálohu DB, jen zajistí, že se struktura webu dá obnovit a verzovat v gitu. (Pozn.: ACF nabízí studentům roční licenci PRO zdarma, ale výslovně jen „for classroom work" — na festivalový web se nevztahuje.)

**Náhrada:** Tvrzení nevyvracím, jen oprav umístění složky. Doporučený postup pro web s každoroční výměnou správce: (1) NEDÁVEJ `acf-json` do složky pluginu ACF — přepíše ji první automatická aktualizace. (2) Nejjednodušší varianta: `wp-content/themes/<vas-child-theme>/acf-json`, vytvořit ručně (ACF ji nevytvoří), práva 755 a zapisovatelná webserverem — funguje bez jediného řádku kódu. (3) Robustnější varianta, pokud hrozí změna motivu: malý vlastní „projektový" plugin (např. `wp-content/plugins/festival-core/`) se složkou `acf-json` a dvěma filtry v hlavním souboru — `add_filter('acf/settings/save_json', fn() => __DIR__ . '/acf-json');` a `add_filter('acf/settings/load_json', function($paths){ unset($paths[0]); $paths[] = __DIR__ . '/acf-json'; return $paths; });`. Definice pak přežijí i výměnu motivu a jsou v gitu na jednom místě. (4) Po nasazení ověř, že soubory opravdu vznikají — kvůli tichému selhání při nezapisovatelné složce se nedá spoléhat na to, že „admin by to řekl". (5) Na zbytek (obsah příspěvků, média, hodnoty polí) je pořád potřeba klasická záloha DB + uploads; na to stačí zdarma UpdraftPlus nebo `wp db export` v cronu.

**Zdroj:** https://www.advancedcustomfields.com/resources/local-json/

---

### ❌ Members (Members – Membership & User Role Editor Plugin), v3.2.26, MemberPress/Caseproof

**Verdikt:** VYVRÁCENO (částečně — cenově OK, ale klíčová část o taxonomiích neplatí)

**Ověřované tvrzení:** Members free umí v adminu vytvořit novou roli a zaškrtat jí jednotlivé capabilities včetně těch pro vlastní typy obsahu a vlastní taxonomie (manage_/edit_/assign_terms), bez psaní PHP.

**Co umí free verze:**

Členění free/placené je tady VÝJIMEČNĚ ČISTÉ a obavy o rozpočet jsou zde liché.

ZDARMA (vše, co potřebujete):
- Members je celý GPLv2, žádná Pro verze neexistuje. readme.txt: "License: GPLv2 or later", Stable tag 3.2.26.
- Všech 12 doplňků je v balíčku zdarma, jen se zapínají. Ověřeno výpisem adresáře addons/: members-acf-integration, members-admin-access, members-block-permissions, members-category-and-tag-caps, members-core-create-caps, members-edd-integration, members-givewp-integration, members-meta-box-integration, members-privacy-caps, members-role-hierarchy, members-role-levels, members-woocommerce-integration. members-plugin.com to potvrzuje: "Members includes ALL of its add-ons bundled for free — just activate the ones you need."
- Zdarma je tedy: editor rolí, tvorba/klonování/mazání rolí, zaškrtávání i explicitní zakazování capabilities, import/export rolí do JSON, Content Permissions, více rolí na uživatele, Private Site, Admin Access.

PLACENÉ:
- Žádná placená verze Members neexistuje. Jediný placený produkt v okolí je MemberPress (memberpress.com/plans/pricing/), což je SAMOSTATNÝ produkt na placená členství, platební brány, kurzy a drip content. Pro editor rolí a capabilities není potřeba vůbec. readme.txt ho zmiňuje jen jako upsell ("Adding MemberPress", ř. 62, 99-105).
- Pro váš scénář (role Dramaturgie, skrytí Stránek, omezení taxonomie) tedy neutratíte ani korunu za Members.

POZOR — omezení není cenové, ale technické: free verze vám per-taxonomy omezení edit_terms na Ročníku prostě nenabídne, protože ta capability v systému neexistuje. Zaplacení čehokoli to nevyřeší, řeší to jedině způsob registrace taxonomie.

**Náhrada:** Members NECHTE — pro roli, zaškrtávání capabilities i skrytí Stránek je to správná volba a je zdarma. Doplnit je třeba jen registraci taxonomie Ročník. Tři cesty, od nejlevnější:

A) BEZ PHP, kompromis (0 Kč, 5 minut) — nejlevnější, ale hrubý nástroj
V Members roli "Dramaturgie" NEzaškrtnout manage_categories. Tím ztratí právo vytvářet/editovat/mazat termy — u Ročníku i u všech ostatních výchozích taxonomií (Rubriky, Štítky). Přiřazovat existující termy k obsahu dál může, protože assign_terms je mapované na edit_posts.
Použitelné, pokud Dramaturgie nepotřebuje spravovat žádnou jinou taxonomii. Přesně splní "nesmí sáhnout na Ročníky", ale vedlejším efektem zamkne i Rubriky/Štítky.
Navíc zapněte doplněk "Category and Tag Caps" — ten oddělí Rubriky a Štítky na vlastní capabilities (manage_categories/edit_categories/... a manage_post_tags/edit_post_tags/...), takže je můžete Dramaturgii vrátit zvlášť. Ročníku to ale nepomůže.

B) BEZ PHP, čisté řešení (0 Kč) — spravovat Ročník přes Pods místo CPT UI
Pods (wordpress.org/plugins/pods/, v3.3.9.2, GPLv2, jádro kompletně zdarma) JAKO JEDINÝ má v UI nastavení capabilities i pro taxonomie. Ověřeno ve zdrojáku pods/src/Pods/Admin/Config/Pod.php ř. 1309-1328 (sekce taxonomií, ne post typů):
  'capability_type' => [ 'label' => 'User Capability', ... 'data' => [ 'default' => 'Default', 'custom' => 'Custom Capability' ] ]
  'capability_type_custom' => [ 'label' => 'Custom User Capability', 'help' => 'Enables additional capabilities for this Taxonomy including: manage_{capability}_terms, edit_{capability}_terms, assign_{capability}_terms, and delete_{capability}_terms' ]
a generování v pods/classes/PodsInit.php ř. 1459-1476 (manage_terms/edit_terms/delete_terms/assign_terms).
Postup: v Pods u taxonomie Ročník nastavit User Capability = Custom, hodnotu např. "rocnik". Vzniknou manage_rocnik_terms, edit_rocnik_terms, assign_rocnik_terms, delete_rocnik_terms. Ty se pak automaticky objeví v Members na záložce Taxonomies (protože members_get_taxonomy_group_caps() čte $tax->cap všech taxonomií) a zaškrtáte je myší. Dramaturgii dáte assign_rocnik_terms, nedáte edit_rocnik_terms.
Pods má i komponentu Migrate-CPTUI (pods/components/Migrate-CPTUI/Migrate-CPTUI.php) pro převod z CPT UI. Cena: 0 Kč. Nevýhoda: další plugin navíc a migrace existující taxonomie — otestujte na kopii, termy a jejich přiřazení zůstávají v DB, mění se jen registrace.

C) MINIMÁLNÍ PHP (0 Kč, ~8 řádků jednou nalepit) — pokud nechcete měnit CPT UI za Pods
Do pluginu Code Snippets (zdarma) nebo do mu-plugins vložit:

  add_filter( 'register_taxonomy_args', function( $args, $taxonomy ) {
      if ( 'rocnik' === $taxonomy ) {
          $args['capabilities'] = [
              'manage_terms' => 'manage_rocnik',
              'edit_terms'   => 'edit_rocnik',
              'delete_terms' => 'delete_rocnik',
              'assign_terms' => 'assign_rocnik',
          ];
      }
      return $args;
  }, 10, 2 );

Ty čtyři capability se okamžitě objeví v Members na záložce Taxonomies a dál už se jen klikátko. Je to PHP, ale jednorázové vložení, které přežije výměnu šablony (mu-plugin) a příští ročníkový správce se ho nedotkne. Funguje nezávisle na tom, kdo taxonomii registruje.

DOPORUČENÍ pro váš případ (festival, minimální rozpočet, každoroční předávka): varianta C do mu-plugins. Je nejlevnější na údržbu, nevyžaduje migraci taxonomie ani další plugin a nový správce nemusí rozumět ničemu — v adminu pak vidí jen zaškrtávátka v Members. Varianta B je "čistší bez kódu", ale platíte za to migrací na Pods a jedním pluginem navíc, což je při předávce větší riziko než osm řádků v mu-pluginu.

POZOR NA PAST u varianty A i obecně: assign_terms je ve výchozím stavu namapované na core edit_posts. Pokud roli Dramaturgie odeberete edit_posts (např. protože pracuje jen s vlastním CPT), nebude moci Ročník přiřadit vůbec ničemu. U variant B a C tento problém odpadá, protože assign_rocnik(_terms) je samostatná capability.

**Zdroj:** https://wordpress.org/plugins/members/

---

## Partneři, osvěžovny, fotogalerie, historie

### ✅ Imsanity

**Verdikt:** POTVRZENO

**Ověřované tvrzení:** Imsanity při nahrání zmenší obrázek na nastavený max rozměr a NAHRADÍ původní soubor na disku (nevytvoří jen zmenšenou kopii vedle nezmenšeného originálu). Potřebná funkce je zdarma.

**Co umí free verze:**

Celá popsaná funkce je zdarma, bez háčku. Imsanity nemá pro/premium verzi, žádný licenční klíč ani feature gate — prohledal jsem hlavní soubor pluginu i changelog a nic takového tam není. GPLv3, vývoj veřejně na GitHubu (nosilver4u/imsanity), na wordpress.org je jen odkaz na dobrovolný dar.

Zdarma je: automatické zmenšování při uploadu, nastavení max šířky/výšky/kvality, Bulk Resize existujících obrázků, resize jednotlivého obrázku z Media Library, konverze PNG→JPG, od 2.9.0 i WebP/AVIF.

Placené NENÍ nic z toho, co potřebujete. Jediný komerční produkt v okolí je EWWW Image Optimizer — jiný plugin od stejného autora (Shane Bishop, ewww.io), na který Imsanity v popisu odkazuje. Řeší jinou věc: ztrátovou kompresi navíc nad rámec standardní WP komprese. Pro váš cíl ("nesežrat hosting originály") ho nepotřebujete a instalovat nemusíte — Imsanity zmenší rozměr, což je u 30Mpx fotek naprostá většina úspory. EWWW má free verzi s lokální kompresí, placené jsou jen cloudové API kredity; cenu neuvádím, protože jsem ji pro tento verdikt nezjišťoval — není relevantní, nic z ní nepotřebujete.

Rozpočtově: 0 Kč ročně, žádná registrace, žádný účet, žádný klíč k předání dalšímu ročníku. Pro web, který každý rok přebírá někdo jiný, je to zhruba nejlepší možný profil — jediné, co se musí předat, je poznámka "v Settings → Imsanity jsou tři pole, všechna na 2000".

**Náhrada:** Náhrada není potřeba, tvrzení obstálo. Jen dvě konkrétní věci k zařízení po instalaci: (1) v Settings → Imsanity nastavit max rozměr na 2000 ve všech třech polích, ne jen v prvním — default je 1920; (2) na 6 existujících fotek z ročníku 2024 spustit Bulk Resize, protože aktivace zpětně nic nemění. Pokud by Imsanity na hostingu selhal kvůli paměti u 30Mpx souborů (projeví se chybou při uploadu první fotky, ne tichým selháním), fallback je zmenšit fotky před nahráním lokálně — ale zkuste nejdřív upload jedné fotky, s default 256M limitem WP to obvykle projde.

**Zdroj:** https://wordpress.org/plugins/imsanity/

---

### ✅ Enable Media Replace

**Verdikt:** POTVRZENO (s věcnou opravou formulace)

**Ověřované tvrzení:** Enable Media Replace ve free verzi umí nahradit soubor v knihovně médií novým souborem a zachovat stejnou URL i všechny vazby v obsahu (volba "Replace the file, use new file name and update all links").

**Co umí free verze:**

Celá funkce výměny média je zdarma, GPLv2, bez Pro verze a bez účtu. Obě volby (zachovat název/URL i změnit název a přepsat odkazy), přegenerování náhledů i integrace na page buildery jsou ve free verzi. Cena: 0 Kč, žádné roční předplatné.

Jediná placená plocha v pluginu: volitelná beta funkce "Remove background" (AI odstranění pozadí). Podle oficiálního FAQ je aktuálně zdarma "in beta" s limitem "reasonable usage"; kdo limit překročí, může vložit API klíč z placeného plánu ShortPixel Unlimited / Unlimited AI. Konkrétní cenu plánu se mi na shortpixel.com/pricing nepodařilo vytáhnout (ceník se načítá až JS), ale je to irelevantní — s výměnou loga tato funkce nijak nesouvisí a jde vypnout (ShortPixel má k tomu KB článek "Disabling the Remove background functionality"). Pro popsaný scénář festivalu není třeba zaplatit nic.

Riziko pro projekt, který každý rok přebírá někdo jiný: plugin je aktivně udržovaný (aktualizace červen 2026, 600k+ instalací, za vývojem stojí firma ShortPixel), takže riziko opuštění je nízké. Ale ShortPixel plugin používá i jako marketingový kanál na své ostatní produkty (ShortPixel Image Optimizer, FastPixel Caching) — v adminu se objevují upsell prvky, což je pro netechnika spíš matoucí než škodlivé.

**Náhrada:** Náhrada není potřeba — plugin i potřebná funkce jsou zdarma. Je ale nutné opravit návod pro admina: pro zachování stejné URL se používá volba 1 "Simply replace the file" (vyžaduje stejný typ souboru), NE volba 2 uvedená v tvrzení. Volbu 2 "Replace the file, use the new file name, and update all links" použít jen tehdy, když nové logo přijde v jiném formátu — pak se URL změní a plugin přepíše odkazy v obsahu. Do dokumentace pro festival doporučuji napsat doslova: "Partner poslal logo ve stejném formátu (JPG za JPG / PNG za PNG) → první volba. Poslal jiný formát → druhá volba. Pokud se na webu pořád zobrazuje staré logo, vymaž cache a dej Ctrl+Shift+R."

**Zdroj:** https://wordpress.org/plugins/enable-media-replace/

---

## Navigace, statické stránky a editovatelný vzhled

### ✅ Kadence Theme (free, WordPress.org)

**Verdikt:** POTVRZENO

**Ověřované tvrzení:** Kadence free téma (bez Kadence Pro) obsahuje drag&drop header builder se samostatnou mobilní/tabletovou řadou a off-canvas mobilním menu, 9-slotovou globální paletu barev, typografii pro jednotlivé elementy, přepínač General → Performance → 'Load Google Fonts Locally' + 'Preload Local Fonts', a paletu dostupnou jako CSS proměnné var(--global-palette1) až var(--global-palette9) v Doplňkovém CSS.

**Co umí free verze:**

ZDARMA (ověřeno přímo ve zdrojovém kódu free ZIPu v1.5.2, GPL, wordpress.org):
- Drag&drop header i footer builder, 3 řady × 3 sekce, samostatná Tablet/Mobile záložka, Off Canvas panel (layout sidepanel/fullwidth, strana, animace, šířka)
- Mobile Trigger (hamburger), Mobile Navigation, Mobile Button, Mobile HTML, Mobile Social
- Globální paleta (9 hlavních + 6 doplňkových slotů), druhá paleta pro dark mode, CSS proměnné --global-palette1..15 na :root
- Typografie: Base, H1–H6, Title, Breadcrumbs, typografie prvků hlavičky/patičky, responzivní, 1000+ Google Fonts
- General → Performance: Load Google Fonts Locally, Preload Local Fonts, Flush Local Font Files, Enable CSS Preload, Lightbox, Scroll To ID
- BONUS navíc oproti tvrzení, a taky zdarma (ověřeno, soubory existují): Sticky Header (inc/customizer/options/header-sticky-options.php) a Transparent Header (transparent-header-options.php) — často se mylně uvádějí jako Pro

PLACENÉ (až v předplatném):
- Conditional Headers (různé hlavičky podle stránky/typu obsahu)
- Header addons: login modal, account menu, WooCommerce popup cart
- Mega menu
- Nahrání vlastních fontů (non-Google custom font upload)
- Prioritní podpora, pokročilé starter templates

CENA: kadencewp.com/pricing/ dnes přesměrovává na https://www.liquidweb.com/software/kadence/ — nejlevnější placený tier je Essentials $99/rok (dále Pro $299/rok, Elite $499/rok). Tj. ~2 300 Kč/rok — pro váš rozpočet mimo hru, ale NENÍ potřeba: všechny čtyři funkce z vašeho zadání (hlavička/patička drag&drop, globální paleta, typografie, lokální Google Fonts) jsou ve free verzi.

DVA SKEPTICKÉ POZNATKY:
1) Marketingová stránka Liquid Webu uvádí "header/footer builder" jako součást placeného tieru Essentials. To je zavádějící — grep ve zdrojáku free tématu dokazuje, že builder je zdarma. Nenechte se tím při hledání informací zmást.
2) Kadence koupil Liquid Web/Nexcess, dokumentace se přesunula (kadencewp.com/help-center/* → 302 na docs.nexcess.com). Riziko pro projekt s každoročním předáním je ale nízké: téma je GPL, na wordpress.org, 500k+ instalací, poslední update 24. 7. 2026. I kdyby vývoj ustal, nainstalované téma dál funguje a zdrojový kód je volně dostupný.

**Zdroj:** https://wordpress.org/themes/kadence/

---

### ✅ Kadence Blocks

**Verdikt:** POTVRZENO (s jednou upřesňující výhradou u „slotu globální palety")

**Ověřované tvrzení:** Ve free verzi jsou bloky Row Layout, Section, Info Box, Accordion, Tabs, Advanced Gallery (vč. Carousel), Countdown a Posts; Row Layout/Info Box umí rámeček a box-shadow s vlastním X/Y a NULOVÝM rozostřením (6px 6px 0), barvu stínu lze vybrat ze slotu globální palety, ne jen jako hex. Jinak fallback do Doplňkového CSS (+2 h).

**Co umí free verze:**

Pro účel projektu (kartičky, FAQ accordion, galerie, odpočet na homepage) je free verze plně dostačující a nic z toho, co tvrzení potřebuje, není placené.

ZDARMA a ověřeno v kódu:
- Row Layout, Section, Info Box, Accordion, Tabs, Advanced Gallery, Countdown, Posts — vše ve free readme.txt.
- Advanced Gallery: Grid, Masonry, Carousel, Fluid Carousel, Slider.
- Countdown: běžný odpočet k pevnému datu (pro festival přesně to, co je potřeba).
- Box shadow u Row Layout i Info Box: X, Y, Blur, Spread, Inset, barva + průhlednost, plus 8 presetů. Blur min = 0, takže 6px 6px 0 jde nastavit klikáním, bez CSS.
- Border + Border Radius u Row Layout i Info Box, včetně responzivních hodnot a stavů normal/hover.
- Vlastní globální barevná paleta přímo v nastavení pluginu (option kadence_blocks_colors), funguje i bez Kadence šablony.

PLACENÉ (pro tento projekt nepodstatné):
- Gallery rozvržení Thumbnail Slider, Tiles, Mosaic.
- Pozice popisků v galerii mimo "center" (Bottom Left, Top Right atd.).
- Advanced Query Loop, Advanced Slider, Modal, Product Carousel, Animate on Scroll, Custom Fonts, Dynamic Content, evergreen kampaně u Countdownu, 800+ patternů v Design Library.

CENA, kdyby na Pro někdy došlo: na oficiálním webu (liquidweb.com/software/kadence/blocks/) je bundle Essentials 99 USD/rok, Pro 299 USD/rok, Elite 499 USD/rok. Samostatné Kadence Blocks Pro se podle recenzních webů (třetí strana, neověřeno na oficiálním zdroji) pohybuje kolem 89 USD/rok. Pro festival s minimálním rozpočtem to ale není třeba řešit — nic z požadovaného za paywallem není.

PRAKTICKÝ POZOR pro předávání webu dalšímu ročníku:
a) Chcete-li, aby se barva stínu odkazovala na SLOT palety (změním palette1 a stíny se přebarví samy po celém webu), musíte použít i free šablonu Kadence Theme, která registruje slugy theme-palette1..9. S jinou šablonou se do bloku uloží konkrétní hex a příští ročník ho bude muset přepisovat ručně v každém bloku. Doporučení: vzít Kadence Theme (taky zdarma) a držet se jejích 9 slotů.
b) Když vyberete barvu stínu jako slot palety, posuvník průhlednosti se ignoruje — render_color() při hodnotě "paletteN" vrací čisté var(--global-paletteN) bez rgba. Pro tvrdý plakátový stín je to naopak žádoucí (chcete 100% krytí), ale je dobré o tom vědět.
c) Výchozí hodnota blur je 14; po zapnutí stínu je nutné ji přepsat na 0, nebo použít preset "Right Bottom Solid" / "Top Left Solid", který nastaví blur 0 sám.

**Náhrada:** Náhrada není potřeba — tvrzení obstálo. Jediná úprava plánu: počítejte s free Kadence Theme vedle Kadence Blocks, aby barva stínu držela slot globální palety a příští ročník ji uměl změnit na jednom místě. Položku „fallback do Doplňkového CSS, cca +2 h" lze z rozpočtu úplně vyškrtnout.

**Zdroj:** https://wordpress.org/plugins/kadence-blocks/ (primární: stažený free ZIP v3.7.11 z https://downloads.wordpress.org/plugin/kadence-blocks.zip — soubory readme.txt, dist/components.js, dist/blocks-rowlayout.js, dist/blocks-infobox.js, dist/blocks-advancedgallery.js, includes/class-kadence-blocks-css.php, includes/blocks/class-kadence-blocks-row-layout-block.php); doplňkově https://docs.nexcess.com/software/kadence/advanced-gallery-block/ a https://www.liquidweb.com/software/kadence/blocks/

---

### ✅ Redirection (John Godley, wordpress.org/plugins/redirection/)

**Verdikt:** POTVRZENO

**Ověřované tvrzení:** Redirection zvládne ve free verzi ručně zadaná 301 pravidla včetně adres s podadresářem (/program/ucinkujici → /program) a funguje na sdíleném hostingu Wedos bez vlastního cronu a bez zásahu do .htaccess.

**Co umí free verze:**

Free verze umí VŠECHNO, co potřebuješ, a placená verze neexistuje. Cena: 0 Kč, natrvalo. Autor to na wordpress.org explicitně potvrzuje ("There's no premium version"). Ve free jsou: neomezený počet ručních 301/302/307/308 pravidel, regex, podmíněné redirecty (podle přihlášení, prohlížeče, cookie, IP), sledování a logování 404, import/export (CSV, JSON, .htaccess, Nginx), import z konkurenčních pluginů, automatický redirect při změně permalinku. Nic z toho není za paywallem.

TŘI PODMÍNKY, KTERÉ MUSÍŠ SPLNIT (nejsou to platby, ale precondition):

a) VERZE WORDPRESSU — readme na wordpress.org u aktuální 5.10.1 vyžaduje WordPress 6.7+ a PHP 7.4+ (starší stránka instalace uvádí 6.6, ber tu vyšší). U webu, který každý rok mění správce, je reálné, že běží na staré verzi WP. Když neaktualizuješ, nejnovější Redirection se nenainstaluje — musel bys sáhnout po starší verzi pluginu, což je bezpečnostně špatně.

b) REST API PRO ADMIN ROZHRANÍ — redirection.me/support/problems/rest-api/ potvrzuje závislost na /wp-json/. Důležité upřesnění: REST API pohání React administraci pluginu, tedy zadávání a editaci pravidel. Samotné vykonávání redirectů je PHP hook při načtení stránky, na REST API nezávisí. Když by hosting /wp-json/ blokoval, existující pravidla dál fungují, jen je nezeditujes. Na Wedosu to očekávám v pořádku, ale otestuj to hned po instalaci — je to jediný realistický scénář selhání.

c) LOMÍTKO NA KONCI — WordPress standardně používá permalinky s koncovým lomítkem (/program/ucinkujici/), ale staré odkazy mohou být bez něj. Redirection má na to volbu "Ignore trailing slashes" (podle redirection.me/support/matching-a-url/ ji lze nastavit globálně jako výchozí i na jednotlivém pravidle) — zapni ji, jinak ti pravidlo může tiše nesednout. Stejně tak je tam "Ignore case".

PROVOZNÍ DOPORUČENÍ PRO BEZÚDRŽBOVÝ WEB: v nastavení pluginu vypni nebo zkrať expiraci logů 404 a redirectů (např. na 7 dní). Logy se jinak roky hromadí ve vlastních DB tabulkách a na sdíleném hostingu s omezenou databází to nikdo rok neuhlídá. Mazání starých logů je jediná věc, která WP-Cron potřebuje.

**Náhrada:** Náhrada není potřeba, tvrzení se nepodařilo vyvrátit — Redirection je pro tento účel správná volba a je skutečně zdarma.

Záložní varianty, jen kdyby některá z podmínek padla:
- Kdyby Wedos blokoval /wp-json/ a nešlo zadávat pravidla: pár desítek řádků do functions.php v child theme — pole starý_slug => nový_slug a wp_redirect($cil, 301) na hooku template_redirect. Nula pluginů, nula závislostí, ale horší předávatelnost dalšímu správci.
- Kdyby web běžel na starém WordPressu pod 6.7 a nešlo ho aktualizovat: "Simple 301 Redirects" nebo "Safe Redirect Manager" mají nižší nároky. Obojí zdarma, ale méně funkcí a Redirection z nich umí přímo importovat, takže se k němu po aktualizaci WP dostaneš bez ztráty pravidel.
- Rank Math / Yoast SEO mají redirect modul, ale u Yoastu je až v Premium (cca 2 300 Kč/rok) — pro festival s minimálním rozpočtem to je přesně ten problém, kterému se vyhýbáš. Nepoužívat.

**Zdroj:** https://wordpress.org/plugins/redirection/

---

### ✅ Safe SVG (10up) — wordpress.org/plugins/safe-svg/

**Verdikt:** POTVRZENO

**Ověřované tvrzení:** Safe SVG povolí nahrávání SVG do Knihovny médií a sanitizuje obsah proti vloženému skriptu; bez něj WordPress SVG odmítne s hláškou o nepovoleném typu souboru. Nasazovat POUZE pokud logo přijde v SVG — u dnešního PNG je plugin zbytečný a je to o jednu závislost míň.

**Co umí free verze:**

Free verze umí vše, co projekt potřebuje. Na oficiální stránce pluginu není uvedena žádná pro/premium verze, žádný ceník ani upsell — plugin je na wordpress.org pod GPL, distribuovaný firmou 10up jako plně otevřený, bez placeného tieru. Cena tedy 0 Kč, žádné roční předplatné.

Free verze konkrétně zahrnuje: povolení uploadu SVG přes Knihovnu médií, sanitizaci obsahu, náhledy SVG ve všech pohledech knihovny, omezení uploadu podle uživatelské role, volitelnou SVGO optimalizaci (ve výchozím stavu vypnutá, zapíná se filtrem), blok „Safe SVG Display" pro vložení inline SVG včetně odkazu (target, rel) a izolaci inline SVG se styly do shadow DOM.

POZOR na jedno reálné omezení nasazení, které tvrzení neřeší: verze 2.5.0 vyžaduje WordPress 6.9+ a PHP 7.4+. Pokud festivalový web běží na starším jádře (u projektu, který každý rok mění správce, je to pravděpodobné), buď se nejdřív musí aktualizovat WP, nebo se nainstaluje starší verze pluginu — a tam je riziko výše zmíněných CVE. Před instalací ověřte verzi WordPressu.

Omezení free verze (ne placená hranice, prostě design pluginu): SVG MIME typ se nepovoluje globálně, takže SVG nahrané mimo standardní WP upload cesty (vlastní kód, některé page buildery) plugin nesanitizuje. Téma nedosáhne CSS na inline SVG, které mají vlastní <style> (shadow root), dědičné vlastnosti jako color a CSS proměnné fungují.

**Náhrada:** Náhrada není potřeba — tvrzení je potvrzené a plugin je zdarma. Doporučení k realizaci: 1) Aktivně se zeptejte grafika, v jakém formátu logo přijde, ještě než plugin instalujete. 2) Pokud přijde jen PNG, plugin skutečně neinstalujte. 3) Pokud přijde SVG, použijte Safe SVG a NE filtr upload_mimes ve functions.php — ten sice ušetří závislost, ale nahrané SVG nijak nekontroluje a stačí jeden účet redaktora k uložení XSS. 4) Po nasazení v nastavení pluginu omezte upload SVG jen na roli administrátora a plugin držte aktualizovaný (historie CVE). 5) Do předávacího README pro příštího správce napište jednu větu, proč plugin na webu je — jinak ho někdo za dva roky odinstaluje a rozbije logo.

**Zdroj:** https://wordpress.org/plugins/safe-svg/

---

## Role, účty, hosting a migrace dat

### ✅ Members – Membership & User Role Editor Plugin (Caseproof / MemberPress)

**Verdikt:** POTVRZENO

**Ověřované tvrzení:** Members ve free verzi umí vytvořit vlastní roli, naklonovat existující, nastavit u každé capability stav grant/deny/unset a zobrazí i capabilities registrované cizími pluginy (edit_tribe_events z The Events Calendar, capabilities Event Tickets a přejmenované capabilities CPT z ACF). Všechny doplňky (Admin Access, ACF integration) jsou v balíku zdarma.

**Co umí free verze:**

Members nemá placenou verzi. Není to „free tier vs. pro" — žádné pro neexistuje. Cena: 0 Kč, trvale.

Oficiální stanovisko výrobce na members-plugin.com: „we don't make any money directly from this plugin while other, similar plugins charge substantial fees". Monetizace jde přes MemberPress, což je samostatný placený produkt na placená členství a kurzy — pro vytvoření role „Dramaturgie" není potřeba a není to upgrade path. Žádný upsell neblokuje potřebnou funkci.

Stav pluginu (wordpress.org/plugins/members/, ověřeno 21. 9. 2026): verze 3.2.26, aktualizováno 25. 8. 2026, testováno do WP 7.1.1, vyžaduje WP 6.0+ a PHP 7.4+, 300 000+ aktivních instalací. Aktivně udržováno, žádný varovný banner o neaktuálnosti. GPL, z oficiálního repozitáře WordPress.org.

Všech dvanáct doplňků včetně Admin Access a ACF integration je v balíku zdarma, zapínají se přepínačem v dashboardu Members. Nic se nedokupuje.

Pro rozpočet festivalu: tohle je bezpečná volba. Žádné roční předplatné, žádný licenční klíč, který nástupci vyprší, plugin se instaluje přímo z WP administrace.

PRAKTICKÉ POZNÁMKY PRO ROLI „DRAMATURGIE":
- CPT s vlastním capability_type (tribe_events, ACF CPT) nemá své capabilities přiřazené defaultně žádné roli — ani Administrátorovi. Nástupce je musí zaškrtnout i pro Administrátora, jinak si sám zamkne přístup. Members to dělá triviálním, ale je to krok, na který se zapomíná.
- Práva k ticketům a seznamu attendees (Event Tickets) se nezaškrtnou samostatně — řídí je core capabilities edit_posts / edit_others_posts plus TEC rodina edit_tribe_events. S tím počítej při skládání role.
- Pokud bude mít Admin 2 víc rolí najednou, zapni v Members → Settings volbu, aby denied capabilities přebíjely granted. Jinak jsou konflikty nepředvídatelné.
- Members má „Administrator Rescue (Magic Link)" pro případ, že se nástupce omylem vyzamkne. U webu, který každý rok přebírá někdo jiný, je to reálně užitečné.

**Zdroj:** https://wordpress.org/plugins/members/

---

### ✅ Duplicator (Lite) — WordPress plugin, verze 5.0.4 (aktualizováno 18. 9. 2026)

**Verdikt:** POTVRZENO — s jednou věcnou opravou (údaj o 500 MB je zastaralý a nepřesný)

**Ověřované tvrzení:** Duplicator Lite vytvoří archiv + installer.php, které stačí nahrát FTP klientem a spustit v prohlížeči; nevyžaduje SSH ani předinstalovaný WordPress (jen prázdnou MySQL databázi) a při instalaci sám provede search-replace URL včetně serializovaných dat v options a postmeta. Limit free verze je 500 MB archivu.

**Co umí free verze:**

ZDARMA (Lite 5.0.4) — vše, co pro plánovanou migraci potřebujete:
- Classic Install: stažení archivu + installer.php, nahrání FTP, spuštění v prohlížeči
- plná záloha i záloha jen databáze, filtry souborů a DB tabulek
- migrační wizard se search-replace včetně serializovaných dat
- heslo + AES-256 šifrování archivu
- volba archive enginu (DupArchive / ZipArchive / shell zip) a DB enginu (mysqldump / chunked PHP)
- 1-click restore z WP administrace — podle readme 5.0.4 JE ve free ("1-click restore … all part of the free plugin"). Stránka free-vs-pro, která ho řadí do Pro, je zastaralá. Háček: 1-click restore potřebuje funkční WP admin; pro obnovu rozbitého/mrtvého webu slouží Recovery Points, a ty jsou Pro.

PAST — "cloud storage zdarma" NENÍ zdarma:
readme tvrdí "cloud storage are all part of the free plugin" a "The free plugin stores backups locally and on Duplicator Cloud". Ale FAQ na cloud.duplicator.com říká: "Duplicator Cloud works with Duplicator Lite 5.0+ and Duplicator Pro. A separate Cloud subscription is required; no Pro license is needed." Cena od 29 USD/rok.
=> Offsite úložiště ve free NENÍ. Zálohy z Lite leží na stejném Wedos serveru jako web. Když padne/napadne se hosting, padnou s ním i zálohy. Nástupce je musí ručně stahovat k sobě.

PLACENÉ (Pro), z toho relevantní pro roli "zálohovacího nástroje":
- Scheduled backups (hodinově/denně/týdně/měsíčně) — v Lite zálohy POUZE ručně, žádné plánování
- Cloud integrace: Dropbox, Google Drive, OneDrive, Amazon S3, FTP/SFTP, S3-kompatibilní
- Recovery Points (rychlá obnova havarovaného webu)
- e-mailová notifikace při selhání zálohy
- Drag & Drop install, server-to-server import, staging, multisite, WP-CLI, installer branding, multi-threaded engine

CENY (ceník výrobce, https://duplicator.com/pricing/): Basic 79 USD/rok (2 weby), Plus 199 USD/rok (5), Pro 399 USD/rok (20), Elite 599 USD/rok (100). Na stránce běží banner "60 % sleva", jehož vztah k těmto číslům se mi z fetche nepodařilo ověřit. Stránka free-vs-pro uvádí "od 69,30 USD/rok" — to s uvedenými čísly nesedí, jde nejspíš o starou akci. Obě hodnoty uvádím tak, jak jsem je našel, nedopočítávám je.

Pro rozpočet studentského festivalu: migrace = 0 Kč, žádný kompromis. Průběžné zálohování = 0 Kč, ale pouze ručně a pouze na stejný server.

**Náhrada:** Pro migraci náhradu nepotřebujete — tvrzení platí, Duplicator Lite postup na Wedos bez SSH zvládne zdarma. Opravte si jen v dokumentaci větu o 500MB limitu (neplatí tak, jak je napsaná, a pro váš objem dat je irelevantní). Pro roli "nástupce udělá zálohu jedním tlačítkem" má ale Lite dvě díry: žádné plánování a žádné bezplatné offsite úložiště — záloha zůstává na stejném Wedosu a někdo si ji musí ručně stáhnout. Pokud má předávání webu jednou ročně přežít i scénář "hosting je pryč", ověřte si aktuální free tier některého plugin s plánovanými zálohami a vzdáleným úložištěm zdarma (kandidát k prověření: UpdraftPlus — ve free verzi historicky nabízel plánování i Google Drive/Dropbox; v rámci tohoto úkolu jsem to NEověřoval, berte to jako tip k ověření, ne jako doporučení). Duplicator Lite si klidně nechte jako migrační a ad-hoc nástroj současně.

**Zdroj:** https://duplicator.com/knowledge-base/classic-install/

---

### ✅ Redirection (johngodley), verze 5.10.1

**Verdikt:** POTVRZENO

**Ověřované tvrzení:** Redirection ve free verzi umí hromadný import pravidel z CSV (zdroj;cíl) a má log 404, ze kterého jde po ostrém přechodu dohledat zapomenuté URL.

**Co umí free verze:**

Žádná placená verze neexistuje — není co uvést jako cenu. Doslova z readme.txt pluginu (řádek 111, sekce FAQ "Wait, it's free?"): "Yes, it's really free. There's no premium version and no need to pay money to get access to features. This is a dedicated redirect management plugin."

Obě potřebné funkce jsou tedy plně zdarma a bez omezení: import/export CSV (readme: "Import and export to CSV for viewing in a spreadsheet") i sledování 404 ("Track 404 errors"). Není tu žádný freemium model, žádný limit počtu přesměrování, žádný upsell. Plugin je GPL, vyvíjí ho John Godley na GitHubu. Pro rozpočet neziskového festivalu je to čistá varianta — jediné riziko není cena, ale to, že správce po letech přestane plugin aktualizovat; proto stojí za to jednou ročně při předání zkontrolovat datum poslední aktualizace na wordpress.org.

**Náhrada:** Náhrada není potřeba, plugin je správná volba. Místo náhrady dvě opravy postupu: (1) CSV exportovat s ČÁRKOU, ne středníkem — tvrzení "zdroj;cíl" je chybné, správně je "zdroj,cíl"; před importem otevřít soubor v textovém editoru a ověřit oddělovač, Redirection navíc nabízí náhled importu před potvrzením. (2) Hned po ostrém spuštění jít do Nástroje → Redirection → Options a přepnout retenci 404 logu ze 7 dnů na "Forever" (nebo "Two months"), jinak se zapomenuté URL nedohledají. Do předávacích poznámek pro dalšího správce připsat obojí plus to, že přesměrování jsou v databázi, ne v .htaccess.

**Zdroj:** https://wordpress.org/plugins/redirection/

---

### ✅ WP 2FA (Melapress)

**Verdikt:** POTVRZENO

**Ověřované tvrzení:** WP 2FA ve free verzi umí vynutit 2FA pro konkrétní roli (Administrator) pomocí TOTP aplikace a vygeneruje záložní kódy pro obnovu přístupu.

**Co umí free verze:**

ZDARMA (ověřeno ve zdrojovém kódu free balíku 4.1.0): TOTP přes libovolnou aplikaci (Google Authenticator, Authy, Aegis…), jednorázový kód e-mailem, záložní kódy (16místné, generovatelné a obnovitelné z profilu uživatele), jedna globální 2FA politika s vynucením na "All users" / "Only for specific users and roles" / "Do not enforce", výběr konkrétních rolí i konkrétních uživatelů, vyloučení uživatelů/rolí, grace period včetně volby co se stane po vypršení (zamknout účet / donutit nastavit ihned), REST API. Podpora jen přes fórum.

PLACENÉ (Premium 79 USD/rok, Enterprise 89 USD/rok za 1 web): různé politiky pro různé role současně, passkeys, YubiKey, SMS, přihlašovací odkaz e-mailem, důvěryhodná zařízení, zákaz vypnutí 2FA uživatelem, 2FA při resetu hesla, "Allow next user login without 2FA" (nouzové odemčení adminem), zálohové kódy e-mailem, WooCommerce integrace, reporty, white labeling, e-mailová podpora.

PRAKTICKÉ K PROJEKTU (festival, každý rok jiný správce):
- Na oba účty Administrator to free verze zvládne bez omezení a bez placení.
- Při zapínání vynucení nezapomeňte, že se vztahuje i na vás — nastavte grace period, nebo se dočasně vyjměte, jinak riskujete zamčení (plugin na to sám upozorňuje, class-settings-page-policies-new.php:138).
- TOTP secret i záložní kódy jsou vázané na konkrétní osobu a zařízení. Při předání festivalu nový správce NEDĚDÍ 2FA — musí si TOTP zaregistrovat znovu a vygenerovat si nové záložní kódy. To je správné chování, ale musí být v předávacím postupu napsané, jinak se to zjistí až ve chvíli, kdy se nikdo nedostane dovnitř.
- Free verze nemá premiové "Allow next user login without 2FA" (nouzové odemčení adminem přes UI). Jediná záchrana při ztrátě telefonu jsou tedy záložní kódy — ty musí být uložené mimo web, ideálně v předávací dokumentaci/trezoru, ne ve stejném správci hesel, který se předává s heslem. Krajní řešení bez pluginu je smazání user meta přes databázi/WP-CLI, což je pro netechnického nástupce nepoužitelné.

**Zdroj:** https://wordpress.org/plugins/wp-2fa/

---

