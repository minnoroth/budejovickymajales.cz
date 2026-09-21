# Moduly — detailní návrh

Vygenerováno z vícagentní analýzy (7 modulů, 25 ověřených tvrzení o pluginech).
Přehled a rozhodnutí jsou v [00-analyza.md](00-analyza.md), otázky v [01-otazky-na-schuzku.md](01-otazky-na-schuzku.md).

**Souhrnný odhad: 193–312 hodin.**

---

## Program, lineup, žánry, místa

**Odhad: 42–64 h**

Doporučuji The Events Calendar (free) jako datovou a administrační vrstvu, ale NE jako frontend — program a lineup si vykreslí dva vlastní shortcody v jednom malém pluginu nad stávajícím markupem kartiček. Tím padá celá otázka Filter Bar (placený): filtrování 102 kartiček podle kategorie a místa současně si dělám sám v prohlížeči, stejně jako to dnes dělá Isotope. TEC free nativně pokrývá přesně to, co BM Admin uměl ručně: rozsah datum+čas, místo jako samostatný záznam s našeptávačem a inline vytvořením, „Top představení" jako Featured Event, obrázek, odkaz na web, hierarchickou taxonomii pro dvouúrovňový vztah Žánr→Kategorie a export do kalendáře (ICS/Google) zdarma. Volba TEC navíc otevírá Rezervacím modul Event Tickets RSVP nad týmiž událostmi — moduly Program a Rezervace se tak shodnou na jednom datovém modelu místo dvou.

### Mapování na WordPress

| Entita | WordPress konstrukt | Pole | Poznámka |
|---|---|---|---|
| Vystoupení (102 ks) | CPT tribe_events (The Events Calendar, free) | Název = titulek; Popis = klasický WP editor (bold, kurzíva, odrážky, odkaz — pokrývá celý původní WYSIWYG); Datum a čas od–do = nativní pole Start/End (datum + čas, přes půlnoc i přes dny); Obrázek = náhledový obrázek (mazání = tlačítko Odebrat); Web = nativní pole Event Website URL | Odstranění obrázku řeší WP samo tlačítkem „Odebrat náhledový obrázek" — původní checkbox „Odstranit obrázek" je zbytečný. |
| Kategorie (9 ks: Divadlo, Filmy, Hudba, Online, Program pro nejmenší, Studentské promítání, Venkovní scéna, Vnitřní scéna, Volba krále) | Nadřazený (rodičovský) termín v taxonomii tribe_events_cat | Název; Popis; Barva (ACF Color Picker) | POZOR: v reálných datech je 9 kategorií, ne 6, jak uvádí zadání. Přejmenování kategorie se automaticky propíše všude — WordPress drží vazbu na ID, ne na text. |
| Žánr (159 ks) | Podřazený (dceřiný) termín v téže taxonomii tribe_events_cat, rodič = kategorie | Název; Nadřazená položka = kategorie (select); Barva (ACF Color Picker, volitelná) | Odchylka od BM Adminu: smazání kategorie ve WP žánry nesmaže, ale povýší je na kategorie. Nutno napsat do návodu. Inline vytvoření z editoru události WP umí („+ Přidat novou položku" s výběrem nadřazené). |
| Barva žánru / kategorie (propisuje se do lineupu) | Jedna ACF skupina polí (Color Picker) navěšená na celou taxonomii tribe_events_cat | Barva (hex) | Frontend řeší barvu kaskádou: barva žánru → když prázdná, barva kategorie → když prázdná, výchozí šedá. Doporučuji vyplnit jen 9 kategorií a žánry nechat dědit; mechanismus ale zvládne obojí, takže doslovný požadavek „barva u žánru" je splněn. |
| Místo (49 v DB, 28 reálně použitých v ročníku 2026) | CPT tribe_venue (The Events Calendar, free) | Název; Adresa; Bezbariérovost (ACT true/false); volitelně Popis a odkaz na mapu | Editor události má našeptávač existujících míst i vytvoření nového přímo z formuláře — chová se stejně jako BM Admin. Smazání místa událost nerozbije, jen zůstane bez místa. |
| Top představení | Nativní checkbox „Feature Event" v sekci Event Options | ano/ne | Řadí před ostatní v programu a zároveň je to zdroj pro blok na homepage — homepage si featured události vytáhne stejným dotazem, nic navíc se nenastavuje. |
| V carouselu | ACF true/false na tribe_events | ano/ne | Samostatný přepínač, protože zadavatel chce „top" a „carousel" ovládat nezávisle. |
| Bezbariérovost | ACF true/false — primárně na tribe_venue, volitelný přepis na tribe_events | ano/ne (místo) + ano/ne/zdědit (událost) | Zadavatel to chtěl na vystoupení, ale je to vlastnost místa: vyplní se jednou pro 49 míst místo 102× ročně. Doporučuji vyplňovat na místě a na události nechat jen výjimky. |
| Země původu | ACF select na tribe_events, výchozí „Česká republika" | select se seznamem zemí | Na kartičkách ani v lineupu se dnes nikde nezobrazuje — ověřit, jestli to není mrtvé pole (viz otevřené otázky). |
| Odkazy na web a sociální sítě | Nativní Event Website + 3–4 pevná ACF URL pole (Instagram, Facebook, Spotify/YouTube) | URL | ACF Repeater je jen v PRO verzi, takže pevný počet polí, ne opakovatelný seznam. Pro kapelu to bohatě stačí. |
| Stránka Program (mřížka kartiček + filtry) | Vlastní shortcode [bm_program] v mini-pluginu | — | Zachová přesně stávající markup .program-card / .card-image / .card-meta / .card-title, který content manager chválí. Filtry kategorie + místo + den řeší data-atributy a čistý JavaScript bez knihovny. |
| Stránka Lineup | Vlastní shortcode [bm_lineup] v témže pluginu | — | Nahrazuje AngularJS 1.5.3. MVP = mobilní seznam po dnech seskupený podle místa, barevné podle kategorie. |
| Přidání do kalendáře (ICS) | Nativní funkce TEC free | — | Na detailu události se objeví „Add to calendar" (Google / iCalendar / Outlook), nic se neprogramuje. Stačí přeložit popisky. |
| Okno festivalu (skrývání minulých ročníků) | Dvě WP options nastavovaná modulem Nastavení | bm_festival_start, bm_festival_end (datum) | Shortcody [bm_program] i [bm_lineup] zobrazují jen události v tomto rozsahu. MEZIMODULOVÁ ZÁVISLOST — rozhraní je jen ta dvě option, kdo je nastavuje, je věc modulu Nastavení. |

### Pluginy

| Plugin | Zdarma | Účel |
|---|---|---|
| The Events Calendar | ano | Datový model a administrace vystoupení: rozsah datum+čas, místa jako samostatné záznamy s našeptávačem, hierarchická taxonomie pro Kategorie→Žánr, Featured Event, ICS export. Frontend pluginu se nepoužívá. |
| The Events Calendar — Featured Event | ano | Checkbox „Top představení" bez vlastního kódu a zdroj pro blok na homepage. |
| The Events Calendar — ICS / Add to calendar | ano | Požadavek zadavatele „funkce přidání do kalendáře" bez jediné řádky kódu. |
| The Events Calendar — Filter Bar (NEPOUŽÍVAT) | **NE** | Vícenásobné filtrování v nativních výpisech TEC. Záměrně se vynechává — filtrování řeší vlastní shortcode, takže placený Filter Bar není potřeba. |
| Advanced Custom Fields (free) | ano | Barva u termínů taxonomie, přepínače „V carouselu" a „Bezbariérovost", select Země původu, doplňkové odkazy. |
| Čeština TEC z translate.wordpress.org | ano | Admin 2 (dramaturgie) je netechnický člověk — anglický administrační panel je reálná překážka převzetí. |

### Co se musí naprogramovat (51 h)

- **Mini-plugin bm-program: shortcode [bm_program] — mřížka kartiček s filtry kategorie + místo + den, zachovaný markup .program-card / .card-image / .card-meta / .card-title, řazení (featured první, pak chronologicky, proběhlé zešedlé a na konec), odznaky „Nutná rezervace" a „Bezbariérové", CSS proměnné barev vygenerované z termínů.** — 14 h
  - Proč to nejde standardně: TEC free nativně neumí filtrovat podle kategorie a místa současně (to je placený Filter Bar) a jeho vlastní výpisy nikdy nevyrobí kartičky, které content manager chválí. DŮLEŽITÉ: ve WordPressu zaškrtnutí žánru „Rock" automaticky nezaškrtne rodičovskou kategorii „Hudba" — kategorii musí kód dopočítat přes get_ancestors() a uložit ji do data-atributu kartičky. Stejnou kaskádou se řeší barva: barva žánru → barva kategorie → výchozí.
- **Shortcode [bm_lineup] — mobilní časový přehled: záložky po dnech, uvnitř seskupení podle místa, řádky seřazené podle času a obarvené podle kategorie, vlastní JS bez knihovny.** — 10 h
  - Proč to nejde standardně: AngularJS 1.5.3 je roky po konci podpory a dvourozměrná tabulka místo×čas je na mobilu nečitelná; 80 % návštěvníků chodí z telefonu. Pozor na pravidlo, ať to po roce nikdo „neopravuje": v datech 2026 je 102 událostí v programu, ale jen 99 v lineupu — tři nemají místo. Místo je nepovinné, mřížka je zobrazí, lineup je přeskočí.
- **Šablona detailu události (single-tribe_events.php v tématu) — obrázek, rozsah data a času, místo s odkazem, barevný odznak kategorie/žánru, bezbariérovost, odkazy na web a sítě, ICS tlačítko, návrat do programu.** — 5 h
  - Proč to nejde standardně: Výchozí šablona TEC vypadá jinak než zbytek webu a ukazuje pole, která BM nepotřebuje.
- **Migrační skript: export z MySQL d66215_bm19pr, transformace a import přes TEC ORM (tribe_events()->set_args([...])->create() spouštěné přes wp eval-file), včetně stažení obrázků z FTP do knihovny médií a vytvoření míst a dvouúrovňových termínů.** — 13 h
  - Proč to nejde standardně: TEC 6 ukládá události do vlastních tabulek wp_tec_events a wp_tec_occurrences — obyčejné „wp post create" + „wp post meta add" událost NEZAREGISTRUJE a v kalendáři se neobjeví. Musí se jít přes oficiální ORM. Zdrojová data jsou unixové timestampy, takže před importem nastavit časovou zónu webu na Europe/Prague a převod udělat explicitně.
- **Čištění taxonomie při migraci: sloučení duplicitních žánrů a doplnění chybějících vazeb.** — 4 h
  - Proč to nejde standardně: V datech je 159 žánrů na 102 událostí, z toho 99 jen pod Hudbou, a jsou tam zjevné duplicity (punk/rock jako ID 98 i 100, disco 116 i 117, Workshop ve čtyřech kategoriích, Přednáška 78 i 88, Rozhovor 71 i 135, Soutěž 45 i 146). Šest událostí nemá kategorii ani žánr vůbec. Bez sloučení bude mít checklist v editoru přes 170 položek a nikdo v něm nic nenajde.
- **Konfigurace TEC a ACF: skrytí nepoužívaného frontendu TEC (slug detailu na /program, přesměrování archivu /events/), vypnutí Google Maps, skupiny polí ACF uložené jako acf-json uvnitř pluginu, role a oprávnění.** — 5 h
  - Proč to nejde standardně: I když se nativní výpisy TEC nepoužívají, adresy /events/ a /event/{slug}/ dál existují a vykreslí se ve cizím stylu — nástupce si bude myslet, že je web rozbitý. Google Maps bez API klíče loguje chyby. ACF skupiny polí žijí jen v databázi, dokud se nezapne Local JSON — pak cestují s kódem pluginu a přežijí i výměnu databáze.

### MVP — musí být do ročníku 2027

- [ ] The Events Calendar free nainstalovaný a nakonfigurovaný, čeština doplněná
- [ ] Dvouúrovňová taxonomie tribe_events_cat: 9 kategorií jako rodiče, vyčištěné žánry jako potomci, barva vyplněná u kategorií
- [ ] 49 míst jako tribe_venue včetně příznaku Bezbariérovost
- [ ] Migrace všech událostí ročníku 2026 (102 ks) přes TEC ORM včetně obrázků, časů a vazeb na místo a žánr
- [ ] Shortcode [bm_program]: mřížka kartiček ve stávajícím vzhledu, filtr kategorie + místo + den, featured první, proběhlé zešedlé
- [ ] Shortcode [bm_lineup]: mobilní seznam po dnech seskupený podle místa, obarvený podle kategorie
- [ ] Detail události: rozsah datum+čas, místo, barevný odznak, bezbariérovost, odkazy, tlačítko přidání do kalendáře (ICS)
- [ ] Checkbox „Top představení" funkční a čitelný pro blok na homepage
- [ ] Respektování okna festivalu z options bm_festival_start / bm_festival_end
- [ ] Mobilní QA na reálném telefonu — mřížka i lineup

### Druhá vlna — může počkat

- Dvourozměrná časová osa lineupu (místo × čas, vodorovný posuv) jako alternativní pohled pro tablet a desktop, +10 až 15 h
- Carousel top představení na homepage (přepínač „V carouselu" existuje od MVP, samotný carousel řeší modul homepage)
- Filtr podle žánru jako druhá úroveň nad filtrem kategorie (dnes to /program umí, ale při 159 žánrech je to spíš na obtíž — zavést až po vyčištění taxonomie)
- Hromadný ICS export celého dne nebo celé vyfiltrované sestavy programu, ne jen jedné události
- Osobní plánovač („moje akce") v localStorage prohlížeče
- Migrace archivních ročníků 2004–2025 do událostí (ids v databázi jdou do 782, takže jde o stovky záznamů) — do MVP patří jen ročník 2026, historii řeší samostatný modul
- Mapa míst festivalu

### Rizika

- TEC 6 ukládá události do vlastních tabulek (wp_tec_events, wp_tec_occurrences). Migrace obyčejným wp post create události založí, ale kalendář je neuvidí a chyba se projeví až u 102 záznamů najednou. Nutné jít přes oficiální ORM tribe_events()->set_args()->create().
- Časová zóna: zdrojová data jsou unixové timestampy. Když se web nenastaví na Europe/Prague ještě před importem, celý program se tiše posune o hodinu a nikdo si toho nevšimne až do festivalu.
- Adresy /events/ a /event/{slug}/ od TEC budou existovat i bez použití jeho výpisů a vykreslí se v cizím vzhledu. Nástupce si bude myslet, že je web rozbitý. Řeší se nastavením slugů a přesměrováním, ale musí to být napsané v návodu.
- Aktualizace TEC mohou přepsat šablonu detailu události. Šablonu je nutné mít v tématu (nadřazení šablon TEC), ne v pluginu, a při větší aktualizaci ji otestovat.
- Hosting Wedos je sdílený: je potřeba ověřit dostupnost WP-CLI přes SSH. Bez něj se migrace přes ORM dělá výrazně hůř (přes jednorázový admin skript) a přidá to 3–5 hodin.
- TEC je velký plugin s vlastními tabulkami — případný odchod od něj za dva roky je nákladný. Riziko je akceptovatelné: 600 tisíc instalací, aktualizace v září 2026, podpora WordPressu 7.1.1, a nástupce najde na Googlu tisíce návodů. Vlastní CPT by tuhle výhodu neměl.
- Pokud bude čeština TEC v administraci děravá, dramaturgie (Admin 2) narazí na anglické popisky polí. Chybějící řetězce lze doplnit přes Loco Translate, ale je to práce navíc mimo odhad.
- ACF Repeater je jen v PRO — odkazy na sítě jsou pevný počet polí. Až někdo bude chtít pátý odkaz, je to zásah do kódu, ne do administrace.

### Jak to vysvětlit tomu, kdo web převezme

> Celý program stojí na pluginu The Events Calendar — to je to jediné, co si nástupce musí umět vygooglit, a návodů je na to plný internet. V administraci platí: Akce = jednotlivá vystoupení (název, popis, obrázek, datum a čas od–do, místo, kategorie/žánr, odkaz na web). Akce → Kategorie akcí = dvouúrovňový strom: nahoře kategorie (Divadlo, Hudba, Venkovní scéna…) s barvou, pod nimi žánry. U akce se zaškrtává ŽÁNR, kategorie se dopočítá automaticky z toho, pod kterou je žánr zařazený. Akce → Místa = seznam míst; bezbariérovost se vyplňuje TADY, ne u jednotlivých vystoupení. Checkbox „Top představení\" v sekci Event Options dostane akci na první místa v programu i na úvodní stránku. Pole navíc (V carouselu, Bezbariérovost, Země původu, odkazy na sítě) jsou z pluginu ACF a jejich definice najdeš v Vlastní pole → Skupiny polí; jsou zároveň uložené v souborech ve složce acf-json uvnitř našeho pluginu, takže cestují s kódem. Vlastního kódu je málo a je na jednom místě: plugin bm-program obsahuje dvě zkratky — [bm_program] (mřížka kartiček na stránce Program) a [bm_lineup] (časový přehled). Obě berou data z Akcí, nikde se nic nepíše ručně podruhé. Datum festivalu se nastavuje v Nastavení a řídí, co se v programu a lineupu vůbec ukáže — po skončení ročníku stačí posunout datum a loňské akce samy zmizí, mazat se nic nemusí. Poslední věc, na kterou se ptá každý: adresy /events/ a /event/... patří pluginu a vypadají jinak než zbytek webu; návštěvníci na ně nechodí, chodí na /program. Není to rozbité, je to tak schválně.

---

## Králové a body ze soutěží

**Odhad: 14–24 h**

Králové jsou jeden vlastní typ obsahu `kral`: jméno = titulek příspěvku, fotka = náhledový obrázek, Škola a Téma jako textová pole — tedy 1:1 s dnešním formulářem BM Adminu. Nový požadavek „body po segmentech“ řeší pole typu Repeater (segment = výběr ze seznamu, hodnota = číslo) a hook, který součet ukládá do meta `body_celkem`, takže v seznamu v adminu zůstane řaditelný sloupec jako dnes. POZOR: Repeater NENÍ v ACF free (je v ACF PRO), ale JE zdarma v Secure Custom Fields — oficiálním forku ACF na WordPress.org; na tom celý modul stojí a je to rozhodnutí, které je potřeba sjednotit napříč projektem. Výpis na web dělá jediný shortcode `[kralove]` v malém vlastním pluginu, který znovu použije stávající HTML třídy (photo/info/name/school/chart/progress/votes), takže vzhled zůstane a přežije i změnu tématu.

### Mapování na WordPress

| Entita | WordPress konstrukt | Pole | Poznámka |
|---|---|---|---|
| Kandidát na krále/královnu | CPT `kral` (registrovaný klikáním v SCF/ACF UI, bez kódu) | Jméno = titulek příspěvku; Fotografie = náhledový obrázek (nativní, hned vidět v seznamu); Škola = text; Téma = text; pořadí na webu = počítá se ze součtu bodů, nikde se nenastavuje | Přesně stejná pole jako dnešní formulář v BM Adminu. Registrovat s capability_type='kral' a map_meta_cap=true, aby šlo Adminu 2 (dramaturgie) dát přístup jen ke králům. Škola zatím jako text, ne taxonomie — je to 1:1 a míň klikání. |
| Body po segmentech soutěže | Pole typu Repeater `body` na CPT kral (SCF free) | Segment = Select (volby se editují v UI field groupy, např. Studentské jamy / Královská hra / Plnění výzev); Hodnota = číslo, celé, default 0 | Select místo volného textu záměrně: jinak 20 kandidátů zapíše tentýž segment pěti způsoby a nikdy z toho nepůjde udělat součtový graf. Přidání segmentu = jeden řádek v nastavení pole, ne kód. |
| Celkový počet bodů | Počítané meta pole `body_celkem` (hook acf/save_post) + řaditelný sloupec v seznamu v adminu | Číslo, jen ke čtení, přepočítá se při každém uložení kandidáta | Nahrazuje dnešní řaditelný sloupec Hlasy. Uložené (ne počítané za běhu) proto, aby šlo řadit přes WP_Query orderby=meta_value_num a aby admin viděl součet v seznamu. |
| Ročník | Hierarchická taxonomie `rocnik` (sdílená s modulem Program) | Termy 2026, 2027…; jeden term na kandidáta; výchozí = aktuální ročník | Záměrně ne datum příspěvku: kandidáti se zakládají v prosinci pro květnový ročník, filtr podle roku publikace by je schoval. WP u hierarchické taxonomie sám přidá filtr do seznamu příspěvků. |
| Stránka Králové (perex + zasazení žebříčku) | Nativní stránka WordPressu + shortcode `[kralove]` | Úvodní odstavec se edituje přímo v editoru stránky; volitelný parametr rocnik="2027" v shortcode | Žádná options page (ta je v ACF placená) — všechno ke králům je na jedné stránce, což je dobrá věta do předávačky. |
| Výpis žebříčku na webu | Shortcode `[kralove]` v malém vlastním pluginu `bm-kralove` | Vypíše <ul class="kings"> s divy photo/info/name/school/chart/progress/votes; šířka pruhu = body_celkem / maximum × 100 %; kotva #slug-školy; schema.org ItemList + position | V pluginu, ne v šabloně tématu — přežije změnu vzhledu a funguje i v blokovém tématu, kde se PHP šablony ignorují. |

### Pluginy

| Plugin | Zdarma | Účel |
|---|---|---|
| Secure Custom Fields (SCF) | ano | Vlastní typ obsahu Král, taxonomie Ročník a všechna pole včetně Repeateru na segmenty bodů — celé klikáním v administraci, bez psaní kódu |
| Members (Theme Hybrid) — alternativa User Role Editor | ano | Role Admin 2 (dramaturgie) s přístupem jen k programu, králům a rezervacím; hodiny počítá modul Role, tady jsou nulové |

### Co se musí naprogramovat (13 h)

- **Hook `acf/save_post`: sečte řádky repeateru `body` a uloží výsledek do meta `body_celkem`** — 2 h
  - Proč to nejde standardně: SCF/ACF neumí počítané pole ani součet repeateru; bez uloženého součtu nejde řadit dotazem ani ukázat sloupec v adminu
- **Řaditelný sloupec „Body celkem“ v seznamu králů + předvyplnění aktuálního ročníku u nového kandidáta** — 2 h
  - Proč to nejde standardně: WordPress ve výchozím stavu sloupce z meta polí nezná a BM Admin řaditelné hlasy měl — admin to bude čekat; default ročníku brání nejčastější chybě (kandidát bez ročníku se na webu neukáže)
- **Shortcode `[kralove]`: dotaz podle ročníku, řazení podle body_celkem (fallback podle jména při samých nulách), výpočet šířky pruhu, výpis karet + mobilní CSS** — 7 h
  - Proč to nejde standardně: Žádný plugin nevygeneruje přesně tenhle výpis s poměrovým pruhem vůči lídrovi a se stávajícími třídami; psát ho jako blok nebo šablonu tématu by bylo dražší a hůř předatelné
- **Migrace dat bez skriptu: SQL výpis 20 kandidátů, ruční zadání v adminu, hromadné nahrání fotek do Médií** — 2 h
  - Proč to nejde standardně: Repeater se ukládá do meta jako body_0_segment / body_0_hodnota — psát kvůli 20 řádkům importér a ladit tenhle formát je dražší než je přepsat; u větších modulů (program, místa) WP-CLI import dává smysl, tady ne

### MVP — musí být do ročníku 2027

- [ ] CPT Král s poli Jméno / Škola / Téma / Fotografie — 1:1 s dnešním BM Adminem, aby nikdo nemusel nic přeučovat
- [ ] Pole Body jako Repeater se Selectem segmentů (Studentské jamy, Královská hra, Plnění výzev…) a automatickým součtem do body_celkem
- [ ] Řaditelný sloupec „Body celkem“ v seznamu králů (parita s dnešním sloupcem Hlasy)
- [ ] Taxonomie Ročník s předvyplněným aktuálním ročníkem; kandidáti minulých ročníků na webu automaticky zmizí
- [ ] Stránka Králové: perex editovatelný v editoru + shortcode [kralove]; karty seřazené podle součtu, pruh v poměru k lídrovi, čitelné na mobilu (fotka s pevným ořezem, jméno, škola — téma, velké číslo)
- [ ] Zachované kotvy #slug-školy (/kralove#gymji) kvůli starým odkazům ze škol a z Instagramu
- [ ] Admin 2 (dramaturgie) může krále zakládat a editovat a nikam jinam se nedostane
- [ ] Zadaných 20 kandidátů ročníku 2026 jako referenční data — bez testovacího záznamu curl_test_final / TestSchool

### Druhá vlna — může počkat

- Veřejný rozpad bodů po segmentech (skládaný pruh nebo rozbalovací <details> pod kartou) — je to levné právě proto, že segmenty jsou Select, ne volný text
- Detail kandidáta: medailonek, odkaz na Instagram, delší text — dnes web žádný detail nemá, jen seznam
- Veřejný archiv králů po ročnících a odznak „vítěz ročníku“ — ověřeno, že dnešní stránka Historie žádné jméno krále neuvádí, takže to nikdo nepostrádá
- Doplnění vítězů 2004–2024 do modulu Historie (pokud se data vůbec někde najdou)
- Váhy segmentů (násobitel), pokud se ukáže, že dramaturgie přepočítává body ručně
- Škola jako taxonomie — až kdyby měla jedna škola víc kandidátů nebo kdyby se chtěla ukázat historie školy napříč ročníky
- Export pořadí (CSV) pro vyhlášení na Sokolském ostrově

### Rizika

- ZADÁNÍ SI ODPORUJE: ACF free Repeater NEMÁ (je v ACF PRO). Zdarma ho má Secure Custom Fields, oficiální fork ACF na WordPress.org, kde je zdarma i Repeater i Options Page. Doporučení: postavit projekt na SCF. Fallback, kdyby SCF neprošel: 5–6 pevných číselných polí (body_jamy, body_hra, …) a součet podle prefixu — méně pružné, zato ještě snáz předatelné.
- Navazuje na to rozhodnutí přes celý projekt: pokud SCF dává Options Page zdarma, padá omezení, kvůli kterému ostatní moduly globální nastavení obcházejí. Sjednotit na úrovni projektu, ne po modulech.
- Záměna pluginů při aktualizaci: na WordPress.org dnes existují dva podobné zápisy (ACF i SCF) a ACF se navíc aktualizuje z vlastního serveru. Nechat nainstalovaný právě jeden a napsat to tučně do předávačky — jinak příští sestava jedním kliknutím přijde o definice polí.
- Body zadává člověk ručně, takže překlep v jednom segmentu změní pořadí na webu. Mitigace: součet je vidět ve formuláři i v seznamu, před vyhlášením se porovná s papírem.
- Na začátku ročníku jsou všude nuly a řazení podle součtu je pak náhodné — nutný fallback na řazení podle jména (dva řádky v shortcode), jinak to vypadá jako chyba.
- Fotky kandidátů mají různé poměry stran; bez pevného ořezu (add_image_size + object-fit) se karty na mobilu rozsypou. Dnešní web to řeší CSS, nová šablona to musí zopakovat.
- V živých datech je testovací záznam (curl_test_final / TestSchool / 0 bodů) — nesmí se dostat do nového webu.
- GDPR: web zveřejňuje jméno, školu a fotku studenta, často nezletilého. Technicky triviální, organizačně to chce souhlasy.
- Sdílené závislosti: taxonomie Ročník patří i modulu Program a role Admin 2 modulu Role. Když je někdo navrhne jinak, tenhle modul se musí přizpůsobit — hodiny na to tady započítané nejsou.

### Jak to vysvětlit tomu, kdo web převezme

> Králové jsou obyčejné příspěvky. V levém menu Králové → Přidat nového: nahoře jméno, vpravo fotka jako náhledový obrázek, dole pole Škola a Téma a tabulka Body — přidáš řádek, vybereš segment soutěže a napíšeš číslo. Součet se počítá sám, vidíš ho ve sloupci „Body celkem“ v seznamu a podle něj se web sám seřadí; nikde se nic nepřetahuje a nikde se pořadí nezadává. Nový ročník = založíš nové kandidáty a u každého vybereš Ročník (je předvyplněný); staré nemaž, z webu zmizí samy. Stránka Králové je normální stránka WordPressu: text nahoře upravíš v editoru, samotný žebříček dělá jediný řádek [kralove] — ten nemaž. Nic z toho není v šabloně vzhledu, ale v pluginu „BM králové“, takže když se jednou překlopí design, králové fungují dál. Jediné, co neměň bez rozmyslu: plugin Secure Custom Fields. V něm jsou definovaná všechna pole a nesmí se vyměnit za podobně vypadající Advanced Custom Fields.

---

## Rezervační systém

**Odhad: 24–44 h**

Doporučuji Event Tickets (free) v režimu RSVP nad The Events Calendar — rezervace jsou vlastnost akce v Programu, ne druhý paralelní systém. Free verze ověřeně pokrývá kapacitu (prázdné pole = neomezeno), okno otevření a uzavření (Start sale / End sale s datem i časem), seznam účastníků s exportem a zrušení rezervace adminem; neumí vlastní pole poznámky ani zrušení návštěvníkem, což řeším malým doplňkem, protože placená cesta (Essentials $259/rok, Pro $399/rok) je nad celý roční rozpočet webu. Events Manager (free) sice podle readme 7.4.5 umí zrušení rezervace návštěvníkem i kapacitu zdarma, ale nese vlastní typ akce a vlastní kalendář — Program by pak žil ve dvou systémech, což je pro předání horší než dvě chybějící funkce; vlastní plugin (50–100 h) odmítám kvůli nulové dokumentaci a komunitě pro příští sestavu. Odhad 24–44 h pokrývá MVP bez pole poznámky; +12 h, pokud se poznámka ukáže jako reálně používaná.

### Mapování na WordPress

| Entita | WordPress konstrukt | Pole | Poznámka |
|---|---|---|---|
| Vystoupení k rezervaci (aktivace rezervací u akce) | RSVP lístek pluginu Event Tickets (post type tribe_rsvp_tickets) připojený k akci tribe_events | Nadpis lístku (text), Capacity (číslo, prázdné = neomezeno), Start sale (datum+čas), End sale (datum+čas), Description (text) | Rezervace se nespravují v samostatné sekci, ale přímo v editaci akce v Programu. To je hlavní rozdíl proti BM Adminu — a hlavní věc, kterou vysvětlit příští sestavě. |
| Maximum míst | nativní pole Capacity v RSVP | celé číslo; prázdné = neomezeno | Chování 1:1 s BM Adminem. |
| Otevření / Uzavření rezervací | nativní pole Start sale / End sale v RSVP | datum + čas u obou | Mimo okno se formulář na webu nezobrazí. |
| Příznak Aktivní | nemá vlastní pole — nahrazuje ho okno Start/End sale (případně smazání RSVP lístku) | — | Jediná funkce BM Adminu, která nemá přímý ekvivalent. Do návodu: 'vypnout rezervace = posunout End sale do minulosti'. |
| Povinná rezervace (⚠️ Nutná rezervace místa na kartičce) | ACF pole typu True/False na akci + jeden řádek v šabloně kartičky | ano/ne | Na živém webu je to jen text v .card-desc kartičky programu — není to funkce rezervačního systému. Patří do šablon modulu Program. |
| Rezervace / účastník | Attendee (interní post type Event Tickets), report Attendees u každé akce | Jméno, E-mail, Počet míst, Datum vytvoření, stav | Jeden návštěvník = jeden záznam; množství míst je pole v RSVP formuláři. |
| Počet rezervací (43/45) v přehledu pořadatele | nativní sloupec v seznamu akcí / v reportu Attendees | obsazeno / kapacita | Na veřejné kartičce programu to ET samo nezobrazí — doplnit v šabloně (viz vlastní kód). |
| Seznam rezervací pro pořadatele + export | Attendees report, tlačítko Export (CSV) | — | Nahrazuje seznam v BM Adminu. |
| Zrušení rezervace — adminem | smazání účastníka v reportu Attendees | — | Zdarma, bez kódu. Uvolní kapacitu. Tohle je MVP varianta požadavku 'přidat zrušení rezervace'. |
| Zrušení rezervace — návštěvníkem | vlastní mini-plugin: odkaz s jednorázovým tokenem v potvrzovacím e-mailu | token (hash), ID účastníka | Ve free verzi neexistuje; self-service editace/zrušení RSVP je placená funkce. |
| Povolit psaní poznámek + Nápověda k poznámce | vlastní mini-plugin: jedno textové pole v RSVP formuláři + ACF pole s textem nápovědy na akci | poznámka (text, volitelná), nápověda (text) | Sběr vlastních údajů u účastníka je placená funkce (custom registration fields). Dělat jen pokud se pole reálně používalo. |
| Popisek pole pro počet míst (default 'Počet míst') | Loco Translate — přepis řetězce v překladu Event Tickets | text | Jde změnit jen globálně pro celý web, ne per akci. Bez kódu, přežije aktualizaci pluginu. |
| Přístup dramaturgie (Admin 2) k rezervacím | role Editor / vlastní role přes plugin Members, s capability na akce a lístky | — | Musí vidět Attendees u všech akcí, ne jen u vlastních. Ověřit capability, kterou ET na report kontroluje. |

### Pluginy

| Plugin | Zdarma | Účel |
|---|---|---|
| Event Tickets | ano | Vlastní rezervační systém — RSVP lístek s kapacitou a časovým oknem u každé akce programu, seznam účastníků, export CSV, potvrzovací e-mail. |
| Event Tickets — report Attendees | ano | Seznam rezervací pro pořadatele a zrušení rezervace adminem (MVP varianta požadovaného 'zrušení rezervace'). |
| Event Tickets — placené balíčky (NEPOUŽÍVÁME, jen pro rozhodnutí) | **NE** | Vlastní pole u rezervace a zrušení rezervace návštěvníkem jsou dostupné jen v placené verzi — tím je zdůvodněný vlastní kód. |
| The Events Calendar | ano | Typ obsahu akce (tribe_events), na který se RSVP připojuje. Rozhoduje se v modulu Program — rezervace na něm stojí. |
| Loco Translate | ano | Přejmenování popisků v RSVP formuláři (např. 'Počet míst') a doladění češtiny Event Tickets bez zásahu do kódu. |
| WP Mail SMTP | ano | Doručení potvrzovacích e-mailů o rezervaci ze sdíleného hostingu Wedos tak, aby nekončily ve spamu. |
| Members | ano | Role Admin 2 (dramaturgie) — přístup pouze k programu, králům a rezervacím. |
| Events Manager (ODMÍTNUTO — jen pro doložení srovnání) | ano | Jediná free alternativa, která umí zrušení rezervace návštěvníkem. Odmítnuta kvůli vlastnímu typu akce, který by rozdvojil Program. |

### Co se musí naprogramovat (33 h)

- **Zobrazení obsazenosti na kartičce programu — do šablony kartičky doplnit 'zbývá X míst' / 'obsazeno' načtené z kapacity RSVP daného lístku.** — 4 h
  - Proč to nejde standardně: Event Tickets ukazuje zbývající kapacitu jen ve svém bloku na detailu akce, ne v přehledu programu. Návštěvník z mobilu potřebuje vidět už v seznamu, že je plno.
- **Badge 'Povinná rezervace' — ACF True/False na akci a jeden řádek v šabloně kartičky i v detailu akce (dnes na webu text '⚠️ Nutná rezervace místa').** — 2 h
  - Proč to nejde standardně: Event Tickets nerozlišuje doporučenou a povinnou rezervaci, je to jen upozornění pro návštěvníka.
- **Zrušení rezervace návštěvníkem — do potvrzovacího e-mailu odkaz s jednorázovým tokenem; po potvrzení se účastník smaže, kapacita se uvolní a pořadateli odejde informační e-mail. RIZIKOVÁ POLOŽKA: sahá do interních API a e-mailové šablony Event Tickets, otestovat po každé aktualizaci pluginu.** — 15 h
  - Proč to nejde standardně: Self-service zrušení nebo editace RSVP návštěvníkem je dostupné jen v placené verzi Event Tickets; free verze umí zrušení pouze rukou admina.
- **PODMÍNĚNĚ — jen pokud odpověď na otázku o poznámkách zní ano: pole 'Poznámka' v RSVP formuláři (jeden hook, ne kopie šablony), uložení do meta účastníka, sloupec v reportu Attendees a v CSV exportu; text nápovědy per akci přes ACF.** — 12 h
  - Proč to nejde standardně: Sběr vlastních údajů u účastníka (custom registration fields) je jen v placené verzi. Ve free verzi RSVP sbírá pouze jméno a e-mail.

### MVP — musí být do ročníku 2027

- [ ] Event Tickets nainstalován a nastaven pouze v režimu RSVP (Tickets Commerce vypnuté)
- [ ] RSVP lístek u ~10 akcí s kapacitou a oknem otevření/uzavření — ověřeno na reálných číslech z 2026 (43/45, 25/25, 10/10)
- [ ] Potvrzovací e-mail účastníkovi reálně dochází a nekončí ve spamu (nastavené SMTP)
- [ ] Pořadatel vidí u akce seznam rezervací a umí ho vyexportovat do CSV
- [ ] Admin umí rezervaci zrušit smazáním účastníka a kapacita se prokazatelně uvolní
- [ ] Badge 'Povinná rezervace' na kartičce programu i v detailu akce
- [ ] Zbývající kapacita / 'obsazeno' viditelné už v přehledu programu
- [ ] RSVP formulář použitelný jednou rukou na mobilu (80 % návštěvnosti)
- [ ] Role Admin 2 (dramaturgie) vidí a spravuje rezervace, nevidí nastavení webu
- [ ] Popisek 'Počet míst' přeložený přes Loco Translate, bez zásahu do kódu
- [ ] Dvoustránkový návod 'Jak otevřít rezervace na akci' + 'Jak zrušit rezervaci' pro příští sestavu

### Druhá vlna — může počkat

- Zrušení rezervace návštěvníkem odkazem v e-mailu — pokud zadavatel myslel právě tohle, přesune se do MVP (+15 h)
- Pole 'Poznámka' u rezervace včetně nápovědy — jen pokud se v 2026 reálně používalo
- Čekací listina, když se akce naplní (LOVU ZDAR! 43/45, Namaluj si kelímek 10/10) — neumí to ani jeden free plugin
- Hromadný e-mail všem účastníkům jedné akce (změna místa, zrušení akce kvůli dešti)
- QR check-in na místě konání
- Automatické mazání osobních údajů účastníků po skončení festivalu (GDPR retence)
- Přejmenování popisku počtu míst per akci — free verze umí jen globálně pro celý web
- Export rezervace do kalendáře (ics) — navazuje na požadavek 'přidání do kalendáře' u programu

### Rizika

- Odhad 24–44 h pokrývá MVP bez pole poznámky; pole poznámky je +12 h a zrušení návštěvníkem +15 h, pokud se ukáže, že jsou potřeba.
- Event Tickets podle všeho nemá samostatný přepínač 'Aktivní' — viditelnost formuláře řídí jen okno Start sale / End sale. Pokud si to nová sestava neuvědomí, zůstane formulář na webu viditelný po festivalu. Nutně do předávacího návodu (a před nasazením ověřit, že skutečně žádný vypínač neexistuje).
- Placená cesta je mimo rozpočet. Oficiální URL Event Tickets Plus přesměrovává na balíčky Essentials $259 / Pro $399 / Elite $599 za rok, přičemž vlastní pole u rezervace jsou uvedena až u Pro. Jakmile někdo řekne 'koupíme si to placené', jeden modul spolkne celý roční rozpočet webu.
- Vazba na modul Program: rezervace stojí na typu akce. Pokud Program nepoběží na The Events Calendar, je nutné rozhodnutí přehodnotit. Dva různé systémy akcí (jeden pro program, druhý pro rezervace) jsou pro předání nejhorší možný výsledek.
- Vlastní zrušení rezervace odkazem duplikuje placenou funkci přes interní API a e-mailovou šablonu pluginu. Event Tickets vydává aktualizace často (aktuálně 5.29.5) — po každé aktualizaci je nutné funkci otestovat.
- E-maily ze sdíleného hostingu Wedos běžně končí ve spamu. Bez funkčního SMTP je rezervační systém k ničemu, protože účastník nedostane potvrzení ani odkaz na zrušení.
- Souběh: dva lidé rezervují poslední místo ve stejnou vteřinu. Přetečení kapacity je typická reklamace — otestovat na akci s kapacitou 1 ještě před festivalem.
- Osobní údaje účastníků (jméno, e-mail) zůstanou v databázi bez automatické retence. GDPR dluh, který se každý rok zvětšuje.
- Event Tickets free 5.29.5 vyžaduje WordPress 6.8+ a PHP 7.4+. Ověřit verzi PHP na Wedos tarifu ještě před rozhodnutím.
- Instalační průvodce Event Tickets tlačí uživatele do zapnutí Tickets Commerce (Stripe/PayPal). To nechceme — eshop běží odděleně na Shoptetu. Riziko, že to nová sestava omylem zapne.

### Jak to vysvětlit tomu, kdo web převezme

> Rezervace nejsou samostatný modul. Jsou to takzvané RSVP lístky pluginu Event Tickets a přidávají se přímo k akci v Programu — žádná zvláštní sekce v administraci neexistuje. Chceš otevřít rezervace na akci? Otevři tu akci, v bloku Tickets dej „Add RSVP“, vyplň Capacity (prázdné pole = neomezeno) a Start sale / End sale, tedy odkdy dokdy se dá rezervovat. Kdo se přihlásil, uvidíš u akce pod „Attendees“; tam se seznam exportuje do CSV a tam se také rezervace ruší (smažeš účastníka a místo se uvolní). Pozor na jednu věc: vypínač „aktivní“ tu není — rezervace vypneš tím, že posuneš End sale do minulosti. Při instalaci přeskoč průvodce a nikdy nezapínej Tickets Commerce; e-shop běží odděleně na Shoptetu a my používáme jen RSVP zdarma. Vlastní kód jsme psali jen na dvě věci, které free verze neumí: odkaz „Zrušit rezervaci“ v potvrzovacím e-mailu a (pokud se dělalo) pole „Poznámka“. Najdeš je v jednom malém pluginu bm-rezervace, jeden soubor, komentovaný česky — když po aktualizaci Event Tickets něco přestane fungovat, začni tam. Všechno ostatní je standardní Event Tickets, na který existuje plná dokumentace, fórum i videa; když si nevíš rady, hledej „Event Tickets RSVP“, ne „Majáles“.

---

## Aktuality, homepage a nastavení ročníku

**Odhad: 30–50 h**

Aktuality jdou na nativní typ „Příspěvek" — nulový vlastní kód, každý návod na WordPress platí. Ročník je vlastní taxonomie `rocnik` (termy 2026, 2027…) přiřazená k příspěvkům i k programu; termové pole z ACF free nesou začátek, konec a volný text s datem na úvod. Ukazatel „který ročník je aktivní" je jedno ACF pole typu Taxonomie na stránce Úvod (obchází PRO Options Page, má právě jednu hodnotu, nejde nastavit dvakrát ani nijak). Obsah neaktivních ročníků se skrývá POUZE ve výpisech (úvodní stránka, archivy, Query Loop bloky) dvěma úzkými háčky; jednotlivé URL i archiv /rocnik/2025/ fungují dál, takže minulé ročníky zůstávají dohledatelné. Úvodní stránka je normální stránka složená z bloků se zámkem `contentOnly`, carousel je CSS scroll-snap bez pluginu.

### Mapování na WordPress

| Entita | WordPress konstrukt | Pole | Poznámka |
|---|---|---|---|
| Aktualita | nativní typ obsahu „Příspěvek" (post) | Titulek, obsah (bloky), náhledový obrázek, datum publikace, rubrika — vše nativní; navíc taxonomie Ročník (přiřazuje se automaticky) | Žádný CPT, žádná ACF pole. Modul v BM Adminu je prázdný, takže se nic nemigruje — začíná se na čistém. V adminu se štítek přejmenuje na „Aktuality" (10 řádků), chování zůstává nativní. Rubriky a štítky se vypnou z menu, aby nemátly. |
| Ročník | vlastní taxonomie `rocnik` (hierarchická, termy „2026", „2027"), registrovaná v ACF free přes UI | Název termu = rok. Termová pole (ACF free, location Taxonomy Term): Začátek festivalu (date picker), Konec festivalu (date picker), Text s datem na úvodní stránku (text, např. „31. května – 6. června 2026"), Krátký popis ročníku (textarea, volitelně pro archiv) | Přiřazená k `post` a k typu programu. `show_admin_column => true` → sloupec Ročník v seznamu příspěvků a filtr zadarmo. `edit_terms` omezit na Admin 1, aby Admin 2 neviděl „+ Přidat nový ročník" v postranním boxu a nezaložil prázdný term bez dat. |
| Aktivní ročník (přepínač) | ACF pole typu „Taxonomie" (single select, taxonomie rocnik) na stránce Úvod | Aktivní ročník (select, povinné) | NÁHRADA ZA ACF OPTIONS PAGE (ta je jen v PRO). Čte se `get_field('aktivni_rocnik', get_option('page_on_front'))`. Výhoda proti zaškrtávátku „Aktivní" na termu: má právě jednu hodnotu, nejdou dva aktivní ani žádný, a nepotřebuje žádný hlídací kód. Admin 2 nemá přístup ke Stránkám, takže ročník nepřepne. |
| Úvodní stránka | nativní stránka „Úvod" nastavená jako titulní (Nastavení → Zobrazování) | Sekce jako bloky: hero (obrázek + `[bm_rocnik]` s datem) / Top představení (Query Loop) / carousel (Query Loop + scroll-snap CSS) / Aktuality (Query Loop, 3 nejnovější) / Partneři | NE šablona front-page.html v Editoru webu — Editor webu je pro nástupce nejděsivější plocha. Stránka se zamkne přes `templateLock: contentOnly` ve vzoru: nástupce mění texty a obrázky, ale nesmaže ani nepřehází sekce. |
| Náhled aktualit na úvodu | nativní blok Query Loop (Smyčka dotazu) | typ Příspěvky, 3 položky, mřížka, náhledový obrázek + datum + titulek | Žádný plugin. Filtr na aktivní ročník doplňuje kód automaticky přes `query_loop_block_query_vars`, takže v bloku není nic natvrdo nastaveného, co by příští rok zastaralo. |
| Top představení / V carouselu / Bezbariérovost | taxonomie `oznaceni` (termy „Top představení", „V carouselu", „Bezbariérové") místo ACF checkboxů | zaškrtávátka v postranním panelu editoru — pro redaktora vypadají stejně jako checkbox | KLÍČOVÉ PRO ÚVOD: nativní Query Loop umí filtrovat podle taxonomie, ale ne podle ACF pole. S taxonomií je úvodní stránka poskládaná bez jediného řádku PHP. Rozhodnutí patří modulu Program — pokud tam padne volba na The Events Calendar, má TEC vlastní příznak „Featured", který „Top představení" nahradí; pak se úvod poskládá nad ním. |
| Archiv minulých ročníků | nativní archiv taxonomie /rocnik/2025/ | — | Vzniká zadarmo registrací taxonomie. Šablona `taxonomy-rocnik` v block theme: hlavička s roky, výpis akcí a aktualit ročníku. Odkazy na /rocnik/YYYY/ se ručně vloží na stránku Historie. Toto je celé řešení „skryté, ale dohledatelné". |
| Odpočet do festivalu | zrušeno, nahrazeno textovým polem | — | Původní JS odpočet je na živém webu rozbitý (překlep `<<h1 id="countdown">`) a po festivalu ukazuje nesmysl. Zadavatel sám chce volný text — ten řeší i nepříjemné období po festivalu („Uvidíme se v roce 2027"). Odpočet lze později dopočítat z data začátku ročníku. |
| Vlastní kód modulu | jeden plugin `bm-core` (wp-content/plugins/bm-core/) | bm-core.php + acf-json/ + assets/bm.css | NE functions.php motivu — při změně vzhledu by se všechno ztratilo. Jeden soubor, komentáře česky, cca 150 řádků. Složka acf-json uvnitř drží definice polí a taxonomií na disku, ne jen v databázi. |

### Pluginy

| Plugin | Zdarma | Účel |
|---|---|---|
| Advanced Custom Fields (free) | ano | Registrace taxonomie `rocnik` přes UI, termová pole (datumy, volný text) a select „Aktivní ročník" na stránce Úvod. |
| Advanced Custom Fields (free) — lokální JSON | ano | Uložení definic polí a taxonomie na disk, aby se daly verzovat a nezmizely s databází. |
| Members | ano | Vytvoření role „Dramaturgie" (Admin 2) bez přístupu ke Stránkám (tedy bez možnosti přepnout ročník) a omezení capability `edit_terms` u taxonomie Ročník na Admina 1. |

### Co se musí naprogramovat (16 h)

- **Skrývání obsahu z neaktivních ročníků ve VÝPISECH — dva úzké háčky: `pre_get_posts` jen pro `is_main_query()` na frontendu, a `query_loop_block_query_vars` pro bloky Query Loop. Vynechává se: admin, REST, singulární stránky (`is_singular()`), vyhledávání, feedy, archiv /rocnik/ a každý Query Loop, který už má vlastní filtr na ročník.** — 6 h
  - Proč to nejde standardně: WordPress nezná pojem „aktivní ročník". Zásadní je úzký záběr: kdyby se filtroval každý dotaz, vypadly by staré příspěvky ze sitemapy a rozbily by se REST obrazovky editoru. A kdyby se filtrovalo i `is_singular()`, vracely by staré akce 404 — přesný opak požadavku na dohledatelnost.
- **Automatické přiřazení aktivního ročníku při uložení příspěvku nebo akce (háček `save_post`, jen když term ještě není nastavený).** — 2 h
  - Proč to nejde standardně: Redaktor nesmí na ročník myslet — jinak se dřív nebo později objeví aktualita bez ročníku, která zmizí z webu a nikdo nepozná proč.
- **Shortcode `[bm_rocnik]` (bez parametrů vypíše volný text s datem; `[bm_rocnik pole="zacatek"]` vypíše datum začátku).** — 2 h
  - Proč to nejde standardně: Termová ACF pole se do bloku jinak nedostanou — ACF bloky ani dynamické vazby ve free verzi tohle nepokryjí. Bez parametrů, aby se nedal napsat špatně; v hero sekci je předvyplněný a blok zamčený.
- **Náhled neaktivního ročníku pro přihlášené: `?rocnik=2027` přepne filtr, ale jen pro uživatele s právem `edit_posts`.** — 2 h
  - Proč to nejde standardně: Bez toho je spuštění nového ročníku skok do tmy — nejde si předem prohlédnout, jak bude úvodní stránka vypadat po přepnutí. 10 řádků, které ušetří stres na den spuštění.
- **CSS carousel — vodorovný pruh karet s `scroll-snap-type: x mandatory` nad nativním Query Loop blokem, styly v `bm-core/assets/bm.css`.** — 3 h
  - Proč to nejde standardně: WordPress nemá nativní carousel blok a plugin (Spectra, Getwid) by kvůli jedné sekci přitáhl celou sadu bloků. Při 80 % návštěv z mobilu je nativní swipe scroll lepší než JS carousel. CSS patří do pluginu, ne do Vzhled → Styly → Další CSS — to pole se váže na motiv a při jeho výměně zmizí.
- **Přejmenování štítků nativního typu „Příspěvky" na „Aktuality" (filtr `post_type_labels_post`) a skrytí Rubrik/Štítků z menu.** — 1 h
  - Proč to nejde standardně: Slovník organizace. Chování zůstává nativní — jen v dokumentaci pro nástupce musí být věta „v návodech na internetu se tomu říká Příspěvky".

### MVP — musí být do ročníku 2027

- [ ] Aktuality jako nativní příspěvky: založení, editace, náhledový obrázek, mobilní vzhled výpisu i detailu
- [ ] Taxonomie Ročník s termovými poli (začátek, konec, volný text s datem) a s automatickým přiřazením při uložení
- [ ] Přepínač aktivního ročníku jako ACF pole na stránce Úvod
- [ ] Skrývání obsahu neaktivních ročníků ve výpisech přes oba háčky — otestované jak na hlavní stránce, tak v Query Loop blocích
- [ ] Archiv /rocnik/YYYY/ funkční a prolinkovaný ze stránky Historie; jednotlivé URL starých akcí a aktualit vracejí obsah, ne 404
- [ ] Úvodní stránka jako stránka „Úvod": hero s volným textem data, Top představení, carousel, 3 nejnovější aktuality — mobile-first
- [ ] Zámek `templateLock: contentOnly` na úvodní stránce
- [ ] Náhled neaktivního ročníku pro přihlášené (?rocnik=2027)
- [ ] Role Dramaturgie bez přístupu ke Stránkám a bez práva zakládat ročníky
- [ ] Návod pro správce jako soukromá stránka v adminu: jak se zakládá nový ročník a jak se přepíná

### Druhá vlna — může počkat

- Odpočet do festivalu dopočítaný z data začátku ročníku (dnes nahrazen volným textem, který si zadavatel výslovně přál)
- Sekce „Dnes na Majálesu" na úvodu — výpis dnešního programu během festivalových dnů
- Fotogalerie a shrnutí navázané na termín ročníku v archivu (/rocnik/2025/ jako plnohodnotná vzpomínková stránka)
- Cache plugin na Wedosu + automatické promazání cache při přepnutí aktivního ročníku
- Automatické sdílení nových aktualit na Instagram/Facebook
- Zpětné doplnění ročníků 2004–2024 ze stránky Historie jako termy taxonomie, aby archiv sahal dál než k roku 2026
- Přepnutí ročníku naplánované k datu místo ručního kliknutí

### Rizika

- Nejpravděpodobnější chyba: Query Loop blok na úvodu ukáže loňské akce, protože se filtruje jen hlavní dotaz. Proto jsou háčky dva. Musí se otestovat záměrně — založit testovací aktualitu v ročníku 2025 a ověřit, že na úvodu není.
- Náhled Query Loop bloku v editoru jde přes REST a staré příspěvky tam vidět BUDE, zatímco na živé stránce ne. Bez věty v návodu to příští správce nahlásí jako rozbitý web.
- Pokud se na stránce Úvod nevyplní aktivní ročník (nebo někdo změní, která stránka je titulní), zmizí z webu všechen program a aktuality naráz. Nutná pojistka: při prázdné hodnotě se nefiltruje nic a v adminu se zobrazí červené upozornění.
- Skrývání se nesmí dotknout `is_singular()` a vyhledávání — jinak staré akce vracejí 404 a požadavek na dohledatelnost padá. Toto je nejsnáz přehlédnutelná chyba v celém modulu.
- Závislost na ACF: taxonomie i pole jsou registrované přes jeho UI, takže deaktivace ACF shodí ročníky. Mitigace je složka acf-json uvnitř bm-core — definice zůstávají na disku a jdou obnovit.
- Editor webu (Site Editor) je pro netechnického nástupce velká plocha, na které jde nenávratně rozbít vzhled. Proto je úvod stránka se zámkem contentOnly, ne šablona. Zámek ale nechrání před tím, kdo se do Editoru webu dostane jinudy.
- Cache na sdíleném hostingu: po přepnutí ročníku můžou návštěvníci ještě hodiny vidět starý obsah. Dokud není promazání cache automatické, musí být v návodu krok „smaž cache".
- Nativní Query Loop nemusí ve všech verzích WordPressu nabízet filtr podle vlastní taxonomie v postranním panelu. Pokud ne, filtr na „Top představení" se doplní kódem — plus cca 2 hodiny.
- Hierarchický box taxonomie v editoru nabízí „+ Přidat nový ročník". Admin 2 tak může založit prázdný term bez datumů. Řeší se omezením capability `edit_terms` na Admina 1 — odkaz se pak nezobrazí.
- Rozpočet: modul sám nepotřebuje žádnou placenou licenci. Pokud ale modul Program sáhne po placené verzi Event Tickets, limit 5000 Kč/rok se vyčerpá tam a na nic jiného nezbude.

### Jak to vysvětlit tomu, kdo web převezme

> Celý ročník se ovládá ze dvou obrazovek a jednou za rok. (1) Aktuality = běžné „Příspěvky" — píšou se jako kdekoli jinde ve WordPressu, k ročníku se přiřadí samy a tři nejnovější se automaticky objeví na úvodu. V návodech na internetu se jim říká Příspěvky, u nás Aktuality; je to totéž. (2) Nový ročník: Program → Ročníky → Přidat nový, název „2027", vyplnit začátek, konec a text s datem, který se ukáže na úvodu. V tu chvíli se nic nestane — ročník je neviditelný a dá se do něj celé měsíce chystat program. Zkontrolovat se dá odkazem s ?rocnik=2027 na konci adresy (funguje jen přihlášenému). (3) Spuštění: Stránky → Úvod → v poli „Aktivní ročník" přepnout na 2027 a uložit. Od té vteřiny web ukazuje jen nový ročník; loňský program a aktuality zmizí z výpisů, ale nic se nesmazalo — jsou dál na svých adresách a pohromadě na /rocnik/2026/, kam vede odkaz ze stránky Historie. Pak promazat cache. Úvodní stránka se edituje jako každá jiná stránka: texty a obrázky ano, sekce přehazovat nejdou — jsou zamčené schválně. Všechen vlastní kód projektu je v jediném souboru wp-content/plugins/bm-core/bm-core.php s českými komentáři; kromě něj běží web na nativním WordPressu a třech pluginech. Do Editoru webu (Vzhled → Editor) není potřeba chodit na nic kromě barev a písma.

---

## Partneři, osvěžovny, fotogalerie, historie

**Odhad: 15–26 h**

Celý tenhle modul řeš BEZ vlastních typů obsahu, BEZ ACF a BEZ PHP šablon — jsou to obyčejné WordPress stránky poskládané z core bloků. Partneři = stránka, kde každá sekce je nadpis H2 a pod ním blok Galerie (core Gallery umí u KAŽDÉHO obrázku vlastní odkaz — ověřeno v dokumentaci WP). Osvěžovny = stránka poskládaná z opakovaného block patternu "Karta osvěžovny" (Nadpis + adresa + tabulka položka/cena), vzhled kartiček je cca 100 řádků CSS v child tématu. Historie = stránka "Historie" a jejích 21 podstránek 2004–2024, takže URL /historie/2024 zůstávají beze změny; fotogalerie žije přímo v podstránce ročníku jako blok Galerie s nativním lightboxem. Jediný nutný plugin je Imsanity, který zmenšuje nahrávané fotky, aby originály 6720×4480 nezaplnily sdílený Wedos.

### Mapování na WordPress

| Entita | WordPress konstrukt | Pole | Poznámka |
|---|---|---|---|
| Partner (46 log na /partners, zadání mluví o 52) | nativní — blok Galerie na stránce "Partneři", žádný CPT | Logo = obrázek z Knihovny médií; Odkaz = u jednotlivého obrázku Odkaz → Vlastní URL (core Gallery to umí, ověřeno); Alt text = popisek pro čtečky; Pořadí = přetažením v bloku | Loga bez odkazu (strecha.png, GUERILLLA.png) prostě odkaz nemají. 9 log má na starém webu třídu "squares" (jiný poměr stran) — v Galerii to řeší přepínač "Oříznout obrázky" + "Poměr stran", ne CSS hack. |
| Sekce partnerů (8 úrovní, ne 3: Projekt vznikl za podpory / Hlavní produkční / Kreativní / Hlavní mediální / Mediální / Partneři festivalu / Partneři specifických částí / Ve spolupráci s) | nativní — nadpis H2 uvnitř stránky | Název sekce = text nadpisu; Pořadí sekcí = přesun bloku nahoru/dolů | Toto je klíčové zjednodušení: "správa sekcí" = zadavatel přepíše nadpis nebo přidá nový. Žádná taxonomie, žádný číselník, nic k nastudování. |
| Osvěžovna (9 podniků) | nativní — block pattern "Karta osvěžovny" (nesynchronizovaný), soubor v child tématu | Název podniku = nadpis H3; Adresa + odkaz na mapy = odkaz v nadpisu; Podnadpis Alko/Nealko = odstavec se stylem (volitelný); Položky = tabulka 2 sloupce (název \| cena) | Data NEJSOU uniformní: jedna karta je jen "Sleva 20 % na vše (heslo: Majáles)", jedna položka má dlouhý popis ingrediencí. Rigidní struktura (repeater) by to rozbila — volný pattern ne. |
| Položka menu osvěžovny (24 kusů) | nativní — řádek tabulky uvnitř karty | Název položky = text; Cena = text (ne číslo — je tam "105 Kč" i "Sleva 20 %") | Mění se jednou ročně jedním člověkem. Vlastní datový model by se tu nikdy nevyplatil. |
| Ročník historie (21 ročníků 2004–2024) | nativní — podstránka stránky "Historie" | Rok = titulek stránky ("2024"); Text ročníku = obsah stránky (import ze staré DB dopadne jako blok Klasický editor, to je v pořádku); Fotky = blok Galerie | URL /historie/2024 zůstává identická jako dnes → nula přesměrování. Starý <select> pro výběr ročníku nahraď vodorovným seznamem let — na mobilu je to lepší a je to prostý seznam odkazů. |
| Fotka (47 kusů, na ročníku 2024 jich je 21) | nativní — obrázek v bloku Galerie uvnitř podstránky ročníku | Soubor = obrázek; Alt text; Popisek = volitelný | Lightbox je v core ("Zvětšit po kliknutí") včetně listování vpřed/vzad — žádný galerijní plugin. Všechny popisky na starém webu jsou placeholder "Fotil", takže se na ně nespoléhej. |
| Fotogalerie (nová položka menu) | nativní — jedna stránka "Fotogalerie" | Jedna dlaždice na ročník = obrázek s odkazem na /historie/{rok} | ZÁMĚRNĚ neduplikuje fotky. Je to rozcestník, ne druhé úložiště. Netechnik tak nikdy neřeší "kam ta fotka patří". |
| Podpoř nás | nativní — stránka + blok Vlastní HTML | iframe na donio.cz | Starý iframe má natvrdo height 4000px, na mobilu je to rozbité. Při přepisu buď responzivní obal, nebo jen velké tlačítko "Přispět na Donio". |
| E-shop | nativní — externí položka v menu | Text + URL shop.budejovickymajales.cz | Shoptet zůstává mimo, 5 minut práce. |

### Pluginy

| Plugin | Zdarma | Účel |
|---|---|---|
| Imsanity | ano | Automaticky zmenší každou nahranou fotku na max 2000 px. Bez toho hosting sežerou originály — na ročníku 2024 je 6 fotek 6720×4480 a WP z každé ještě generuje sadu náhledů. |
| Enable Media Replace | ano | Nižší priorita než Imsanity. Když partner pošle nové logo, admin ho vymění na místě místo mazání a znovuvkládání do galerie. Reálně šetří nervy netechnikovi. |

### Co se musí naprogramovat (14 h)

- **Child téma + CSS pro kartičky osvěžoven a mřížku log partnerů (cca 100 řádků)** — 4 h
  - Proč to nejde standardně: Vzhled, který content manager výslovně chválí — bílá karta, modrý rám 3px (#0000ff), tvrdý plakátový stín 8px, cena vpravo tučně modře, posun karty při hoveru — není v žádném core bloku ani v běžném tématu. Zároveň je to jediná část modulu, která opravdu vyžaduje programátora, a je to čisté CSS bez logiky.
- **Tři block patterny jako soubory v child tématu (patterns/osvezovna.php, patterns/partneri-sekce.php, patterns/historie-rocnik.php) — jen hlavičkový komentář a statický block markup, žádná PHP logika, nic se neregistruje ve functions.php** — 3 h
  - Proč to nejde standardně: WordPress patterny ze složky patterns/ načítá sám. Bez nich by netechnik musel skládat Group → Nadpis → Tabulka ručně a pokaždé jinak. S nimi je přidání nové osvěžovny "+ → Vzory → Karta osvěžovny → přepsat texty".
- **Jednorázový migrační skript (Bash + WP-CLI: wp media import, wp post create) nad SQL dumpem d66215_bm19pr pro 46+ log, 21 textů ročníků a 47 fotek** — 6 h
  - Proč to nejde standardně: Ruční překlikání je 10+ hodin a vnese překlepy v URL partnerů. Skript se po migraci zahodí, v produkci nezůstává žádný vlastní kód. Pozor: názvy souborů fotek (2025_1.jpg) NEODPOVÍDAJÍ ročníku (jsou na stránce ročníku 2024) — mapování fotka→ročník musí vzniknout z databáze, ne z názvů.
- **Přesměrování starých URL /partners, /osvezovny na nové české adresy (.htaccess)** — 1 h
  - Proč to nejde standardně: Sdílené odkazy a Google. /historie/{rok} se nemění, takže jde jen o dvě až tři pravidla. KOORDINOVAT s modulem migrace, ať to nikdo nedělá dvakrát.

### MVP — musí být do ročníku 2027

- [ ] Stránka Partneři: 8 sekcí jako nadpisy H2, pod každou blok Galerie s logy, u každého loga vlastní odkaz. Nesourodé poměry stran srovnat přepínačem Oříznout obrázky.
- [ ] Stránka Osvěžovny: block pattern Karta osvěžovny + CSS v child tématu, 9 karet a 24 položek přepsaných ze současné stránky.
- [ ] Historie: stránka Historie + 21 podstránek 2004–2024 s textem ročníku, URL /historie/{rok} zachovány, výběr ročníku jako vodorovný seznam let místo <select>.
- [ ] Fotky ročníku 2024 (21 kusů) v bloku Galerie s nativním lightboxem Zvětšit po kliknutí.
- [ ] Imsanity nainstalovaný a nastavený na 2000 px JEŠTĚ PŘED nahráním první fotky.
- [ ] 3 loga ve formátu SVG převedená na PNG (WP core SVG nepustí a Safe SVG je zbytečný dluh).
- [ ] Podpoř nás (donio) a externí odkaz na E-shop v menu.

### Druhá vlna — může počkat

- Migrace fotek starších ročníků než 2024 (zbývajících cca 26 fotek) — až bude jasné, ke kterým ročníkům patří.
- Stránka Fotogalerie jako rozcestník s dlaždicí na každý ročník. Do prvního ostrého provozu stačí, že fotky jsou v Historii.
- Doplnění alt textů k logům partnerů a fotkám (přístupnost + SEO).
- Synchronizovaný vzor s pruhem hlavních partnerů na homepage — dnes tam žádná loga nejsou, tak to nedělej dopředu.
- Sjednocení 8 sekcí partnerů na méně úrovní, pokud si to zadavatel po zkušenosti bude přát.

### Rizika

- PAST ACF: v ACF free nejsou JEN Options Pages, ale ani Repeater, Gallery a Flexible Content. Kdokoli sáhne po repeateru na 24 položek menu osvěžoven nebo po galerijním poli u ročníku, narazí na placenou licenci nebo si to musí naprogramovat. Proto tento návrh ACF nepoužívá vůbec.
- WordPress core nepustí upload SVG. Tři loga (ke.svg, igy.svg, HGSecurity.svg) jsou SVG. Řeš převodem na PNG při migraci, NE instalací Safe SVG — to je další plugin k údržbě kvůli třem souborům.
- CSS kartiček žije v child tématu. Když příští sestava vymění téma, vzhled zmizí. Mitigace: child téma, jméno souboru výslovně v předávacím zápisu, a v adminu poznámka v patternu.
- Originály fotek mají až 6720×4480 (zhruba 8–12 MB každá). WordPress sice sám dělá zmenšenou -scaled kopii na 2560 px, ale ORIGINÁL na disku nechává. Bez Imsanity se sdílený Wedos zaplní během dvou ročníků.
- Návrh předpokládá blokové téma a Gutenberg bez page builderu. Pokud jiný modul (homepage, lineup) prosadí Elementor nebo WPBakery, patterny i CSS se musí předělat — tohle rozhodnutí musí být napříč projektem jedno.
- Řazení 21 fotek v bloku Galerie se dělá přetahováním. Na mobilu nebo tabletu je to nepříjemné. Do předávacího zápisu patří věta, že fotky se nahrávají z počítače, ne z telefonu.
- Import 21 textů ročníků ze staré DB doputuje jako blok Klasický editor (staré HTML má entity typu &aacute;). To je funkčně v pořádku a zobrazí se správně, ale editace je pak ve starém editoru. Nepřevádět na bloky ručně — 21× klik navíc bez užitku.
- Riziko rozplizlé odpovědnosti: sekce partnerů jsou jen nadpisy, takže je může kdokoli s právem editovat stránky přejmenovat nebo smazat i s logy. U role Admin 2 (dramaturgie) tedy stránky Partneři/Osvěžovny/Historie NEmají být editovatelné.

### Jak to vysvětlit tomu, kdo web převezme

> Tenhle modul nemá v adminu žádnou vlastní obrazovku. Všechno jsou obyčejné stránky — v levém menu Stránky.
> 
> PARTNEŘI: Stránky → Partneři. Každá sekce (Mediální partneři, Za podpory…) je velký nadpis a pod ním blok Galerie. Nové logo přetáhneš myší do galerie, pak na něj klikneš a vpravo v panelu nastavíš Odkaz → Vlastní URL a vložíš web partnera. Novou sekci uděláš tak, že napíšeš nadpis a pod něj vložíš prázdnou Galerii. Logo bez odkazu prostě odkaz nemá.
> 
> OSVĚŽOVNY: Stránky → Osvěžovny. Nový podnik přidáš tlačítkem + → záložka Vzory → „Karta osvěžovny" a jen přepíšeš texty. Ceny jsou obyčejná tabulka.
> 
> HISTORIE: Stránky → Historie a její podstránky 2004 až 2024. Nový ročník uděláš tak, že poslední podstránku duplikuješ, přejmenuješ na rok, přepíšeš text a v bloku Galerie vyměníš fotky. Adresa /historie/2027 vznikne sama podle názvu.
> 
> DVĚ VĚCI NEVYPÍNEJ: plugin Imsanity (zmenšuje nahrávané fotky na 2000 px, jinak se hosting zaplní) a child téma majales-child — v jeho souboru style.css je vzhled kartiček osvěžoven (modrý rám a stín). Fotky nahrávej z počítače, ne z telefonu.
> 
> Když něco pokazíš, otevři tu stránku a vpravo nahoře klikni na Revize — vrátíš se ke starší verzi. Žádná z těchto stránek nemá vlastní databázovou tabulku ani vlastní typ obsahu, takže není co rozbít hlouběji než v textu stránky. Programátora na tenhle modul nepotřebuješ.

---

## Navigace, statické stránky a editovatelný vzhled

**Odhad: 42–62 h**

Doporučuji Kadence (free téma) + Kadence Blocks (free) + nativní blokový editor; žádný page builder. Vzhled se mění výhradně v Customizeru — 9-slotová globální paleta, typografie, drag&drop hlavička/patička — a veškeré vlastní CSS se píše do Doplňkového CSS s odkazem na var(--global-palette1..9), takže změna jedné barvy překreslí lišty, tlačítka i kartičky naráz. GeneratePress odpadá, protože stavba hlavičky je tam až v GP Premium za 59 USD/rok, zatímco Kadence ji má zdarma. Bricks odmítám (layout se ukládá jako Bricks JSON v postmeta — po přechodu na jiné téma zůstanou prázdné stránky, a každá nová sestava se musí učit další nástroj), WPBakery odmítám taky (po deaktivaci zůstane na stránkách shortcode soup).

### Mapování na WordPress

| Entita | WordPress konstrukt | Pole | Poznámka |
|---|---|---|---|
| Statické stránky (15 stávajících + 4 nové) | nativní (WP Pages) | Titulek (text), Obsah (bloky), Náhledový obrázek (obrázek), Nadřazená stránka (výběr), Pořadí (číslo), Trvalý odkaz/slug (text) | Žádné CPT, žádná ACF pole. Obsah dnešních stránek je ručně psané HTML přímo v Nette šablonách včetně vloženého <style> bloku (ověřeno v osvezovny.html, faqs.html) — přenáší se ručně copy/paste, SQL dump + WP-CLI import se na tenhle modul NEVZTAHUJE. Před fixací odhadu ověřit na FTP, jestli texty nejsou přece jen v DB. |
| Hlavní navigace (6 položek + podpoložky) | nativní (Vzhled → Menu) + Kadence header builder | Název položky (text), Odkaz (stránka nebo URL), Úroveň (drag&drop odsazení) | Rodiče Program / Partneři / Praktické info / O festivalu zakládám jako 'Vlastní odkaz' s URL '#', aby se na mobilu jen rozbalily a neodnavigovaly pryč. Úvod a Králové jsou přímé odkazy. E-shop = externí odkaz na Shoptet. Klasické menu, ne blok Navigace — pro nováčka je přetahování položek nesrovnatelně srozumitelnější. |
| Barvy webu (lišty, pozadí, tlačítka, odkazy, rámečky) | nativní (Customizer → Kadence → Barvy, 9-slotová globální paleta) | Slot 1 = hlavní akcent #0000D3, slot 2 = druhý akcent #ff0055, sloty 3–6 = texty a rámečky (černá → šedé), sloty 7–9 = pozadí (světlé → bílá) | KLÍČOVÉ: sloty mají v Kadence sémantický význam (akcent / text / pozadí), nejsou to volné brandové barvy. Šest vedlejších barev z main.css (#FFC808, #9166AC, #006633, #FF8009, #FFC9F3, #979B3F) do palety NEPATŘÍ — jsou to barvy kategorií programu (dnes třídy filter-c1/c3/c5/c7/c10 na kartách) a patří jako pole 'barva' na taxonomii žánru/kategorie v modulu Program. To je rozhraní mezi moduly, nesmí se to duplikovat. |
| Písma | nativní (Customizer → Kadence → Typografie) | Rodina nadpisů, Rodina základního textu, velikosti a řezy per element, přepínač 'Load Google Fonts Locally' v General → Performance | Dnešní font je GT Walsheim (Grilli Type, komerční) self-hostovaný jako WOFF2 + Space Mono taženo @importem z Google CDN. Kadence free picker neumí vybrat vlastní soubor — je to buď OFL font z Google Fonts (klient si ho mění z rozbalovátka, GDPR čisté díky lokálnímu hostování), NEBO GT Walsheim přes @font-face v Doplňkovém CSS (mění ho jen ten, kdo umí do CSS). Ne obojí. Plugin Custom Fonts for Kadence (Hearten Made) existuje, ale není v repozitáři wordpress.org = bez auto-updatů, proto ho nenasazuji. |
| Kartičky (osvěžovny, partneři, obecné boxy) | nativní synchronizovaný vzor + Kadence Blocks Row Layout / Info Box | Nadpis, Text, Odkaz, Obrázek; rámeček a stín nastavené barvou ze slotu palety, ne hexem | Vizuál převzatý z dnešního webu, který content manager chválí: bílé pozadí, 3px rámeček, tvrdý plakátový stín 6px 6px 0 (bez rozostření), uppercase 800/900, hover translate(-4,-4). Uloží se jako synchronizovaný vzor 'BM kartička' → vkládá se jedním kliknutím a změna vzoru se propíše všude. |
| FAQ (12 otázek) | nativní stránka + blok Kadence Accordion | Otázka (nadpis panelu), Odpověď (obsah panelu) | Dnes je to 12 <h3> pod sebou. Accordion šetří na mobilu place a je bez pluginu navíc. |
| Historie (21 ročníků 2004–2024) | nativní (WP Page + core blok Tabs z WP 7.1, fallback Accordion) | Ročník (záložka), Text + fotky (obsah záložky) | Žádné CPT pro ročníky — je to statický text, který se doplňuje jednou za rok. Core Tabs místo Kadence Tabs = o jednu závislost míň. |
| Fotogalerie (47 fotek) | nativní (blok Galerie + core lightbox 'Zvětšit po kliknutí') | Obrázky (multiupload), Popisek, Počet sloupců | Bez pluginu. Carousel až kdyby ho zadavatel opravdu chtěl → Kadence Advanced Gallery má rozvržení Carousel ve free verzi. |
| Homepage | nativní (WP Page nastavená jako titulní stránka) | Hero obrázek, volné textové pole s datem festivalu (blok Nadpis/Odstavec), odpočet (Kadence Countdown), sloty pro Top představení a náhled aktualit | Volné textové pole s datem je prostý blok přímo na stránce — NENÍ potřeba žádná options page (ACF Options je jen v PRO verzi). Dynamické části (Top představení, náhled aktualit) dodávají moduly Program a Aktuality jako bloky, tenhle modul vlastní jen skořápku a rozložení. Hranici je nutné respektovat, ať se hodiny nepočítají dvakrát. |
| Logo / majálesový nápis | nativní (Customizer → Identita webu) | Logo (obrázek), Šířka loga, Favicon | Dnes je wordmark PNG (napis25NEW.png) — a to je správně, zůstává obrázek. Rebranding ročníku = nahrání nového souboru, ne výměna fontu, a tím pádem žádná licenční otázka. SVG jde nahrát jen s pluginem Safe SVG. |
| Staré URL (/aboutUs, /faqs, /program/ucinkujici, /kralove…) | plugin (Redirection) | Zdrojová adresa, Cílová adresa, Typ 301 | Rozhodnuto: české slugy + cca 13 pravidel 301, vypsaných na předávací stránce. Anglické slugy v menu jsou pro neziskovku čitelnostní dluh; alternativa 'nechat /aboutUs navždy' je horší. |
| Vložený Donio widget (Podpoř nás) | nativní (blok Vlastní HTML) | HTML kód iframe | Administrátor má schopnost unfiltered_html, plugin na embedy není potřeba. |
| Patička (3 sloupce odkazů + ikony sociálních sítí) | nativní (Kadence footer builder + menu v patičce) | Sloupce, Menu, Text copyrightu, Ikony sítí | Dnes je patička natvrdo v šabloně a má rozdílný obsah pro desktop a mobil. V Kadence je to jedno menu, které se samo přeskládá. |
| Přístup Admina 2 (dramaturgie) ke vzhledu | nativní (schopnost edit_theme_options) | — | Customizer, Menu i Doplňkové CSS vyžadují edit_theme_options = jen role Administrátor. Admin 2 na roli Editor se k nim nedostane bez jediného pluginu navíc. Omezení viditelnosti Stránek/Příspěvků pro Admina 2 řeší modul Role a oprávnění. |

### Pluginy

| Plugin | Zdarma | Účel |
|---|---|---|
| Kadence (téma) | ano | Hlavička a patička drag&drop, globální paleta barev, typografie, lokální hostování Google Fonts |
| Kadence Blocks | ano | Bloky pro kartičky, FAQ accordion, galerii, odpočet na homepage |
| Redirection | ano | 301 přesměrování ze starých anglických adres na české slugy |
| Safe SVG | ano | Nahrání loga a ikon ve formátu SVG |

### Co se musí naprogramovat (11 h)

- **Doplňkové CSS pro kartičky programu a lineupu (3px rámeček, tvrdý offset stín, uppercase nadpisy), napsané přes var(--global-palette1) a var(--global-palette2) — nikdy přes hex** — 4 h
  - Proč to nejde standardně: Karty v programu a lineupu vykresluje šablona modulu Program, ne blok, takže se na ně nastavení z bloku nevztahuje. Kdyby se hexy napsaly natvrdo, změna barvy v Customizeru by se na ně nepropsala a klient by usoudil, že systém nefunguje. Píše se do Vzhled → Přizpůsobit → Doplňkové CSS, ne do child tématu ani do functions.php.
- **Sjednocení modré: nahradit #0000ff z inline stylů dnešních šablon za slot 1 palety (#0000D3)** — 2 h
  - Proč to nejde standardně: Na dnešním webu jsou dvě různé modré — main.css používá #0000D3, ale inline CSS kartiček v program.html a osvezovny.html používá #0000ff. Bez sjednocení bude 'změna barvy jedním klikem' fungovat jen na části webu.
- **@font-face pro GT Walsheim v Doplňkovém CSS + přepsání rodiny nadpisů — POUZE pokud se dohledá platná webfont licence** — 2 h
  - Proč to nejde standardně: Kadence free picker nabízí Google Fonts a systémové rodiny, vlastní soubor vybrat neumí. Plugin, který to řeší (Custom Fonts for Kadence od Hearten Made), není v repozitáři wordpress.org, takže by se sám neaktualizoval. Tohle je jediná položka, kterou doporučuji radši ÚPLNĚ vynechat a vzít OFL font z rozbalovátka.
- **Mobilní doladění hlavičky a kartiček na breakpointech 700 a 1100 px podle dnešního layoutu** — 3 h
  - Proč to nejde standardně: Kadence defaulty jsou slušné, ale plakátový stín a 3px rámeček se na úzkém displeji chovají jinak než na desktopu a 80 % návštěv je z mobilu. Testuje se na reálném telefonu, ne v devtools.

### MVP — musí být do ročníku 2027

- [ ] Navigace: 6 hlavních položek s rozbalovacím podmenu, rodiče jako '#' toggle, na mobilu off-canvas panel
- [ ] 19 stránek (15 převedených + 4 nové: Pro návštěvníky, Přístupnost, Doprava, Fotogalerie) s obsahem a českými slugy
- [ ] 13 pravidel 301 ze starých anglických adres, aby nezemřely odkazy z plakátů a sociálních sítí
- [ ] Globální paleta (slot 1 = #0000D3, slot 2 = #ff0055, šedé, bílá) a typografie plně měnitelné klientem v Customizeru bez vývojáře
- [ ] Písmo nahrazené OFL fontem z Google Fonts se zapnutým lokálním hostováním — vyřeší i dnešní GDPR problém se Space Mono z Google CDN
- [ ] Homepage: hero, volné textové pole s datem festivalu, odpočet, sloty pro Top představení a náhled aktualit od ostatních modulů
- [ ] Synchronizovaný vzor 'BM kartička' pro osvěžovny a partnery + FAQ jako accordion
- [ ] Vizuální kontrola všech stránek na reálném telefonu, ne jen v prohlížeči
- [ ] Skrytá stránka 'Předání' v administraci (5 míst, kde se co mění, seznam 4 pluginů) + 2h zaškolení s nahraným záznamem

### Druhá vlna — může počkat

- Carousely na homepage ve stylu dikyzemuzem.cz — do MVP stačí statická mřížka tří karet a tlačítko 'Zobrazit celý program'
- Fotogalerie s carouselem a filtrováním po ročnících (v MVP prostá mřížka + core lightbox)
- Detailní stránka Přístupnost navázaná na checkbox Bezbariérovost z modulu Program
- Kontaktní formulář — na dnešním webu žádný není (ověřeno, nikde v HTML není <form>), zatím stačí e-mailové adresy
- SVG logo místo PNG (vyžaduje Safe SVG)
- Vlastní OG obrázky pro jednotlivé stránky (dnes je jeden sdílený og.png)
- Nasazení GT Walsheim, pokud se dohledá webfont licence
- Sjednocení patičky a menu do jedné spravované struktury (dnes má desktop a mobil rozdílné odkazy)

### Rizika

- Doplňkové CSS z Customizeru je uložené jako záznam custom_css svázaný s aktivním tématem — při výměně tématu se NEPŘENESE a web se vizuálně rozsype. Proto musí být v předávací stránce věta 'téma se nemění' napsaná doslova.
- GT Walsheim je komerční font Grilli Type, dnes nasazený self-hosted jako WOFF2 v /assets/fonts/. Bez doložené webfont licence je to právní riziko, které mlčky dědí každá další sestava.
- Dnešní main.css tahá Space Mono @importem z fonts.googleapis.com — přenos IP adres návštěvníků bez souhlasu. Kadence to umí vyřešit přepínačem, ale ten se musí zapnout a výsledek ověřit v záložce Network, ne jen věřit nastavení.
- Dvě různé modré na webu (#0000D3 v main.css vs #0000ff v inline CSS kartiček). Pokud se při migraci nesjednotí, změna barvy v Customizeru se propíše jen částečně a klient ztratí důvěru v celý systém.
- Kadence patří StellarWP (Liquid Web) — na pětiletém horizontu může změnit licenční politiku free verze. Zmírnění: téma je GPL, poslední stažená verze zůstává funkční, obsah je v nativních blocích, takže přechod na jiné téma znamená přenastavit vzhled, ne přepsat obsah.
- Největší riziko není technické: příští sestava začne instalovat pluginy a page builder navrch a za dva roky je z toho zase neudržovatelný web. Předávací stránka proto musí obsahovat explicitní seznam 'tohle jsou 4 pluginy, nic dalšího neinstalujeme' a důvod.
- WP-CLI nemusí být na sdíleném Wedosu dostupné; instalace pak jde přes webové rozhraní. Pro tenhle modul nekritické, pro migrační moduly ano.
- Odhad 10–16 h na přenos obsahu platí jen tehdy, když texty přijdou hotové a odsouhlasené. Přepisování textů během migrace odhad zdvojnásobí.
- Kadence Blocks free nemusí umět box-shadow s nulovým rozostřením — pak plakátový vzhled kartiček spadne celý do Doplňkového CSS, což je o kus víc kódu k předání (+2 h).

### Jak to vysvětlit tomu, kdo web převezme

> Všechno, co bude nová sestava měnit, je na pěti místech a nikde jinde. (1) Vzhled → Přizpůsobit → Kadence → Barvy: paleta devíti barev; změna slotu 1 překreslí lišty, tlačítka, odkazy i rámečky kartiček naráz — to je ta ukázka, kterou předvedeš při předání jako první, protože po ní všichni pochopí, jak web funguje. (2) Vzhled → Přizpůsobit → Kadence → Typografie: písma, vybírají se z rozbalovátka Google Fonts. (3) Vzhled → Přizpůsobit → Identita webu: logo ročníku — je to obrázek, ne font, takže rebranding nevyžaduje nic řešit s licencemi. (4) Vzhled → Menu: navigace, přetahováním položek myší. (5) Stránky: texty, obrázky a odkazy v blokovém editoru. Šesté místo je Vzhled → Přizpůsobit → Doplňkové CSS — je tam zhruba 40 řádků vlastního CSS okomentovaných česky, které používají proměnné palety (var(--global-palette1)), takže se do nich při změně barev NEMUSÍ sahat. Dvě věty, které musí zaznít nahlas: téma se nemění (vlastní CSS je na téma navázané a při přepnutí zmizí) a pluginy jsou čtyři, jejich seznam je na skryté stránce 'Předání' v administraci a nic dalšího se neinstaluje. Rebranding ročníku je pak nahrát nové logo, přepnout slot 1 palety a vybrat jiné písmo — třicet minut, bez vývojáře. Předávací stránka obsahuje i odkaz na nahraný záznam dvouhodinového zaškolení, aby se nová sestava nemusela ptát té předchozí.

---

## Role, účty, hosting a migrace dat

**Odhad: 26–42 h**

Zůstat na Wedosu, tarif NoLimit (obnova ~121 Kč/měs s DPH, tj. ~1 450 Kč/rok — vejde se do rozpočtu i s doménou), NoLimit WP ani VPS nekupovat. Role řešit pluginem Members (free): Admin 1 = nativní Administrator, Admin 2 = vlastní role „Dramaturgie" naklikaná z capabilities The Events Calendar, Event Tickets a CPT Králové — nula řádků PHP. Migrace NEJDE přes WP-CLI na serveru, protože Wedos na sdíleném hostingu nedává SSH: celý web se postaví a naimportuje LOKÁLNĚ (DDEV / Local, oba mají WP-CLI v sobě) a na Wedos se nasadí jedním balíčkem přes Duplicator Lite + FTP. Kritické riziko osobního Gmailu se řeší zvlášť a hned: nový zákaznický účet Wedos na IČO spolku, „Předání služeb novému majiteli", změna držitele domény u CZ.NIC a sdílený trezor hesel.

### Mapování na WordPress

| Entita | WordPress konstrukt | Pole | Poznámka |
|---|---|---|---|
| Admin 1 (vedení + marketing) | nativní role Administrator | Plná práva. POVINNĚ dva samostatné účty Administrator od prvního dne (např. predseda@ a marketing@), ne jeden sdílený login — jinak jen replikujeme problém jednoho Gmailu uvnitř WordPressu. | Účty na doménové adresy @budejovickymajales.cz, ne na osobní maily. E-mail už běží na Google Workspace (ověřeno: MX = aspmx.l.google.com), takže doménové schránky existují. |
| Admin 2 (dramaturgie) | vlastní role „Dramaturgie" vytvořená v pluginu Members | POVOLIT: read, upload_files, edit/publish/delete/edit_others/edit_published_tribe_events + _tribe_venue + _tribe_organizer, manage_categories pro taxonomie žánrů/kategorií/míst, capabilities CPT Králové (viz níže), správa účastníků Event Tickets. ZAKÁZAT: edit_pages, edit_posts (aktuality — pokud je nemá psát), manage_options, edit_theme_options, switch_themes, install_plugins, install_themes, edit_users, list_users, export, import. | Role se klonuje z Editora a ubere se. Když bude chtít vedení příští rok Adminu 2 přidat i Aktuality, je to zaškrtnutí políčka v Members, ne zásah do PHP. |
| CPT Králové (vlastní typ obsahu, definuje modul Králové) | ACF free → Post Types → Advanced → Rename Capabilities | capability_type nastavit na kral / kralove → vzniknou edit_kralove, publish_kralove, delete_kralove atd., které jde v Members přiřadit roli Dramaturgie nezávisle na stránkách a aktualitách. | OVĚŘENO: „Rename Capabilities" je ve FREE verzi ACF 6.1+, takže tady není potřeba žádný vlastní kód. Bez toho by CPT dědil capabilities od post a Admin 2 by se dostal i k aktualitám. |
| Hosting | Wedos NoLimit (stávající tarif, jen ověřit, který přesně mají) | PHP 8.4 (Wedos nabízí i 8.5 — jet na 8.4, na 8.5 ještě nemusí být všechny pluginy), MariaDB 10.11, neomezený SSD prostor, 2 GB na databáze, týdenní zálohy, Let's Encrypt SSL zdarma, .htaccess (Apache) funguje. | Databáze d66215_bm19pr na wm56.wedos.net = klasický sdílený Wedos. NoLimit WP (~205,70 Kč/měs s DPH při obnově) přidává LiteSpeed a Redis — pro web se 102 akcemi zbytečné, dvojnásobná cena bez měřitelného přínosu. |
| Vystoupení (102 akcí ze staré DB) | tribe_events (The Events Calendar) — cílový typ definuje modul Program | název → post_title, popis (WYSIWYG) → post_content, datum od–do → _EventStartDate/_EventEndDate, místo → _EventVenueID, obrázek → featured image. Navíc _bm_old_id = staré číselné ID z Nette. | _bm_old_id je klíčové: z něj se vygenerují přesměrování ze 102 starých URL /program/{id}. Bez něj se redirecty dělají ručně. |
| Místa (49) a Žánry/Kategorie | tribe_venue (49 záznamů) + taxonomie žánrů s nadřazenými kategoriemi | název, adresa; u žánrů term_meta s barvou. | Import přes ORM The Events Calendar (tribe_venues()->set_args()->create()), ne ručním zápisem do postmeta — jinak se rozbije práce s časovými zónami. |
| Králové (20), Partneři (52), Historie ročníků (21), Osvěžovny (24), FAQ (12) | CPT / opakovatelná ACF pole — definují příslušné moduly | Pro tenhle modul jen: všechno jde jedním importním skriptem ze stejného SQL dumpu, není důvod dělat víc běhů. | FAQ (12), statických 15 stránek a osvěžovny (24) je rychlejší přepsat rukou než skriptovat. Skriptovat se vyplatí jen u 102 akcí, 49 míst, 52 partnerů a 21 ročníků. |
| Rezervace (aktivní data ročníku 2026) | Event Tickets RSVP — struktura se vytvoří, data účastníků se NEmigrují | Migrují se jen definice rezervací (kapacita, okna) jako nastavení u nových akcí ročníku 2027. | ZÁMĚRNĚ nemigrujeme jména a e-maily 230 lidí z 2026 — jsou to osobní údaje bez účelu dalšího zpracování. Stará DB se po migraci zazálohuje offline a smaže ze serveru. |
| Nahrané soubory (/uploads/*.jpg, *_thumb.jpg) | WordPress knihovna médií | Stáhnout přes FTP celou složku /uploads, importovat lokálně přes wp media import --featured_image s napojením na _bm_old_id. | Ověřeno v HTML: obrázky mají hashová jména a variantu _thumb. WordPress si náhledy vygeneruje sám, importují se jen plné verze. |
| Staré URL | slugy nových stránek + plugin Redirection | /lineup, /kralove, /historie, /osvezovny, /dobrovolnici, /kontakty, /gdpr, /pro-media, /podpor-nas = nechat jako slugy nových stránek → NULA přesměrování. Přesměrovat jen: /aboutUs → /o-nas, /faqs → /faq, /partners → /partneri, /program → /program, /program/{id} → nový permalink (102 řádků z CSV). | Čím míň pravidel, tím líp se to předává. Redirection má CSV import, takže 102 řádků je jeden upload. |
| Doména budejovickymajales.cz | CZ.NIC, registrátor Wedos (REG-WEDOS) | Ověřeno whois: držitel = kontakt A24CONTACT-12721, expirace 17. 9. 2027, NS ns.wedos.cz/eu/com, A záznam 46.28.105.102 (Wedos). | Do expirace 2027 je čas, ale změnu držitele udělat hned — ne až těsně před obnovou. |

### Pluginy

| Plugin | Zdarma | Účel |
|---|---|---|
| Members (MemberPress/Melapress) | ano | Vytvoření role „Dramaturgie" pro Admina 2 a naklikání capabilities bez psaní PHP. Nástupce vidí práva jako zaškrtávací tabulku. |
| Duplicator (Lite) | ano | Nasazení lokálně postaveného webu na Wedos bez SSH: balíček + installer.php přes FTP. Po migraci zůstává jako záložní nástroj — nástupce udělá zálohu jedním tlačítkem. |
| Redirection | ano | Přesměrování ~105 starých URL na nové, hlavně /program/{id}. Nástupce je vidí v administraci, ne v .htaccess. |
| WP 2FA (Melapress) | ano | Dvoufaktor na oba účty Administrator. Bez toho je jediná pojistka heslo, které se předává mezi sestavami. |

### Co se musí naprogramovat (23 h)

- **Importní skript (PHP soubor spouštěný lokálně přes `wp eval-file import.php`) — čte transformovaný SQL dump a zakládá tribe_events, tribe_venue, termy žánrů a záznamy králů/partnerů/ročníků; ke každé akci zapíše post meta _bm_old_id.** — 14 h
  - Proč to nejde standardně: Neexistuje plugin, který by uměl číst schéma vlastní Nette aplikace. Pro 102 akcí + 49 míst + 52 partnerů + 21 ročníků se ruční přepis nevyplatí a udělá chyby v datech a časech. DŮLEŽITÉ: tenhle skript je JEDNORÁZOVÝ nástroj, nezůstává na produkci a nástupce ho nikdy nespustí — není to dluh na příští sestavu, po migraci se smaže. Musí ale volat ORM The Events Calendar (tribe_events()->set_args([...])->create()), ne zapisovat _EventStartDate ručně, jinak se rozbijí časové zóny a vícedenní akce.
- **Transformační SQL/skript nad exportem d66215_bm19pr: mapa staré ID → nová struktura, sloučení dvouúrovňových žánrů do hierarchické taxonomie, dohledání obrázků v /uploads podle hashe, vyházení modulů Kartičky a Počasí.** — 6 h
  - Proč to nejde standardně: Stará struktura (žánr → kategorie, místo s automatickým zakládáním) se do WordPressu nemapuje 1:1. Rovněž jednorázové, nezůstává.
- **Generátor CSV pro Redirection: SELECT nad novou WP databází, který z _bm_old_id vyrobí 102 řádků /program/{id} → /program/{slug}.** — 2 h
  - Proč to nejde standardně: 102 přesměrování ručně je půl dne a tři překlepy. Výstup je statické CSV, které se jednou naimportuje — v kódu nic nezůstává.
- **Volitelný mu-plugin (~15 řádků) skrývající roli Dramaturgie zbytečné položky v levém menu administrace (Nástroje, Vzhled), které WordPress schová jen částečně.** — 1 h
  - Proč to nejde standardně: Kosmetika kvůli přehlednosti pro Admina 2. Capabilities přístup už blokují, tohle jen uklidí obrazovku. Jestli po testu nebude potřeba, vynechat — každý řádek vlastního kódu je dluh.

### MVP — musí být do ročníku 2027

- [ ] Nový zákaznický účet Wedos založený na IČO spolku a převedené služby (hosting + doména) z účtu Lukáše H. — udělat jako PRVNÍ krok, ne poslední, protože část kroků čeká na potvrzení SMS a e-mailem
- [ ] Dva účty Administrator na doménové adresy @budejovickymajales.cz, oba s 2FA, žádný sdílený login
- [ ] Role „Dramaturgie" s otestovaným přístupem: přihlásit se pod ní a ověřit, že se dostane k programu, králům a rezervacím a NEdostane ke stránkám, vzhledu, pluginům a uživatelům
- [ ] Lokální vývojové prostředí (DDEV nebo Local) s WP-CLI, kde se postaví celý web včetně obsahu
- [ ] Naimportovaných 102 akcí, 49 míst, žánry s kategoriemi, 20 králů, 52 partnerů, 21 ročníků historie + všechna média z /uploads
- [ ] 15 statických stránek přepsaných rukou (rychlejší než skriptovat)
- [ ] Přesměrování funkční pro /aboutUs, /faqs, /partners, /program a všech 102 URL /program/{id}
- [ ] Nasazení na Wedos přes Duplicator + FTP, funkční HTTPS, staré Nette soubory a jeho .htaccess odstraněné
- [ ] Offline záloha staré databáze a starého www/ PŘED smazáním, uložená mimo hosting
- [ ] Sdílený trezor hesel (Bitwarden free, organizace pro 2 uživatele) s přihlašovacími údaji k Wedosu, WordPressu, doméně, Google Workspace a Shoptetu
- [ ] Dvoustránkový předávací list v administraci (stránka viditelná jen adminům): kde se co mění, co nikdy neměnit, koho volat

### Druhá vlna — může počkat

- Přesměrování starých URL obrázků /uploads/<hash>.jpg — obrázky se stejně mění každý ročník a neodkazuje na ně nic zvenčí
- Třetí role „Dobrovolník" jen pro psaní aktualit, pokud se ukáže, že obsah píše víc lidí
- Automatické zálohování mimo hosting (Duplicator Lite umí ruční zálohu, Wedos má týdenní; off-site záloha na Google Drive je až Pro verze — zatím stačí ruční stažení před a po ročníku)
- Staging subdoména pro zkoušení změn — pro sestavu, která WordPress nikdy neviděla, to je spíš past než pomoc; přidat, až o to někdo požádá
- Přesun e-mailu z Google Workspace jinam — teď funguje, nesahat na to během migrace webu
- Monitoring dostupnosti (UptimeRobot free) — hezké, ne nutné

### Rizika

- ZADÁNÍ POČÍTÁ S WP-CLI NA SERVERU — to na Wedosu nejde. Sdílený hosting nemá SSH, takže `wp import` se na produkci nespustí. Řešení je postavit web lokálně a nasadit balíčkem; kdo by to nevěděl předem, ztratí den a skončí u ručního přepisování v phpMyAdminu.
- Ruční import SQL přes phpMyAdmin místo Duplicatoru tiše rozbije serializovaná data (nastavení ACF, The Events Calendar, cesty k obrázkům). Projeví se to až za týden a vypadá to jako náhodné chyby.
- Osobní Gmail webmajales@gmail.com drží hosting, doménu i databázi. Pokud Lukáš H. ztratí přístup, přestane odpovídat nebo mu Google účet zablokuje, web je nevratně pryč — Wedos vrátí přístup jen přes ověření totožnosti majitele. Tohle je jediné riziko, které může skončit ztrátou celého webu, ne jen jeho nefunkčností.
- Doména vyprší 17. 9. 2027, tedy PO příštím ročníku a pravděpodobně už pod novou sestavou. Pokud upomínka půjde na Gmail, ke kterému nikdo nemá přístup, doména propadne. Upomínky musí chodit na doménovou adresu a do sdíleného kalendáře.
- Role Dramaturgie se snadno nastaví „skoro dobře" — nejčastěji unikne obrazovka účastníků v Event Tickets nebo správa taxonomií. Musí se otestovat skutečným přihlášením pod tím účtem, ne pohledem do tabulky capabilities.
- Migrace rezervačních dat ročníku 2026 by přenesla jména a e-maily ~230 lidí do nového systému bez důvodu. Nemigrovat, starou databázi po ověření smazat ze serveru a nechat jen offline zálohu.
- Duplicator Lite má limit 500 MB. Pokud /uploads za 20 let narostlo, balíček se nevytvoří a je potřeba média nahrát zvlášť přes FTP. Zjistit velikost složky HNED na začátku, ne v den nasazení.
- Starý Nette web má vlastní .htaccess s přepisovacími pravidly. Když se nesmaže, WordPress buď nefunguje, nebo fungují stará pravidla a přesměrování se chovají náhodně.
- Každý další plugin je položka, kterou musí příští sestava pochopit. Čtyři navržené jsou strop pro tenhle modul; pokud se objeví pokušení přidat pátý „na drobnost", napsat radši 15 řádků do mu-pluginu nebo to neřešit.
- Dvojí ceník Wedosu: první rok s kupónem ~47 Kč/měs s DPH, od druhého roku 121 Kč/měs s DPH. Rozpočet musí počítat s obnovovací cenou, jinak to druhá sestava schytá jako nečekaný výdaj.
- Ceny jsou z veřejných stránek a recenzí ze září 2026 — ověřit při objednávce, ne je brát jako závazné.

### Jak to vysvětlit tomu, kdo web převezme

> Řekni to takhle: „Web běží na WordPressu u Wedosu. Na Wedos se přihlašuješ zákaznickým účtem spolku — najdeš ho v našem trezoru hesel. Do samotného webu se dostaneš přes budejovickymajales.cz/wp-admin. Máme dvě úrovně přístupu: hlavní admin vidí všechno, dramaturgie vidí jen Program, Krále a Rezervace. Kdo má jakou roli, změníš v menu Members → Roles — je to tabulka zaškrtávátek, žádné programování. Když přijde nový člověk do dramaturgie, vytvoříš mu uživatele a dáš roli Dramaturgie, nic víc."
> 
> Tři věty, na kterých to celé stojí a které se nesmí ztratit:
> 1. NIC NEVISÍ NA OSOBNÍM GMAILU. Hosting, doména i Google Workspace jsou vedené na spolek (IČO), přístupy jsou v Bitwardenu, ověřovací SMS chodí na telefon statutárního orgánu. Když předáváš dál, předáváš trezor a měníš telefon v nastavení — neposíláš nikomu svoje heslo.
> 2. NEŽ NĚCO ZMĚNÍŠ, UDĚLEJ ZÁLOHU. Duplicator v levém menu, tlačítko Create Backup, stáhni si výsledný soubor k sobě. Trvá to dvě minuty a zachránilo to už každého.
> 3. STARÉ ODKAZY FUNGUJÍ DÍKY PLUGINU REDIRECTION. Když někde na Facebooku visí odkaz na /program/689, Redirection ho pošle na novou stránku. Do těch pravidel nesahej, jen občas mrkni do jeho záložky 404 — když se tam něco opakuje, přidej pravidlo.
> 
> Co NIKDY nedělat bez konzultace: neměnit URL (slug) u stránek, které už existují; nemazat plugin, kterému nerozumíš; nesahat na doménu a DNS u Wedosu; nepřepínat PHP na nejnovější verzi „aby to bylo aktuální".
> 
> A jedna věc, kterou je fér říct: migrace ze starého Nette webu byla jednorázová akce. Skripty, kterými se to převádělo, na serveru nejsou a nikdy je nebudeš potřebovat. Všechno, co dneska na webu je, se dá měnit klikáním v administraci.

---

