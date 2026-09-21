# Otázky na schůzku

Seřazeno podle toho, jak moc odpověď mění rozsah práce.
Odškrtávej přímo tady — je to zápis ze schůzky.

## Nejdřív tyhle tři — mění rozpočet i harmonogram

- [ ] **Zrušení rezervace: kdo ji ruší?** Návštěvník sám odkazem v e-mailu, nebo stačí, že ji zruší pořadatel v seznamu? Pořadatel = zdarma. Návštěvník = ~15 h křehkého vlastního kódu nad interním API pluginu. **Rozdíl je 15 hodin.**
- [ ] **Převod účtů na spolek.** Hosting Wedos, doména i databáze visí na osobním Gmailu `webmajales@gmail.com` (Lukáš H.). Má spolek IČO, bankovní účet a statutára, na jehož telefon půjdou ověřovací SMS? **Tohle je krok číslo jedna, ne poslední.**
- [ ] **Poznámky u rezervací.** Kolik akcí v 2026 mělo reálně zapnuté „Povolit psaní poznámek" a četl je někdo? Pokud nikdo, pole se nedělá a **ušetří se 12 hodin**.

## Podle modulů

### Program, lineup, žánry, místa

- [ ] Barva u žánru: v datech je 159 žánrů, z toho 99 jen pod Hudbou. Vybarvit je ručně nikdo nikdy nezvládne a v lineupu by vznikl zmatek. Navrhuji vyplňovat barvu u 9 kategorií a žánry ji dědí; technicky bude barva nastavitelná i u žánru pro výjimky. Souhlasí s tím dramaturgie?
- [ ] Souhlasíte se sloučením duplicitních žánrů při migraci (punk/rock 98+100, disco 116+117, Workshop ve čtyřech kategoriích, Přednáška 78+88, Rozhovor 71+135, Soutěž 45+146, Představení 86+90)? A co se šesti událostmi bez kategorie — zařadit je do nové kategorie „Ostatní", nebo je nechat nezařazené?
- [ ] Zadání mluví o 6 kategoriích, v datech ročníku 2026 jich je 9 (navíc Vnitřní scéna, Volba krále, Online). Které z nich jsou ještě živé a které jsou pozůstatek covidových ročníků a mohou se smazat?
- [ ] Bezbariérovost: navrhuji vyplňovat u místa (49 položek, vyplní se jednou a platí napořád) místo u každého vystoupení (102× každý rok). Přijatelné? U výjimek půjde na události nastavit ručně.
- [ ] Pole „Země původu" se dnes nikde na webu nezobrazuje — na kartičce ani v lineupu. Používá se někde jinde (tiskoviny, sociální sítě), nebo ho můžeme zrušit?
- [ ] Vystoupení 773 (MORNIN9 run) má v datech dvě místa — Zlatý most a Jihočeské divadlo. Ve free verzi TEC má událost jen jedno místo. Navrhuji ponechat startovní místo a druhé zmínit v popisu. Jsou takových běhů a průvodů víc, nebo je to výjimka?
- [ ] Migrujeme jen ročník 2026, nebo i starší (v databázi jsou záznamy do ID 782)? Historie 2004–2024 má na webu samostatnou stránku — stačí ta?
- [ ] Kolik dní před akcí se má v mřížce zobrazovat odznak „Nutná rezervace"? Dnes ho má 17 ze 102 vystoupení a bere se z rezervačního modulu.

### Králové a body ze soutěží

- [ ] Kolik segmentů a jaké přesně? Jsou dané na začátku ročníku, nebo během týdne přibývají? (rozhoduje, jestli pevný Select stačí)
- [ ] Má se rozpad bodů po segmentech ukazovat veřejně, nebo je to jen pomůcka pro dramaturgii a ven jde jen součet?
- [ ] Mají segmenty různou váhu (např. královská hra × 2)? Doporučení: váhy neřešit kódem, admin zapíše rovnou přepočtené body.
- [ ] Obsahuje dnešní DB tabulka králů i minulé ročníky, nebo se každý rok maže? (rozhoduje, jestli je co migrovat do taxonomie Ročník)
- [ ] Vítěze určuje nejvyšší součet, nebo ho vyhlašuje porota? Má web po festivalu ukázat „vítěz ročníku“?
- [ ] Platí, že jedna škola = jeden kandidát? (dnešní data i kotvy to předpokládají)
- [ ] Kdo body zadává — jen Admin 2 (dramaturgie), nebo do toho sahá i vedení?
- [ ] Jak často se body během festivalového týdne aktualizují? (kvůli nastavení cache na sdíleném Wedosu, aby se změna projevila hned)
- [ ] Máme písemné souhlasy se zveřejněním jména, školy a fotografie kandidátů? Část z nich jsou nezletilí.
- [ ] Odkazuje někdo zvenčí na kotvy /kralove#skola (weby a Instagramy škol)? Zachováme je tak jako tak, ale je dobré to vědět.

### Rezervační systém

- [ ] Co přesně znamená 'přidat možnost zrušení rezervace' — zruší si ji návštěvník sám odkazem v e-mailu, nebo to má umět jen pořadatel v seznamu? Cena se liší o zhruba 15 hodin.
- [ ] Kolik akcí v ročníku 2026 mělo reálně zapnuté 'Povolit psaní poznámek' a četl někdo ty poznámky? Pokud nula, pole neděláme a ušetříme 12 hodin.
- [ ] Měnil někdy někdo 'Popisek pole pro počet míst' u konkrétní akce, nebo všude stačilo 'Počet míst'? Ve WordPressu to půjde změnit jen globálně.
- [ ] Mají se rezervace z 2026 migrovat, nebo je to mrtvá data a začíná se s prázdnou tabulkou? Doporučuji nemigrovat — přenáší se jen vzorec nastavení, ne řádky.
- [ ] Rezervuje jeden člověk více míst (např. 3 pro kamarády), nebo platí 1 rezervace = 1 člověk? Ovlivňuje to, jestli se v reálu naplní kapacita správně.
- [ ] Potřebujete u rezervace telefon, nebo stačí jméno a e-mail? Telefon = vlastní pole = placená funkce nebo vlastní kód.
- [ ] Kdo je odpovědný za osobní údaje účastníků, jak dlouho je držíme a kdo je po festivalu smaže?
- [ ] Má dramaturgie (Admin 2) vidět e-maily účastníků, nebo jí stačí počty? Méně přístupu = méně starostí s GDPR.
- [ ] Chcete čekací listinu u naplněných akcí, nebo se to řeší tím, že kdo nedorazí, uvolní místo na místě?
- [ ] Potvrzuje dnes někdo rezervace ručně, nebo se potvrzují automaticky? Ve WordPressu bude výchozí chování automatické potvrzení.

### Aktuality, homepage a nastavení ročníku

- [ ] Kdy přesně se ročník přepíná? Hned po skončení festivalu (a úvod pak celý rok ukazuje jen volný text), nebo až je program dalšího ročníku hotový? Podle odpovědi se liší, co má úvodní stránka dělat 11 měsíců v roce.
- [ ] Padne v modulu Program volba na The Events Calendar? Pokud ano, TEC má vlastní vrstvu dotazů nad akcemi a vlastní příznak „Featured" — skrývání podle ročníku se proti němu musí otestovat a „Top představení" se možná řeší jeho příznakem místo naší taxonomie.
- [ ] Mají se ročníky navázat i na Krále a na Partnery? Oboje se mění rok od roku a stejný mechanismus by fungoval — je to pár hodin navíc, ale musí se rozhodnout teď, ne až potom.
- [ ] Kolik aktualit má být na úvodu (3 nebo 4) a má být náhledový obrázek povinný? Bez obrázku bude mřížka vypadat rozbitě.
- [ ] Má Admin 2 (dramaturgie) vůbec psát aktuality, nebo je to jen věc marketingu? Ovlivní to nastavení role.
- [ ] Mají se archivy minulých ročníků objevit ve vyhledávačích, nebo mají být jen dohledatelné z Historie pro toho, kdo hledá?
- [ ] Odkud se bere „majálesový font" — je licenčně v pořádku nahrát ho do WordPressu, nebo se musí koupit/nahradit?
- [ ] Migrují se nějaké staré aktuality? Modul v BM Adminu je prázdný, takže předpokládáme, že ne — potvrdit.

### Partneři, osvěžovny, fotogalerie, historie

- [ ] Zadání mluví o 52 logách ve 3 úrovních, ale na /partners je 46 log v 8 sekcích a na homepage žádné. Kde je zbylých 6 log a má se 8 sekcí opravdu sloučit na 3?
- [ ] Ke kterým ročníkům patří 47 fotek? Na /historie/2024 jich je 21, zbylých 26 je rozděleno jak?
- [ ] Soubory fotek se jmenují 2025_1.jpg až 2025_20.jpg, ale jsou na stránce ročníku 2024. Jaký je v databázi vztah soubor ↔ ročník? Bez toho nelze migraci zautomatizovat.
- [ ] Všechny popisky fotek jsou placeholder "Fotil" bez jména. Chce zadavatel popisky a jména fotografů vůbec vést?
- [ ] Má být Fotogalerie v novém menu jen rozcestník na ročníky (doporučuji), nebo samostatná kurátorovaná sbírka "nejlepší fotky"?
- [ ] Kolik má aktuální tarif na Wedosu diskového prostoru a jaké jsou v PHP limity upload_max_filesize a post_max_size? Určuje to, jestli půjde nahrát fotka z foťáku přímo.
- [ ] Ceny v osvěžovnách — mění se během festivalu, nebo se zadají jednou ročně před startem?
- [ ] Existují loga partnerů v originálním rozlišení, nebo jsou jen ty zmenšené z webu? U některých je jpg s bílým pozadím, což na barevném pozadí vypadá špatně.
- [ ] Má zůstat odkaz Podpoř nás na Donio jako vložený iframe, nebo stačí velké tlačítko? Iframe má natvrdo výšku 4000 px a na mobilu je rozbitý.

### Navigace, statické stránky a editovatelný vzhled

- [ ] Kdo koupil licenci na GT Walsheim a je to WEBFONT licence (Grilli Type licencuje podle počtu zobrazení stránek), nebo jen desktopová od grafika? Existuje faktura nebo PDF licence, které se dá uložit do médií pro příští sestavu?
- [ ] Souhlasíte s náhradou GT Walsheim za OFL font z Google Fonts (vizuálně blízké jsou Poppins, Outfit, Figtree)? Vyměníte tím licenční riziko za možnost měnit si font sami z rozbalovátka.
- [ ] Jsou texty 15 stávajících stránek uložené v databázi, nebo napsané přímo v Nette šablonách? Ovlivňuje to 10–16 hodin přenosu obsahu.
- [ ] Jsou staré anglické adresy (/aboutUs, /faqs, /program/ucinkujici) na tištěných plakátech, v placených kampaních nebo v odkazech partnerů? Kolik let mají 301 přesměrování fungovat?
- [ ] Je 'správa log partnerů a jejich sekcí' (52 log ve 3 úrovních) součástí tohoto modulu, nebo samostatného? Ať se nepočítá dvakrát.
- [ ] Kdo konkrétně dodá texty pro 4 nové stránky (Pro návštěvníky, Přístupnost, Doprava) a do kdy? Bez nich je modul hotový jen z 80 % a je to nejčastější důvod skluzu.
- [ ] Kam patří odkaz na Shoptet — v zadání je pod Partneři, což je pro návštěvníka nečekané místo. Nemá být E-shop samostatně v hlavní úrovni menu?
- [ ] Jaká verze PHP a MySQL je na aktuálním tarifu Wedos a je tam k dispozici SSH/WP-CLI?
- [ ] Šest vedlejších brandových barev odpovídá kategoriím programu — potvrdíte, že se mají vyjet jako barvy žánrů/kategorií (modul Program), a ne jako barvy webu?

### Role, účty, hosting a migrace dat

- [ ] Na kterém tarifu Wedosu web dnes běží? (databáze na wm56.wedos.net a IP 46.28.105.102 potvrzují sdílený Wedos, ale ne konkrétní tarif.) Kdy služba vyprší a kdo platí fakturu?
- [ ] SSH na sdíleném hostingu Wedos: marketingová tabulka na vedos.hosting/webhosting ho v přehledu funkcí uvádí, ale odpovědi podpory na help.wedos.cz roky tvrdí, že SSH je jen na VPS. Zeptat se podpory přímo. Návrh na SSH ZÁMĚRNĚ nespoléhá — funguje tak i tak — ale kdyby SSH bylo, zjednoduší to budoucí údržbu.
- [ ] Kdo je fyzicky držitel domény pod kontaktem A24CONTACT-12721 u CZ.NIC — Lukáš H. jako osoba, nebo už spolek? Tohle rozhoduje, jestli stačí přesun mezi účty Wedos, nebo je potřeba i změna držitele u CZ.NIC.
- [ ] Kdo je superadmin Google Workspace na doméně budejovickymajales.cz (MX míří na Google)? To je druhý jednobodový selhávací uzel vedle Wedosu a předává se stejně.
- [ ] Má spolek IČO a bankovní účet, na který lze služby Wedos převést a fakturovat? Kdo je statutární orgán, na jehož telefon půjdou ověřovací SMS?
- [ ] Existuje přístup do Shoptetu (shop.budejovickymajales.cz) a visí i ten na stejném Gmailu? Není součástí přechodu, ale do trezoru hesel patří.
- [ ] Kdy přesně může být ostrý přechod? Doporučení: podzim/zima 2026, aby byl web hotový dřív, než se v březnu 2027 začne zadávat program. Během festivalu se nemigruje.
- [ ] Jaké je přesné schéma staré databáze? Bez `SHOW CREATE TABLE` pro tabulky vystoupení, míst, žánrů, králů a partnerů je odhad importního skriptu ± 5 hodin.
- [ ] Má Admin 2 psát i aktuality? Zadání říká „pouze program, králové, rezervace", ale dramaturgie často chce oznámit změnu v programu. Pokud ano, přidá se capability edit_posts.

