# Must-use doplňky

Jediný vlastní PHP kód v celém projektu. Pravidlo z [AGENTS.md](../../AGENTS.md) zní
„žádný vlastní kód nad rámec nutného" — tohle je ta výjimka a má svůj důvod.

Soubory se nahrávají do `wp-content/mu-plugins/` na hostingu. Must-use znamená, že se
načtou vždycky: nejdou vypnout v administraci a přežijí změnu šablony.

## bm-rubriky.php — dědičnost rubrik

**Problém.** Barva akce v programu se bere z kategorie (*Hudba*), ne ze žánru (*disco*).
WordPress ale při zaškrtnutí potomka rodiče nezaškrtne. Akce tím snadno skončí bez
kategorie a v programu je bezbarvá — a nikdo si toho nevšimne, dokud se nekouká na výpis.
Ověřeno v praxi: stalo se to hned u první akce zadané ručně, i když návod na ten krok
výslovně upozorňuje.

**Řešení.** Patnáct řádků, které ke každému zaškrtnutému žánru doplní jeho nadřazené
kategorie. Nic nemaže, jen doplňuje; když je všechno na místě, do databáze nesáhne.

**Proč to nerozbije aktualizace.** Používá jen jádrové funkce WordPressu
(`wp_get_object_terms`, `get_ancestors`, `wp_set_object_terms`) a žádné vnitřnosti
The Events Calendar. Visí na `set_object_terms`, ne na `save_post` — REST API ukládá
rubriky až po `save_post`, takže na `save_post` by doplněk fungoval jen ve formuláři
administrace a ne při zadávání přes API.

**Jak ověřit, že žije.** Založ akci jen se žánrem, ulož a podívej se na seznam rubrik:
kategorie nad žánrem tam musí přibýt sama.

## bm-termin.php — termín ročníku

**Problém.** Termín festivalu byl na úvodní stránce napsaný natvrdo. Když tým v Ročnících
změnil datum, úvodní stránka dál hlásala loňský termín — a nikdo si toho nevšiml. Návod
přitom slibuje, že se pole „Text na homepage" na úvod propisuje. Ověřeno v praxi: zadavatel
změnil termín na 9.–14. 5. 2027 a úvod dál ukazoval 30. 5. – 5. 6.

**Řešení.** Zkratka `[bm_termin]` vypíše termín aktivního ročníku. Aktivní je ten s nejvyšším
názvem — názvy jsou roky a návod zakazuje zakládat dva se stejným rokem. Přednost má ruční
„Text na homepage"; když je prázdný, termín se poskládá z Datum od a Datum do.

**Proč to nerozbije aktualizace.** Čte term meta přímo přes jádro WordPressu
(`get_term_meta`), ne přes funkce Secure Custom Fields. Kdyby se plugin s poli jednou
vyměnil, zkratka funguje dál.

**Kde se používá.** Dvakrát na úvodní stránce — nadlinka nad nadpisem a běžící pás.

**Jak ověřit, že žije.** Změň termín v Akce → Ročníky a načti úvodní stránku.

## bm-program/ — program a lineup

**Problém.** Zkratka `[tribe_events]` je jen v placené verzi The Events Calendar a nativní
pohledy neumí časovou osu patnácti scén vedle sebe. Program je přitom celoročně
nejnavštěvovanější stránka webu, takže na ni nestačí odkaz do kalendáře.

**Řešení.** Zkratka `[bm_program]` vykreslí program ročníku. Jako jediný doplněk má vlastní
složku, protože vedle PHP potřebuje ještě dva skripty a styl. Celá logika je v JavaScriptu
a čte veřejné REST endpointy; PHP jen zaregistruje zkratku, zařadí soubory a vypíše
záchranný blok s odkazy do kalendáře.

**Proč je vedle složky ještě `bm-program-nacti.php`.** WordPress načítá z `mu-plugins/` jen
soubory `.php` ležící přímo v ní — do podsložek se nedívá. Zavaděč má podmínku
`file_exists`, aby smazání složky neshodilo celý web.

**Proč to nerozbije aktualizace.** Nevolá jedinou PHP funkci The Events Calendar, nesahá na
šablony pluginu a nezapisuje do databáze. Jediný styk s kalendářem jsou veřejné REST
endpointy.

**Jak ověřit, že žije.** Načti `/program/` — nesmí být vidět záchranný blok s odkazy do
kalendáře. Podrobnosti a pasti REST API jsou v [bm-program/README.md](bm-program/README.md).

## bm-rezervace.php — zrušení rezervace návštěvníkem

> ### ⛔ Tenhle soubor se zatím nenahrává na web
>
> Blokuje ho **etapa 0 z [analýzy rezervací](../../docs/wordpress/12-rezervace-analyza.md)**:
> doména nemá pro Wedos platný SPF ani DKIM, takže e-mail odeslaný z webu nemusí dojít.
> Celý doplněk stojí na tom, že návštěvníkovi přijde odkaz. Odkaz, který nedorazí, je
> horší než žádný — člověk čeká na zprávu, která nepřijde, a místo stejně propadne.
>
> Nahrát se smí teprve tehdy, až je hotové tohle:
> SMTP přes Google Workspace (FluentSMTP), zapnuté DKIM, záznam `_dmarc` a zkušební
> e-mail doručený do schránky na Gmailu, Seznamu i Outlooku.
>
> Doplněk sám nic neodesílá přímo — používá `wp_mail()`. Jakmile SMTP na webu je,
> začne chodit správnou cestou a v tomhle souboru se nemění ani řádek.

**Problém.** Návštěvník si zamluví místo a pak mu to nevyjde. Dnes rezervaci zrušit
nemůže — musí napsat pořadateli, a většinou nenapíše. Místo propadne a akce je „plná"
s prázdnými židlemi. Koupit se to nedá: samoobslužná úprava rezervace v Event Tickets
vyžaduje i v placené verzi uživatelský účet a přihlášení, tedy přesně tu registraci,
kterou festival nechce.

**Řešení.** V potvrzovacím e-mailu přijde odkaz. Odkaz otevře stránku s otázkou „opravdu
chceš zrušit rezervaci na akci X?" a teprve tlačítko rezervaci zruší, uvolní místo zpátky
do kapacity a smaže jméno a e-mail.

### Proč podepsaný odkaz, a ne `wp_nonce`

Nonce ve WordPressu platí nejvýš 24 hodin a váže se na session přihlášeného uživatele.
Odkaz v e-mailu musí fungovat měsíce, u nepřihlášeného člověka, a rezervace se ruší
klidně den před akcí. Nonce se na to nehodí.

Odkaz proto nese podpis `hash_hmac` nad `wp_salt()` — nad tajemstvím, které je
ve `wp-config.php` na hostingu. Kdo ho nezná, podpis nevyrobí, a porovnává se přes
`hash_equals`, takže se nedá uhodnout po znacích. Žádná databáze navíc, žádná expirace
k hlídání. Kdyby někdo soli na hostingu vyměnil, přestanou platit všechny staré odkazy
najednou — což je bezpečné selhání, ne díra.

**V adrese není jméno ani e-mail**, jen číslo objednávky a podpis. Adresa putuje e-mailem,
zůstává v historii prohlížeče a objevuje se v logu serveru.

### Proč se neruší hned po kliknutí

Antivirové brány, náhledy odkazů v Gmailu a firemní skenery pošty odkazy v e-mailech
předem načítají. Kdyby stačil jeden GET požadavek, **rezervace by se rušily samy od sebe**,
aniž by na ně kdokoli klikl. Odkaz proto jen zobrazí stránku; ruší se až odesláním
formuláře. Je to zároveň pojistka proti překlepu a proti tomu, aby odkaz zafungoval
z historie prohlížeče.

### Co se stane, když se soubor smaže

Rezervace fungují dál přesně jako dnes, jen se přestanou posílat odhlašovací odkazy
a ruší je zase pořadatel v administraci. Odkazy v už odeslaných e-mailech přestanou
fungovat — otevře se běžná úvodní stránka. **Nic se nerozbije.** Doplněk nic nenahrazuje,
jen přidává vrstvu navíc; když zmizí, zmizí ta vrstva a nic víc.

### Čeho z Event Tickets se drží

Tři místa, všechna označená v kódu komentářem `EVENT TICKETS` (dají se najít grepem):

| # | Co | Jak |
|---|---|---|
| 1 | Vznikla rezervace → pošli e-mail | akce `tec_tickets_commerce_attendee_after_create` |
| 2 | Kde jsou data | typy příspěvků `tec_tc_order` a `tec_tc_attendee`, meta `_tec_tickets_commerce_*` — všechny v bloku konstant na začátku souboru |
| 3 | Zrušení a vrácení kapacity | `TEC\Tickets\Commerce\Order::modify_status( $id, 'refunded' )` |

Kdyby Event Tickets kterékoli z nich přejmenoval, doplněk mlčky přestane fungovat —
nerozbije web. Opravuje se to v bloku konstant na začátku souboru.

**Pozor na stav `refunded`.** U bezplatné rezervace to zní divně a v administraci se
ukáže jako „Refunded". Je to ale jediný stav, na který Event Tickets dovolí přejít
z „completed" a který zároveň **vrací místo do kapacity**. Kdo to „opraví" na `voided`
(v administraci „Cancelled"), rozbije tím uvolňování míst, aniž by si toho kdokoli
všiml. Stejně tak nestačí přepsat účastníkovi stav na „nepřijdu" — plugin to tak dělá
u přihlášených uživatelů a kapacitou to nehne.

### Analýza popisuje jinou verzi, než na webu běží

[12-rezervace-analyza.md](../../docs/wordpress/12-rezervace-analyza.md) v nálezu 4 uvádí
hooky `event_tickets_rsvp_tickets_generated` a typ příspěvku `tribe_rsvp_attendees`.
**To je stará větev RSVP a na tomhle webu se vůbec nenačítá.** Event Tickets 5.30.0
(říjen 2026) zavedl RSVP v2 postavené na Tickets Commerce a web na něm jede: dotaz na
`/wp-json/tribe/tickets/v1/tickets` vrací u jediného rezervačního lístku `"provider":"tc"`
a `"type":"tc-rsvp"` (ověřeno 4. 11. 2026). Event Tickets si verzi vybírá podle toho,
jestli na webu byly staré rezervace — a tady žádné nebyly, takže nasadil novou.

Doplněk je proto psaný proti nové větvi. Zbytek analýzy (doporučení, GDPR, e-maily,
rozhodovací brána před čekací frontou) platí beze změny.

Celé to rozebírá [14-rezervace-oprava.md](../../docs/wordpress/14-rezervace-oprava.md):
co z analýzy padá, co platí dál, proč je zrušená rezervace v administraci vidět jako
„Refunded" a proč se doplněk musí testovat po každé aktualizaci Event Tickets.

### Co se stane s osobními údaji

Jméno a e-mail se při zrušení **smažou**, ne jen označí. Záznamy účastníků zmizí úplně
a na objednávce se jméno i adresa přepíšou. Zůstane jen datum zrušení v meta
`_bm_zruseno_navstevnikem` — žádný osobní údaj v něm není.

Zůstává schválně: z počtu těchhle značek se po festivalu spočítá, **kolik lidí rezervaci
opravdu zrušilo**. To číslo rozhoduje o tom, jestli má smysl stavět čekací frontu
(etapa 2 analýzy). Bez něj by se stavěla naslepo.

Do logu serveru se zapisuje jen při neodeslaném e-mailu, a jen číslo objednávky.

### Jak ověřit, že žije

1. Založ testovací rezervaci na libovolnou akci na vlastní e-mail.
2. Musí přijít **dva** e-maily: potvrzení od pluginu a náš s předmětem
   „Rezervace na … — odkaz pro zrušení".
3. Otevři odkaz z druhého e-mailu. Musí se ukázat stránka s otázkou a tlačítkem,
   **a rezervace musí pořád platit**.
4. Klikni na tlačítko. Stránka potvrdí zrušení.
5. V administraci v Lístky → Účastníci u té akce už rezervace není a volných míst
   je o jedno víc.

Patří to do checklistu před festivalem. Zkušební rezervace se posílá na Gmail **i na
Seznam** — chovají se jinak.

### Testovací scénáře po nasazení

Projít všechny, včetně těch zlých. Nic z toho se bez nasazení ověřit nedá.

| # | Co udělat | Co se musí stát |
|---|---|---|
| 1 | Rezervace na jedno místo, zrušit odkazem | Místo se vrátí do kapacity, účastník zmizí ze seznamu |
| 2 | Rezervace na tři místa, zrušit odkazem | Vrátí se **všechna tři**, ne jedno |
| 3 | Kliknout na odkaz podruhé | „Tahle rezervace je už zrušená", nic se nestane, kapacita se nezvýší znovu |
| 4 | Odeslat formulář dvakrát rychle za sebou (dvě záložky) | Totéž co 3 — jedno zrušení, ne dvě |
| 5 | Změnit v odkazu jeden znak podpisu | „Tenhle odkaz nefunguje", rezervace zůstane |
| 6 | Smazat z odkazu podpis úplně | Totéž co 5 |
| 7 | Dosadit do odkazu cizí číslo objednávky s původním podpisem | Totéž co 5 |
| 8 | Pořadatel smaže rezervaci v administraci, pak návštěvník klikne na odkaz | „Rezervaci jsme nenašli" |
| 9 | Nechat potvrzovací stránku otevřenou přes noc a pak odeslat | Stránka se zeptá znovu, po druhém potvrzení zruší |
| 10 | Odpovědět v rezervačním formuláři „Nepřijdu" | Náš e-mail **nepřijde** (není co rušit) |
| 11 | Zrušit a podívat se do administrace na objednávku | Jméno i e-mail přepsané, stav „Refunded", datum v `_bm_zruseno_navstevnikem` |
| 12 | Stránka na mobilu | Čitelná, tlačítko jde zmáčknout, barvy sedí se zbytkem webu |
| 13 | Aktualizovat Event Tickets na novější verzi a projít scénáře 1–3 znovu | Beze změny; jinak viz tabulka výš |
| 14 | Kdyby web někdy začal prodávat placené lístky: zkusit odkaz na placenou objednávku | „Tenhle odkaz tu nic nezruší" |
| 15 | Po zrušení zkontrolovat schránku | Od pluginu **nepřijde** žádný e-mail o vrácení peněz |
| 16 | Nástroje → Smazat osobní údaje, zadat e-mail z testovací rezervace | Rezervace se najde a smaže |
| 17 | Třímístná rezervace, pořadatel ručně smaže jedno místo, pak návštěvník klikne na odkaz | Rezervace se zruší, ale **volných míst přibude o jedno víc, než má** — známé omezení, viz [14-rezervace-oprava.md](../../docs/wordpress/14-rezervace-oprava.md) |

Scénář 13 je ten, na kterém se pozná tiché selhání. Patří do checklistu po každé velké
aktualizaci Event Tickets, protože se nijak neohlásí — odkazy prostě přestanou chodit.

Scénář 17 není chyba doplňku, ale chování Event Tickets, a dopadne stejně, i když
objednávku zruší sám pořadatel. Řeší se větou v návodu pro tým: **z rezervace se nemažou
jednotlivá místa, maže se celá.**
