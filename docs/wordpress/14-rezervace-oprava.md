# Rezervace: oprava analýzy — web běží na RSVP v2 (Tickets Commerce)

Oprava k [12-rezervace-analyza.md](12-rezervace-analyza.md). Stav ověření **4. 11. 2026**.

Analýza rezervací popisuje technické řešení nad **starou větví RSVP** v Event Tickets.
Na tomhle webu ta větev vůbec neběží. Event Tickets 5.30.0 zavedl **RSVP v2 postavené
na Tickets Commerce** a `novy.budejovickymajales.cz` jede na něm.

Doplněk [`wordpress/mu-plugins/bm-rezervace.php`](../../wordpress/mu-plugins/bm-rezervace.php)
je psaný už proti nové větvi. Tenhle dokument vysvětluje, co se tím v analýze mění
a co platí dál.

> **Co je a není ověřené.** Doplněk se zatím nikam nenasazoval — blokuje ho etapa 0
> (SMTP a DNS) — takže **nic z toho neběželo**. Rozlišuj prosím dvě úrovně:
>
> **Změřeno:** že web jede na RSVP v2. Jeden čtecí dotaz na veřejné REST API webu,
> doložený níž. Na webu se při tom nic neměnilo.
>
> **Přečteno ve zdrojovém kódu Event Tickets 5.30.2, ale nevyzkoušeno:** že
> `Order::modify_status( $id, 'refunded' )` opravdu vrátí místo do kapacity a uklidí
> účastníky; že e-mail odejde, protože generování účastníků projde přes
> `Attendee::create()`; že výmaz osobních údajů nástrojem WordPressu sáhne i na nové
> účastníky; že zrušená objednávka nerozešle žádný e-mail o vrácení peněz. Tohle všechno
> je potřeba po nasazení projít ručně — scénáře jsou v tabulce v
> [mu-plugins/README.md](../../wordpress/mu-plugins/README.md).

---

## Co se změřilo

Jediný čtecí dotaz na veřejné REST API rozestavěného webu, 4. 11. 2026:

```
GET https://novy.budejovickymajales.cz/wp-json/tribe/tickets/v1/tickets
→ "total": 1
  "provider": "tc"
  "type": "tc-rsvp"
```

Pro srovnání, stará větev by u lístku hlásila `"provider": "rsvp"` a typ `rsvp`.
Verze pluginu na webu je **Event Tickets 5.30.2** (ověřeno staženým `readme.txt`).

Nezávisle to potvrzuje i meta akce: `_tribe_default_ticket_provider` je nastavené
na `TEC\Tickets\Commerce\Module`.

## Proč to tak je

Event Tickets 5.30.0 přidal třídu `TEC\Tickets\RSVP\Controller`, která si při prvním
načtení vybere, kterou implementaci RSVP vůbec zaregistruje — a volbu si uloží do
nastavení (`tickets_rsvp_version`). Rozhoduje se podle toho, **jestli na webu byly
staré rezervace**:

```php
// src/Tickets/RSVP/Controller.php, 5.30.2
return self::has_rsvp_v1_tickets() ? self::VERSION_1 : self::VERSION_2;
```

Na tomhle webu žádné staré rezervace nebyly — kostra se stavěla až na podzim 2026 —
takže se zaregistrovala v2. Volba je uložená a sama se nezmění.

**Prakticky to znamená, že tohle rozhodnutí už padlo a nejde ho vzít zpátky jinak než
ručním zásahem do nastavení.** Není to volba, kterou by web dělal při každém načtení.

---

## Nález 4 analýzy padá. Ale jinak, než to vypadá

Analýza v nálezu 4 píše, že vlastní kód může viset na dvou akcích
(`event_tickets_rsvp_tickets_generated` a `event_tickets_attendee_ticket_deleted`)
a že je ověřeně stabilní od verze 5.20.0, tedy devatenáct měsíců.

**Tohle je potřeba rozlišit přesně, protože to není totéž:**

| | |
|---|---|
| Přestaly ty hooky existovat? | **Ne.** `src/Tribe/RSVP.php` je v 5.30.2 pořád součástí pluginu a `do_action( 'event_tickets_rsvp_tickets_generated', … )` je v něm na řádku 1234, slovo od slova jako dřív. |
| Tak proč nefungují? | **Protože se třída, která je spouští, na tomhle webu nikdy nenačte.** `RSVP\Controller::do_register()` zaregistruje buď `V1\Controller`, nebo `V2\Controller`. Tady zaregistroval v2, takže kód staré větve leží v pluginu nedotčený a nic ho nespustí. |

To je důležité pro to, jak se taková věc příště ověřuje. **Analýza hledala hooky grepem
přes čtyři verze zdrojového kódu a našla je — správně.** Grep ale neumí odpovědět na
otázku, jestli se ta část pluginu na konkrétním webu vůbec zavede. Ta odpověď je
v datech webu, ne ve zdrojáku. Tady stačil jeden dotaz na REST API.

**Co z nálezu 4 platí dál:**

- „Do e-mailové šablony sahat nemusíme." — platí, ale jinak, než analýza mínila:
  odkaz **nejde vedle potvrzení, ale do něj**. Event Tickets má na obsah navíc vlastní
  filtr a vykresluje ho sám, takže se nekopíruje žádná jeho šablona. Dva e-maily naráz
  od stejného odesílatele o stejné akci by návštěvník stejně nerozlišil — za tři týdny
  by nevěděl, ve kterém z nich odkaz hledat. Změněno 5. 11. 2026 po připomínce zadavatele.
- „Tvrdé smazání účastníka nejde, protože má v sobě kontrolu oprávnění." — platí i v nové
  větvi, jen jinudy: mazání účastníka je v `TEC\Tickets\Commerce\Attendee::delete()`
  a vede přes repozitář, ke kterému se nepřihlášený návštěvník nedostane.
- Odhad křehkosti „2 hooky + 1 metoda" — počet sedí i v nové větvi, ale viz
  [riziko níž](#nové-riziko-rsvp-v2-je-tři-týdny-staré).

**Co z nálezu 4 neplatí:**

- Konkrétní jména obou akcí.
- Typ příspěvku `tribe_rsvp_attendees` a všechny meta klíče `_tribe_rsvp_*`.
- Metoda `update_stock_from_attendees_page()` a postup „nejdřív přepočet kapacity,
  pak přepis stavu".
- **Tvrzení o devatenáctiměsíční stabilitě.** Nepřenáší se — viz níž.

### Nález 4 pro etapu 2 je potřeba ověřit znovu

Druhá akce z nálezu 4, `event_tickets_attendee_ticket_deleted`, má v etapě 2 (čekací
fronta) spouštět notifikaci při uvolnění místa. **Jestli se spustí i pro účastníka
z Tickets Commerce, jsem neověřoval** — etapa 2 je za rozhodovací branou a mimo zadání.
Patří to do přípravy etapy 2, ne až do její implementace.

---

## Co v analýze platí dál

| Část analýzy | Platí? | Poznámka |
|---|---|---|
| **Nález 1** — zrušení návštěvníkem nejde koupit | **Ano, a nová větev to potvrzuje znovu.** | V2 má vlastní cestu pro úpravu rezervace (`RSVP\V2\Frontend::update_attendee_data`) a začíná stejnou podmínkou: `if ( ! is_user_logged_in() ) return;`. Požadavek na přihlášení se nezměnil. |
| **Nález 2** — fronta v placené verzi | Beze změny | Netýká se toho, co se zde staví. |
| **Nález 3** — ceník výrobce | Beze změny | |
| **Nález 5** — žádný hotový doplněk neexistuje | Beze změny | |
| **Doporučení: cesta C, vlastní mu-plugin** | **Ano** | Mění se vnitřek doplňku, ne rozhodnutí. |
| **Dvě etapy s rozhodovací branou** | **Ano** | |
| **Etapa 0 (SMTP a DNS) blokuje všechno** | **Ano** | Nic z toho nezměnila. |
| **Kapitola GDPR** | **Ano** | Včetně toho nejdůležitějšího — viz níž. |
| **„Odchod od The Events Calendar cestu C nemaří"** | **Ano** | Nová větev potřebuje aktivní Tickets Commerce, ale to je pořád součást Event Tickets, ne kalendáře. Platí i to, že odchod od **Event Tickets** by cestu C zrušil. |
| **Věta, že cesta C je 1 soubor, který jde smazat** | **Ano** | |

### GDPR: výmaz přes nástroj WordPressu funguje i v nové větvi

Analýza staví na tom, že žádost o výmaz vyřídí *Nástroje → Smazat osobní údaje*, protože
Event Tickets registruje vlastní eraser. **Platí to i po přechodu na v2**, i když jinudy,
než by se čekalo: `src/Tribe/Privacy.php` si účastníky nevybírá podle typu příspěvku,
ale přes kontejner (`tribe( 'tickets.attendee-repository.rsvp' )`) — a ten `V2\Controller`
přesměruje na repozitář Tickets Commerce. Eraser tedy sáhne na nové účastníky, aniž
by se musel měnit.

Ověřeno četbou, ne spuštěním. **Patří to do testovacích scénářů po nasazení**:
poslat si žádost o výmaz na vlastní e-mail a zkontrolovat, že rezervace zmizela.

---

## Jak se zrušení dělá v nové větvi

Stručně, podrobnosti jsou v komentářích doplňku a v
[mu-plugins/README.md](../../wordpress/mu-plugins/README.md).

| | Stará větev (analýza) | Nová větev (skutečnost) |
|---|---|---|
| Objednávka | jen hash v meta | příspěvek `tec_tc_order` |
| Účastník | `tribe_rsvp_attendees` | `tec_tc_attendee`, rodičem je objednávka |
| Meta | `_tribe_rsvp_*` | `_tec_tickets_commerce_*` |
| E-mail po rezervaci | `event_tickets_rsvp_tickets_generated` | filtr `tec_tickets_emails_rsvp_template_args` nad potvrzením |
| Zrušení | přepočet kapacity + přepis stavu u každého místa zvlášť | `Order::modify_status( $id, 'refunded' )` nad celou objednávkou |

### Proč `…_template_args` a ne `…_additional_content`

Nabízel by se filtr `tec_tickets_emails_rsvp_additional_content` — jméno sedí přesně.
Ve výchozím nastavení se ale **nespustí**: volba „Use ticket email settings" je
u RSVP zapnutá (`Emails/Email/RSVP.php`, `tribe_get_option( …'use-ticket-email', true )`)
a při ní si potvrzení bere obsah navíc od e-mailu *Ticket*, tedy přes filtr
`…_ticket_additional_content`.

`tec_tickets_emails_rsvp_template_args` běží až potom, co se obsah vybral z jedné nebo
druhé větve (`Emails/Email_Abstract.php`, `get_template_context()`), takže funguje
v obou případech. Ověřeno ve zdrojovém kódu Event Tickets 5.30.2.

Je to past stejného druhu jako celý nález 4: **jméno hooku vypadá správně a přesto
nikdy nedoběhne.** Grep by to neodhalil, odpověď je ve větvi nad nastavením.

**Jedna věc se tím zjednodušila.** Analýza varuje, že se token musí podepisovat nad číslem
objednávky, protože jinak odkaz ze tří rezervovaných míst uvolní jedno. V nové větvi je
objednávka samostatný příspěvek a zrušení se dělá nad ní, takže vícemístné rezervace
vycházejí správně samy od sebe. Ta past zmizela.

### Proč stav „refunded" u bezplatné rezervace

Tohle pořadatele v administraci překvapí, tak ať to má kde dohledat.

Tickets Commerce má třináct stavů objednávky. Prošel jsem je všechny a **kapacitu vracejí
jen dva** — jsou to jediné dva, které nesou příznak `increase_stock`:

| Stav | Jak se jmenuje v administraci | Vrací místo? | Konečný? | Vidět ve filtru stavů? |
|---|---|---|---|---|
| `refunded` | Refunded | **ano** | ano | ano |
| `not-completed` | Not Completed | **ano** | ne | **ne** |
| `voided` | **Cancelled** | **ne** | ano | ano |
| `denied` | Failed | ne | ano | ano |
| `reversed` | Reversed | ne | ano | ne |
| ostatních 8 | — | ne | — | — |

Past je v tom, že stav, který se jmenuje **„Cancelled", místo neuvolní**. Kdo doplněk
jednou „opraví" na něj, protože to čte líp, rozbije uvolňování míst a nic ho na to
neupozorní.

Z dvou použitelných je `refunded` lepší volba:

- Je to jeden ze dvou přechodů, které sám plugin nabízí ze stavu „Completed"
  (`Completed::can_be_updated_to()` vrací právě *Voided* a *Refunded*) — tedy cesta,
  se kterou výrobce počítá.
- Je **konečný**: zrušenou rezervaci nejde omylem převést zpátky.
- Je vidět ve filtru stavů v administraci. `not-completed` je schovaný
  (`show_in_admin_status_list => false`), takže by pořadatel zrušené rezervace nenašel.
- `not-completed` navíc významem říká „objednávka nikdy neproběhla", což zahazuje
  informaci, že tu někdo místo měl a vrátil ho. A právě tu informaci potřebujeme
  pro rozhodovací bránu před etapou 2.

**Co uvidí pořadatel.** Zrušená rezervace zůstane v seznamu objednávek se stavem
*Refunded* (v české lokalizaci pluginu podle překladu „Vráceno" nebo „Refundováno"),
bez jména a bez e-mailu. U akce zdarma to zní jako nesmysl, ale žádné peníze se nikam
nevracejí — plugin tím stavem jen říká „tahle objednávka už neplatí a místo je zpátky
volné". **Patří to do návodu pro tým** ([06-navod-pro-tym.md](06-navod-pro-tym.md)),
až se doplněk nasadí.

### Existuje i cesta, která končí stavem „Cancelled". Nepoužil jsem ji

Pro úplnost, protože se nabízí a někoho napadne ji dosadit: kdyby doplněk místo převodu
objednávky **mazal účastníky jednoho po druhém**, vyšlo by to líp na pohled. Event Tickets
při každém smazaném účastníkovi vrátí jedno místo a po smazání posledního převede
objednávku sám na `voided`, tedy na „Cancelled". Žádné „vrácení peněz" v administraci.

Nepoužil jsem ji ze tří důvodů:

1. **Chybí jí pojistka proti přetečení kapacity.** Převod na `refunded` počítá nový stav
   jako `min( původní + počet, kapacita )` — nikdy nevyrobí víc volných míst, než kolik
   jich akce má. Mazání po jednom přičítá bez tohohle stropu.
2. **Je to tři vedlejší účinky místo jednoho volání.** Převod stavu je jedna metoda,
   která si uvnitř drží zámek a transakci. Mazání po jednom spoléhá na řetěz tří hooků
   spuštěných mimo situaci, pro kterou je plugin psal (obrazovka v administraci).
3. Převod na `refunded` je jedna ze dvou cest, které sám plugin ze stavu „Completed"
   nabízí. Mazání po jednom je obchvat.

Shrnuto: hezčí slovo v administraci za slabší pojistku. Nestojí to za to — a ta věta
v návodu pro tým je levnější než vysvětlování, proč má akce najednou o tři volná místa víc.

### Známé omezení: pořadatel smaže část míst a návštěvník pak zruší zbytek

V nové větvi **mazání účastníka v administraci místo vrací** (přes
`Ticket::update_stock_after_attendee_deletion()`). To je dobře a znamená to, že dnešní
postup — doplněk smazat a rezervace ruší pořadatel — funguje i tady.

Má to ale jeden následek. Když pořadatel z třímístné rezervace smaže ručně jedno místo
(vrátí se jedno) a návštěvník pak klikne na odhlašovací odkaz, převod objednávky vrátí
**všechna tři** — tedy o jedno víc, než kolik jich v tu chvíli bylo obsazených. Akce
může skončit s jedním volným místem navíc.

Tři poznámky k tomu:

- **Není to chyba doplňku, je to chování Event Tickets.** Převod na `refunded` počítá
  s původním počtem míst v objednávce, ne s tím, kolik jich zbylo. Totéž by se stalo,
  kdyby objednávku takhle převedl sám pořadatel.
- **Je to shora omezené.** Volných míst nikdy nebude víc, než je kapacita akce.
- **Hlídat to v doplňku by stálo víc, než co by to ušetřilo.** Znamenalo by to porovnávat
  počet živých účastníků s objednávkou a při neshodě návštěvníkovi říct „napiš nám" —
  tedy vyrobit odmítnutí pro situaci, která u desítek rezervací ročně nastane sotva kdy.

Správné řešení je organizační a patří do návodu pro tým: **pořadatel ať z rezervace
nemaže jednotlivá místa, ať ji smaže celou.** Jednomístných rezervací je drtivá většina
a u nich tenhle případ vůbec nevzniká.

### Nesouměrnost, na kterou si dát pozor

Event Tickets při **odpočtu** kapacity přeskakuje odpovědi „Nepřijdu" (hidden order
jen pro evidenci, místo se nedrží), ale při **vracení** kapacity už nic nepřeskakuje.
Kdyby se takováhle objednávka zrušila, přibylo by na akci volné místo, které nikdo neměl.

Doplněk se proti tomu brání dvakrát: odhlašovací odkaz takové rezervaci vůbec neposílá
a před zrušením znovu počítá, kolik míst je opravdu obsazených. Kdo bude doplněk upravovat,
tuhle pojistku ať nemaže — v kódu je u funkce `bm_rezervace_pocet_mist()`.

---

## Nové riziko: RSVP v2 je tři týdny staré

Tohle je hlavní cena přechodu a je potřeba ji říct nahlas.

Analýza stavěla svoji důvěru v hooky na tom, že se **devatenáct měsíců nezměnily**
(od 5.20.0 z 6. 3. 2025 po 5.30.2 z 15. 10. 2026, přes několik velkých verzí).
To byl dobrý argument.

**Na novou větev se nepřenáší.** Všechno, na čem doplněk stojí, je označené `@since 5.30.0`
— tedy z října 2026. Tři týdny. U takhle čerstvého kusu pluginu je mnohem
pravděpodobnější, že se jména a chování ještě pohnou, než u kódu, který se neměnil
rok a půl.

Co to mění prakticky:

- **Otestovat doplněk po každé aktualizaci Event Tickets**, ne jen po velkých. Do konce
  jara 2027 to ber jako povinnost, ne jako doporučení.
- Selhání zůstává měkké: když Event Tickets něco přejmenuje, doplněk přestane posílat
  odkazy a rezervace fungují dál jako dnes. **Ztráta je pořád ohraničená** — to je ta
  vlastnost, kvůli které cesta C prošla, a ta se nezměnila.
- Všechno, čím se doplněk drží pluginu, je v jednom bloku konstant na začátku souboru,
  aby se to dalo opravit na jednom místě.

## Mění se odhad hodin?

Etapa 1 byla odhadnutá na **16–21 h**. Nová větev to posouvá takhle:

| Položka | Směr | Proč |
|---|---|---|
| Zrušení (krok 4, 4–5 h) | **−1 až −2 h** | Jedno volání nad objednávkou místo přepočtu kapacity u každého místa zvlášť. Vícemístné rezervace vycházejí samy. |
| Zmapování datového modelu | **+2 až +3 h** | Analýza mapovala jinou větev. Novou není kde opsat: výrobce k RSVP v2 nemá dokumentaci a na fóru k ní zatím nic není. |
| Testování (krok 5, 2–3 h) | **+2 až +3 h** | Testuje se proti části pluginu, kterou zatím skoro nikdo nepoužil, a přibyly scénáře (stav objednávky, nesouměrnost u „Nepřijdu", výmaz osobních údajů). |

**Nový odhad etapy 1: 19–25 h.** Samotného psaní je zhruba stejně, přibyl průzkum
a testování. Odhad na etapu 2 nechávám být — závisí na druhém hooku, který není ověřený.

---

## Co by tuhle opravu zneplatnilo

1. **Někdo by na webu ručně přepnul `tickets_rsvp_version` zpátky na v1.** Pak by platila
   původní analýza a doplněk by přestal fungovat. Není důvod to dělat.
2. **Event Tickets by RSVP v2 zrušil nebo přejmenoval.** Je mu tři týdny; sledovat
   changelog.
3. **Web by se stavěl znovu od nuly se zálohou starých rezervací.** Pak by si plugin
   při prvním načtení mohl vybrat v1 a rozhodovalo by se znovu.

## Zdroje

Všechno ověřeno 4. 11. 2026.

- Zdrojový kód Event Tickets 5.30.2 —
  <https://plugins.svn.wordpress.org/event-tickets/tags/5.30.2/>, zejména
  `src/Tickets/RSVP/Controller.php`, `src/Tickets/RSVP/V2/` (`Controller.php`,
  `Constants.php`, `Attendees.php`, `Frontend.php`, `REST/Order_Endpoint.php`),
  `src/Tickets/Commerce/` (`Order.php`, `Attendee.php`, `Status/*`, `Flag_Actions/*`),
  `src/Tribe/Privacy.php` a `src/Tribe/RSVP.php`
- Stav webu — `GET https://novy.budejovickymajales.cz/wp-json/tribe/tickets/v1/tickets`
  a `readme.txt` staženého pluginu. **Jen čtení, na webu se nic neměnilo.**
