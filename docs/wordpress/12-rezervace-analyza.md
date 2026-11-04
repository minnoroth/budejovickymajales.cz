# Rezervace: zrušení návštěvníkem a čekací fronta

Analýza pro ročník 2027 (festival 30. 5. – 5. 6. 2027). Stav ověření **4. 11. 2026**.

Zadání: dnešní rezervace jsou RSVP lístky pluginu Event Tickets — návštěvník zadá jméno
a e-mail, dostane potvrzení, a tím to končí. Chybí dvě věci: **zrušení rezervace
návštěvníkem** (odkaz v potvrzovacím e-mailu) a **čekací fronta** s notifikací při
uvolnění místa.

Rozsah provozu: v ročníku 2026 mělo rezervaci 17 ze 102 vystoupení, kapacity v desítkách míst.

> Tenhle dokument je novější než [00-analyza.md](00-analyza.md) a [03-overena-fakta.md](03-overena-fakta.md)
> a na třech místech je opravuje — ceník výrobce, existenci fronty v placené verzi a odhad
> hodin na vlastní kód. Kde se rozcházejí, platí tenhle dokument.

> **Opraveno později:** web běží na RSVP v2 nad Tickets Commerce, takže hooky a datový model
> z [nálezu 4](#4-hooky-na-kterých-by-vlastní-kód-visel-jsou-stabilní) pro něj neplatí —
> viz [14-rezervace-oprava.md](14-rezervace-oprava.md).

---

## Doporučení

**Odhlašovací odkaz se v žádném z prověřených řešení koupit nedá.** To je hlavní nález
téhle analýzy. Placená verze Event Tickets sice umí účastníkovi dovolit, aby si rezervaci změnil, ale
**jen po přihlášení uživatelským účtem** — což je přesně ta registrace, kterou festival
nechce. Ověřeno citací z dokumentace i ze zdrojového kódu (viz [Pět nálezů](#pět-nálezů-které-mění-zadání)).
Pokud má návštěvník kliknout na odkaz v e-mailu a tím se odhlásit, je to vlastní kód,
nebo to není.

**Čekací fronta se koupit dá**, ale jen v ročním balíčku za **350 USD = 7 550 Kč ročně**,
což je 4,3násobek dnešního celkového provozu webu (1 750 Kč/rok). A je to opakovaný
náklad na webu, který každý rok přebírá někdo jiný — přesně ta položka, kterou jednou
nikdo neprodlouží.

### Co doporučuju

**Jeden must-use doplněk `bm-rezervace.php` nad Event Tickets, ve dvou etapách, s rozhodovací
branou mezi nimi. Celkem 35–47 hodin. Plus SMTP, které je povinné bez ohledu na zvolenou cestu.**

| Etapa | Co | Hodin | Kdy |
|---|---|---|---|
| **0** | SMTP a doručitelnost e-mailů | 3–4 | **hned, blokuje všechno ostatní** |
| **1** | Odhlašovací odkaz s podepsaným tokenem | 16–21 | do března 2027 |
| **brána** | Změřit v ročníku 2027, kolik lidí rezervaci opravdu zruší | 0 | červen 2027 |
| **2** | Čekací fronta a notifikace | 16–22 | podle naměřeného |

**Proč brána mezi etapami.** Hodnota fronty se rovná počtu uvolněných míst. Ten dnes nikdo
nezná, protože dnes se rezervace zrušit nedá — místa se neuvolňují vůbec. Etapa 1 to číslo
vyrobí. Když se v ročníku 2027 ukáže, že se zrušilo pět rezervací za celý festival, fronta
za 16–22 hodin kódu se nevyplatí a napíše se do programu věta „Je plno? Napište nám."
Když se jich zruší padesát, fronta má smysl a postaví se na klidno do ročníku 2028.

Tohle je přímý výklad zadavatelova „udělejme to pořádně, ať to funguje do budoucna,
času máme dost". Pořádně neznamená postavit obojí najednou naslepo. Znamená postavit
první polovinu, nechat ji rok běžet a druhou postavit podle dat.

> **Pokud zadavatel na obojí do 2027 trvá**, je to možné — 35–47 hodin je od listopadu
> do dubna zhruba 1,5 hodiny týdně. Doporučení se nemění v tom, *co* postavit, jen v tom,
> že etapa 2 vznikne bez dat. Ber to jako vědomě zaplacenou cenu za jistotu, ne za nedopatření.

### Co naopak nedoporučuju

- **Nekupovat balíček za 7 550 Kč/rok.** Vyřeší jen polovinu zadání (frontu, ne odhlášení),
  stojí 4,3× celý dnešní provoz a je to opakovaný náklad na webu bez stálého správce.
- **Neodcházet od The Events Calendar kvůli vzhledu programu.** Viz
  [samostatná kapitola](#odchod-od-the-events-calendar) — důvod, který k tomu vede, je už
  v plánu vyřešený jinak.
- **Nestavět rezervace úplně od nuly.** 62–96 hodin a nulová komunita. Viz
  [cesta F](#cesta-f-vlastní-rezervace-bez-event-tickets-a-bez-tec).

---

## Pět nálezů, které mění zadání

Všechno níže je ověřené 4. 11. 2026 proti primárním zdrojům. Tři z toho opravují starší
dokumenty v tomhle repozitáři.

### 1. Zrušení návštěvníkem nejde koupit ani v placené verzi

Oficiální dokumentace k RSVP říká doslova:

> „Note that this feature requires attendees to create an account and log in before
> registering for your events."

— <https://docs.nexcess.com/software/the-events-calendar/event-rsvps.md>, ověřeno 4. 11. 2026

Potvrzuje to zdrojový kód **free** verze. Metoda, která mění údaje existujícího účastníka,
začíná takhle (`src/Tribe/RSVP.php`, Event Tickets 5.30.2):

```php
public function update_attendee_data( $attendee_data, $attendee_id, $post_id ) {
    // Bail if the user is not logged in.
    if ( ! is_user_logged_in() ) {
        return;
    }
    $user_id = get_current_user_id();
    $rsvp_attendees = $this->tickets_view->get_event_rsvp_attendees( $post_id, $user_id );
    // ...dál se kontroluje, že účastník patří přihlášenému uživateli
```

— <https://plugins.svn.wordpress.org/event-tickets/tags/5.30.2/src/Tribe/RSVP.php>, ověřeno 4. 11. 2026

Celá samoobslužná úprava RSVP je v Event Tickets postavená na vazbě účastník ↔ uživatelský
účet. Placená verze nad tím staví stránku „My Tickets", ale tu samou podmínku nemění.
**Anonymní odkaz v e-mailu placená verze nikdy neuměla a neumí.**

To je oprava [00-analyza.md](00-analyza.md), která uváděla zrušení návštěvníkem jako věc,
kterou „placená verze umí a my ji suplujeme kódem". Nesupluje se placená funkce — ta
funkce v požadované podobě neexistuje u nikoho.

### 2. Čekací fronta v placené verzi naopak existuje, a to i pro RSVP

Tohle je nové oproti [03-overena-fakta.md](03-overena-fakta.md), které frontu vůbec nezmiňuje.

> „You can effortlessly create a waitlist for both free and paid events by adding it
> to your tickets and RSVPs."
>
> „Once the ticket becomes available they will get another email with the link to where
> they can purchase the ticket."

— <https://docs.nexcess.com/software/the-events-calendar/attendee-waitlist/>, ověřeno 4. 11. 2026

Potvrzuje to i produktová stránka: *„Advanced RSVPs — Attendee tracking, waitlists, and
capacity management keep your headcount accurate whether you're charging or not."*
(<https://www.liquidweb.com/software/the-events-calendar/event-tickets/>, ověřeno 4. 11. 2026)

**Co se ověřit nepodařilo a je pro nás klíčové:** dokumentace **nikde neříká, že se fronta
spustí automaticky**, a **vůbec nezmiňuje scénář, kdy účastníka smaže administrátor**.
Popsaný je jen stav „lístek se uvolní". U nás je přitom smazání účastníka pořadatelem
hlavní a dnes jediný způsob, jak se místo uvolní. Kdyby se to kupovalo, je tohle jediná
otázka, kterou je nutné položit podpoře **před** nákupem.

### 3. Ceník výrobce se změnil — tiery Essentials / Pro / Elite už neexistují

[03-overena-fakta.md](03-overena-fakta.md) (září 2026) uvádí Essentials 259 USD, Pro 399 USD,
Elite 599 USD ročně. **Tahle struktura je pryč.** Dnes je na oficiálním webu jediná řada,
odstupňovaná jen počtem webů:

> „Pick your number of sites. The rest is included. Every plan includes the full package:
> Give, The Events Calendar, LearnDash, and Kadence."
>
> 1 SITE — **$350/YEAR** · 10 SITES — $599/YEAR · 25 SITES — $799/YEAR · 50 SITES — $899/YEAR · 100 SITES — $999/YEAR

— <https://www.liquidweb.com/software/the-events-calendar/>, ověřeno 4. 11. 2026

Při kurzu ČNB **21,570 Kč/USD k 4. 11. 2026** je to **7 550 Kč ročně**.

Dvě poznámky. Za prvé: je to levnější než dřívější Pro za 399 USD, a balíček obsahuje
i **Kadence Pro** — téma, které web používá ve free verzi. Takže se nekupuje jen rezervace.
Za druhé: dokumentace k frontě v jedné větě pořád mluví o „Pro license tier"
(<https://docs.nexcess.com/software/the-events-calendar/event-rsvps.md>), který v ceníku
už není. **Dokumentace a ceník se rozcházejí** — další důvod ptát se podpory před nákupem.

Co se stane po roce bez obnovení: podle odpovědí podpory na oficiálním fóru plugin
funguje dál, jen bez aktualizací a bez podpory. **Nenašel jsem k tomu aktuální oficiální
prohlášení**, jen starší fóra, takže to neberte jako jistotu. Pro náš případ je to stejně
jedno: web bez aktualizací placeného pluginu je přesně ten stav, kterým umřel minulý web.

### 4. Hooky, na kterých by vlastní kód visel, jsou stabilní

Tohle je nejdůležitější technický nález, protože rozhoduje o tom, jestli vlastní kód
přežije aktualizace. Vlastní řešení **nepotřebuje sahat do šablon ani do vnitřností**
Event Tickets. Stačí mu tři věci:

| Co | Jak | Kde |
|---|---|---|
| Vznikla rezervace → pošli vlastní e-mail s odkazem | akce `event_tickets_rsvp_tickets_generated` | `src/Tribe/RSVP.php` |
| Účastník byl smazán → uvědom frontu | akce `event_tickets_attendee_ticket_deleted` | `src/Tribe/Tickets.php` |
| Zruš rezervaci a vrať místo do kapacity | `update_stock_from_attendees_page()` + `update_post_meta( $id, '_tribe_rsvp_status', 'no' )` | `src/Tribe/RSVP.php` |

**Obě akce jsem našel beze změny ve verzích 5.20.0, 5.25.0, 5.29.5 i 5.30.2** — tedy
od **6. 3. 2025** (datum vydání 5.20.0 podle changelogu) do **15. 10. 2026** (5.30.2),
přes devatenáct měsíců vydávání včetně několika velkých verzí. Nejsou to privátní vnitřnosti,
jsou to veřejné hooky, na kterých visí doplňky třetích stran — výrobce je nemění bez důvodu.

Metoda, která vrací místo do kapacity, **nemá kontrolu oprávnění**, takže ji smí zavolat
i nepřihlášený návštěvník přes vlastní endpoint:

```php
public function update_sales_and_stock_by_order_status( $order_id, $attendee_order_status, $ticket_id ) {
    // žádný current_user_can(), jen přepočet skladu
```

Pozor na pořadí: přepočet kapacity si čte **předchozí** stav z post meta, takže se musí
zavolat **před** přepsáním `_tribe_rsvp_status` na `no`. Přesně v tomhle pořadí to dělá
i administrace pluginu.

Naopak `delete_ticket()` volat **nelze** — má v sobě `user_can_manage_attendees()`, takže
by anonymnímu návštěvníkovi vrátilo `false`. Proto je správná cesta „měkké zrušení"
(stav `no`), ne tvrdé smazání. Má to i vedlejší přínos: zůstane auditní stopa, kterou
dnešní mazání pořadatelem nenechává.

To **opravuje zdůvodnění** v [02-moduly-detail.md](02-moduly-detail.md), který u zrušení
návštěvníkem uvádí poznámku „sahá do interních API a e-mailové šablony Event Tickets".
**Do šablony sahat nemusí** — vlastní e-mail se pošle vedle toho od pluginu, přes
`wp_mail()`. Tím padá největší část křehkosti. (Hodin naopak o něco přibylo, protože
tahle analýza počítá i s potvrzovací stránkou, vícemístnými rezervacemi a GDPR.)

**Cenou za to jsou dva e-maily místo jednoho.** Návštěvník dostane potvrzení od pluginu
a hned za ním náš e-mail s odhlašovacím odkazem. Elegantnější by bylo odkaz vložit filtrem
přímo do obsahu e-mailu pluginu — jenže tím by kód začal viset na struktuře cizí šablony,
která se mezi verzemi mění mnohem ochotněji než hooky. **Volím dva e-maily.** Jde to
zmírnit tím, že náš e-mail bude stručný a bude se jmenovat tak, aby bylo hned jasné,
k čemu je („Vaše rezervace — odkaz pro zrušení").

### 5. Samostatný doplněk, který by tyhle dvě funkce přidal, neexistuje

Prohledaný adresář wordpress.org na „waitlist" (251 výsledků) a na kombinace k Event Tickets
nevrátil **jediný** plugin, který by k Event Tickets doplňoval frontu nebo odhlášení.
Všechny waitlist pluginy v adresáři jsou WooCommerce „back in stock" notifikace. Dva pluginy,
které inzerují waitlist a migraci z TEC (`warp-calendar`, `legba-event`), mají **0 aktivních
instalací** — podle železného pravidla jsou tím mimo hru.

---

## Porovnání cest

Kurz ČNB 4. 11. 2026: 1 USD = 21,570 Kč. Dnešní provoz webu: ~1 750 Kč/rok.

| Cesta | Odhlášení návštěvníkem | Fronta | Peníze/rok | Hodiny | Co rozbije aktualizace | Předatelnost | Přežije odchod od TEC? |
|---|---|---|---|---|---|---|---|
| **A. Nic navíc + ruční fronta** | ne (ruší pořadatel) | ručně e-mailem | 0 Kč | 2 | nic | **nejlepší** — není co předávat | ano |
| **B. Koupit balíček** | **ne** (vyžaduje účet) | ano, ale neověřeno pro smazání adminem | **7 550 Kč** | 4–6 | nic | dobrá, dokud někdo platí | ekonomicky ne |
| **C. Vlastní mu-plugin nad ET** ⭐ | **ano** | ano | 0 Kč | **35–47** | 2 hooky, ověřeně stabilní 19 měsíců | střední — 1 soubor, česky, s fallbackem | **ano** |
| **D. Přechod na Events Manager Pro** | jen jako *žádost*, ruší pořadatel | ano (Pro) | 2 135–2 783 Kč | 40–60 (migrace programu) | nic | dobrá, ale mění celý program | jen tehdy, když se odchází |
| **E. Hybrid: odkaz kódem, fronta ručně** | ano | ručně e-mailem | 0 Kč | **19–25** | 2 hooky | **dobrá** | ano |
| **G. Hybrid: odkaz kódem, fronta koupená** | ano | ano, s toutéž nejistotou jako B | **7 550 Kč** | 19–25 | 2 hooky + placený plugin | střední, dokud někdo platí | ekonomicky ne |
| **F. Vlastní rezervace bez ET i TEC** | ano | ano | 0 Kč | **62–96** | nic cizího — ale všechno je vaše | **nejhorší** — 0 komunity | ano (je na tom nezávislé) |

### Poznámky k jednotlivým cestám

**A — nedělat nic navíc.** Dnes to funguje tak, že rezervaci smaže pořadatel v přehledu
Účastníci a kapacita se vrátí. Fronta by byla věta v popisu akce („Je plno? Napište na
rezervace@…") a pořadatel by po smazání napsal dalšímu v pořadí. Při 17 akcích
a kapacitách v desítkách míst to **není výsměch, je to reálná volba** — cena se platí
v minutách pořadatele, ne v hodinách vývojáře, a nezanechává po sobě žádný kód.
Hlavní slabina: návštěvník, který nemůže zrušit sám, většinou prostě nepřijde a místo
propadne. To je ten skutečný problém, který se řeší.

**B — koupit balíček.** Řeší frontu, neřeší odhlášení. Bonus je Kadence Pro v ceně.
Zabiják: 7 550 Kč ročně opakovaně u spolku, kde se sestava mění po roce, a nejistota,
jestli se fronta vůbec spustí při smazání účastníka administrátorem.

**C — vlastní mu-plugin.** Doporučená cesta, rozepsaná [níže](#doporučená-cesta-krok-za-krokem).

**D — Events Manager.** Jediný free plugin, který se k zadání blíží, 60 000 instalací,
v adresáři od roku 2008, poslední aktualizace 26. 10. 2026, 4,2★ z 551 hodnocení
(<https://api.wordpress.org/plugins/info/1.2/>, ověřeno 4. 11. 2026). Dvě zásadní výhrady:

1. **Fronta je jen v Pro** — readme v sekci Premium Features: *„Waitlist / waiting list
   for sold-out events and tickets"*, a ve výčtu free: *„Capacity limits per event, ticket
   and timeslot, with sold-out handling and waitlists (Pro)"*.
   Cena Pro: **99 USD/rok za 1 web** (Pro Plus 129 USD), *„All Plans Billed Annually"*
   — <https://wp-events-plugin.com/pricing/>, ověřeno 4. 11. 2026. To je 2 135 Kč/rok,
   což je v rozpočtu spíš než balíček od TEC.
2. **Zrušení hostem bez účtu existuje, ale nic neruší.** Verze 7.3.6 přidala
   *„magic-link access so guests without a WordPress account can reach the cancellation
   form"*. Jenže dokumentace říká, že to *„does **not** auto-cancel the booking or issue
   refunds"* a že je to *„off by default"* — je to evropský institut odstoupení od smlouvy,
   tedy **žádost, kterou pořadatel ručně posuzuje**. Kapacita se sama neuvolní.
   (<https://wp-events-plugin.com/documentation/compliance/right-of-withdrawal/>, ověřeno 4. 11. 2026.)
   Jestli to vůbec platí pro **bezplatné** rezervace, se mi ověřit nepodařilo — právní
   institut, na kterém to stojí, se týká úplatných smluv.

   Běžné samoobslužné zrušení vyžaduje účet: *„will not be able to log in and see their
   previous bookings or request cancellations automatically"* pro no-user bookings
   (<https://wp-events-plugin.com/documentation/booking-options/>, ověřeno 4. 11. 2026).

   Takže i u Events Manageru platí nález č. 1: **anonymní odhlašovací odkaz, který místo
   skutečně uvolní, neumí ani on.**

**E — hybrid s ruční frontou.** Postaví se odhlašovací odkaz (etapa 1 cesty C) a fronta
zůstane ruční. Je to prakticky totéž jako doporučená cesta zastavená na bráně. Uvádím ji
zvlášť, protože je to legitimní cílový stav, ne jen mezikrok.

**G — hybrid s koupenou frontou.** Zadání se ptalo i na kombinaci „zrušení vlastním kódem
a fronta pluginem". Je to jediná dělba, která dává smysl: odhlašovací odkaz se koupit nedá
(nález č. 1), takže ten musí být kódem tak jako tak, a fronta je jediná z obou funkcí,
která se koupit dá. Obrácená varianta — fronta kódem, zrušení pluginem — **neexistuje**,
protože druhou polovinu neprodává nikdo.

Cena: 7 550 Kč ročně **plus** 19–25 hodin, tedy peníze i kód zároveň. Vedlejší zisk je
Kadence Pro v balíčku. Platí tu ale obě výhrady cesty B: opakovaný náklad u organizace
bez stálého správce a neověřené chování fronty při smazání účastníka administrátorem.
Oproti doporučené cestě C se ušetří 16–22 hodin na frontě a zaplatí se za ně 7 550 Kč
každý rok — po dvou ročnících je to dráž než si frontu napsat.

**F — všechno vlastní.** Viz [samostatná kapitola](#cesta-f-vlastní-rezervace-bez-event-tickets-a-bez-tec).

---

## Odchod od The Events Calendar

Zadavatel zvažuje, že od The Events Calendar odejde, protože mu nevyhovují jeho šablony
a chce vlastní stránku programu s vlastní filtrací. Protože na TEC stojí i rezervace,
týká se to téhle analýzy přímo.

### Nejdřív věcná poznámka: ten důvod tenhle krok nevyžaduje

Plán v [02-moduly-detail.md](02-moduly-detail.md) **už počítá s tím, že se TEC nepoužije
jako frontend**:

> „Doporučuji The Events Calendar (free) jako datovou a administrační vrstvu, ale NE jako
> frontend — program a lineup si vykreslí dva vlastní shortcody […] Tím padá celá otázka
> Filter Bar (placený): filtrování 102 kartiček podle kategorie a místa současně si dělám
> sám v prohlížeči."

Vlastní stránka programu s vlastní filtrací je tedy v plánu od začátku, odhadnutá na 14 h,
a **nevyžaduje TEC opustit**. TEC v tom plánu nedodává vzhled, dodává datový model:
místa jako samostatné záznamy, rozsah datum+čas, hierarchickou taxonomii žánr→kategorie
a ICS export. Toho je v kostře ([05-kostra.md](05-kostra.md)) postaveno 28 míst a 51 termínů.

**Pokud je důvod k odchodu „nelíbí se mi, jak to vypadá", je to vyřešené bez odchodu.**
Pokud je důvod jiný (například že administrace TEC je pro dobrovolníka moc složitá),
je to legitimní a je potřeba ho pojmenovat — ale je to jiná diskuse a jiná analýza.

### Co se stane s jednotlivými cestami, když se odejde

Rozhodující fakt: **Event Tickets není na The Events Calendar závislý.** Readme pluginu
nemá hlavičku `Requires Plugins` a RSVP lze připojit k libovolnému typu obsahu, který se
zaškrtne v *Tickets → Settings → General → „Post types that can have tickets"*
(<https://plugins.svn.wordpress.org/event-tickets/tags/5.30.2/readme.txt>, ověřeno 4. 11. 2026;
chování potvrzeno už v [03-overena-fakta.md](03-overena-fakta.md)).

Jinými slovy: **jde odejít od The Events Calendar a nechat si Event Tickets.** Akce by
byly vlastní typ obsahu přes Secure Custom Fields a RSVP by se k nim připnulo.

| Cesta | Přežije odchod od TEC? |
|---|---|
| **A. Nic navíc** | **Ano.** Je to organizační opatření, na technologii nezávisí. |
| **B. Koupit balíček** | **Technicky ano, ekonomicky ne.** Event Tickets Plus funguje i bez kalendáře, ale platilo by se 7 550 Kč ročně za balík, jehož hlavní složku (The Events Calendar) web nepoužívá. |
| **C. Vlastní mu-plugin** | **Ano, beze změny.** Oba hooky i metoda na vrácení kapacity jsou v Event Tickets, ne v TEC. Kód se nedotkne ani jedné funkce TEC. |
| **D. Events Manager** | **Je to jediná cesta, které odchod prospívá.** Nahradí TEC i Event Tickets jedním pluginem. Pokud se odchází tak jako tak, stojí za vážné zvážení — ale rezervace tím nevyřeší (viz výše). |
| **E. Hybrid** | **Ano**, ze stejného důvodu jako C. |
| **F. Všechno vlastní** | **Ano** — je nezávislá na obojím. Je to jediná cesta, která nepotřebuje žádný cizí plugin. |

**Pro rozhodnutí to znamená jedno:** doporučená cesta C se odchodem od The Events Calendar
**nemaří**. Není potřeba čekat, až se o TEC rozhodne. Jediné, co by cestu C zabilo, je
odchod i od **Event Tickets** — a to je cesta F.

---

## Cesta F: vlastní rezervace bez Event Tickets a bez TEC

Akce jako vlastní typ obsahu v Secure Custom Fields, rezervace jako vlastní záznamy,
všechno vlastní. Tady je rozpis, co by se muselo napsat.

| Co | Hodin | Poznámka |
|---|---|---|
| Typ obsahu Akce + pole v SCF | 3–5 | SCF to umí naklikat z administrace, tohle je nejlevnější část |
| Úložiště rezervací (vlastní tabulka nebo CPT) + migrace schématu | 6–8 | vlastní tabulka = vlastní `dbDelta`, vlastní verzování |
| Přihlašovací formulář: validace, kapacita, **souběh** | 10–14 | souběh je ta drahá část: dva lidé kliknou současně na poslední místo |
| Ochrana proti robotům a opakovanému odeslání | 3–5 | Event Tickets to taky neumí, ale tady si to napíšete sami |
| Potvrzovací e-mail + šablona | 4–6 | |
| Odhlašovací odkaz s podepsaným tokenem | 5–7 | levnější než u cesty C, protože není co obcházet |
| Fronta + notifikace | 8–12 | |
| **Administrace: seznam přihlášených, hledání, filtr, mazání, CSV export** | **16–24** | viz níže |
| Oprávnění a role (dramaturgie vidí, ale ne nastavení) | 2–3 | |
| GDPR: exportér, výmaz, retence | 4–6 | |
| Testování, dokumentace, screencast | 10–14 | |
| **Celkem** | **62–96** | |

### Administrace je u téhle cesty dražší než samotná funkce

Web plní jeden dobrovolník, který s WordPressem nikdy nepracoval. U cest A–E dostane
**hotovou obrazovku Účastníci od Event Tickets**: tabulka se jmény a e-maily, vyhledávání,
hromadné akce, tlačítka Export a Tisk, nastavitelné sloupce přes Screen Options,
check-in. Je k ní anglická dokumentace, videa i fórum. V kostře je ověřené, že ji vidí
i role Dramaturgie ([05-kostra.md](05-kostra.md)).

U cesty F tohle všechno zmizí a musí se napsat znovu přes `WP_List_Table` — třídu, která
není součástí veřejného API WordPressu, nemá pořádnou dokumentaci a chová se jinak v každé
verzi. Šestnáct až dvacet čtyři hodin je odhad pro někoho, kdo ji už psal. A výsledek bude
v nejlepším případě **horší** než to, co je dnes zdarma: bez exportu do CSV se neobejdete,
bez vyhledávání taky ne, a každá další drobnost (seřadit podle data, zobrazit obsazenost)
je další hodina.

**Zároveň je to ta část, na které se pozná, jestli web žije.** Funkce, kterou dobrovolník
neumí obsloužit, je stejně dobrá jako funkce, která neexistuje.

### Vztah k železnému pravidlu — naplno

Pravidlo zní: **web musí převzít cizí člověk za jeden večer.** Tahle cesta ho neohýbá,
ona ho láme.

Cesty C a E produkují jeden soubor s českými komentáři, který visí na dvou dokumentovaných
hoocích cizího pluginu. Když ho nový člověk nepochopí, **smaže ho a web dál funguje** —
jen se přestanou posílat odhlašovací odkazy a rezervace se zase ruší ručně, jako dnes.
Ztráta je ohraničená.

Cesta F produkuje **celý rezervační systém jako vlastní kód**. Nový člověk ho smazat nemůže,
protože by zmizely rezervace. Nemůže si o něm nic vygooglit, protože na světě existuje
v jednom exempláři. Nemůže se zeptat na fóru. Když v něm najde chybu tři dny před festivalem,
má dvě možnosti: opravit cizí PHP, kterému nerozumí, nebo rezervace vypnout.

**Tohle je přesně ten stav, kterým umřel minulý web** — deset let dolepovaný vlastní kód,
o který se nakonec nikdo neuměl starat. [AGENTS.md](../../AGENTS.md) to formuluje jako
„současný web je ukázka toho, co se stane, když se tohle deset let porušuje".

Cesta F je 62–96 hodin za to, aby se ta chyba zopakovala o jedno patro výš. **Nedoporučuju
ji ani tehdy, když se od The Events Calendar odejde.** Když se odchází, správná odpověď je
cesta D (Events Manager jako hotový celek), ne psát si vlastní.

---

## Doporučená cesta krok za krokem

Jeden soubor `wordpress/mu-plugins/bm-rezervace.php`, stejným způsobem jako už existující
[`bm-rubriky.php`](../../wordpress/mu-plugins/): must-use, nejde omylem vypnout, české
komentáře, v README vysvětlené proč existuje a **jak ověřit, že žije**.

### Etapa 0 — SMTP (3–4 h) · blokuje všechno ostatní

| Krok | Hodin |
|---|---|
| Nastavit odesílání přes SMTP (plugin + účet, viz [kapitola o e-mailech](#e-maily-a-doručitelnost)) | 2 |
| Doplnit DNS: DMARC záznam, případně rozšířit SPF | 1 |
| Ověřit doručení na Gmail, Seznam a Outlook, zkontrolovat hlavičky | 1 |

**Bez téhle etapy nemá zbytek smysl.** Fronta, ze které nedojdou e-maily, je horší než
žádná fronta — návštěvník čeká na zprávu, která nepřijde.

### Etapa 1 — odhlašovací odkaz (16–21 h)

| Krok | Co | Hodin |
|---|---|---|
| 1 | Kostra doplňku, podepisování tokenu (`hash_hmac` nad `wp_salt()`, porovnání přes `hash_equals`), rewrite endpoint | 3–4 |
| 2 | Vlastní e-mail s odkazem, posílaný na `event_tickets_rsvp_tickets_generated` **vedle** e-mailu od pluginu | 3–4 |
| 3 | Potvrzovací stránka: odkaz z e-mailu jen **zobrazí** stránku, zrušení proběhne až po odeslání formuláře | 2–3 |
| 4 | Vlastní zrušení: přepočet kapacity, pak `_tribe_rsvp_status` = `no`; **projít všechna místa jedné objednávky** (viz níže); ošetřit dvojí kliknutí a už zrušenou rezervaci | 4–5 |
| 5 | Testování včetně aktualizace Event Tickets na novější verzi | 2–3 |
| 6 | README, odstavec do [06-navod-pro-tym.md](06-navod-pro-tym.md), screencast | 2 |

**Pozor na vícemístné rezervace.** Formulář RSVP má pole „Počet míst" a Event Tickets
z jedné objednávky udělá **jeden záznam účastníka na každé místo** — akce
`event_tickets_rsvp_tickets_generated_for_product` předává pole `$attendee_ids`.
Token se proto musí podepisovat nad **číslem objednávky**, ne nad jedním účastníkem,
a zrušení musí projít všechny účastníky té objednávky a u každého zvlášť vrátit místo
do kapacity. Kdo na to zapomene, vyrobí odkaz, který ze tří rezervovaných míst uvolní
jedno. Je to zhruba hodina práce navíc, ale musí se s ní počítat od začátku — dodatečně
se mění formát tokenu, a tím se zneplatní odkazy v už odeslaných e-mailech.

**Proč krok 3 nesmí chybět.** Odkaz v e-mailu nesmí rušit rezervaci jediným GET požadavkem.
Antivirové brány, náhledy odkazů v Gmailu a firemní skenery odkazy v e-mailech předem
načítají — rezervace by se rušily samy od sebe, aniž by na ně někdo klikl. Mezikrok
s potvrzovacím tlačítkem to řeší a je to zároveň pojistka proti překlepu.

**Token.** `hash_hmac( 'sha256', $attendee_id . '|' . $email, wp_salt( 'bm_rezervace' ) )`.
Čistě jádro WordPressu, žádná databáze navíc, žádná expirace k hlídání, nejde uhodnout.
Odkaz platí, dokud rezervace existuje — což je správně, rezervace se ruší i den předem.

### Brána — změřit (0 h, červen 2027)

Po festivalu zjistit: kolik rezervací se zrušilo odkazem, u kolika akcí bylo plno a jak
dlouho. Z toho vyjde, jestli má fronta smysl. Patří to do checklistu „co dělat po festivalu".

### Etapa 2 — čekací fronta (16–22 h)

| Krok | Co | Hodin |
|---|---|---|
| 1 | Úložiště fronty — vlastní typ obsahu, ne vlastní tabulka (viz níže) | 2–3 |
| 2 | Formulář „dejte mi vědět, až se uvolní místo", zobrazený na akci místo RSVP, když je plno; nonce a honeypot proti robotům | 4–5 |
| 3 | Notifikace na `event_tickets_attendee_ticket_deleted` — **synchronně, prvním N ve frontě** | 3–4 |
| 4 | Vyřazení z fronty, když se dotyčný úspěšně přihlásí — na `event_tickets_rsvp_tickets_generated`, párování podle e-mailu | 1 |
| 5 | Přehled fronty v administraci + odstranění ze seznamu | 3–4 |
| 6 | Odhlašovací odkaz i z fronty (stejný token, stejný endpoint) + zapojení do výmazu osobních údajů (viz [GDPR](#právo-na-výmaz)) | 1–2 |
| 7 | Testování, dokumentace | 2–3 |

**Krok 4 nevynechávejte.** Bez něj zůstane člověk ve frontě i poté, co si místo zabral,
a při dalším uvolnění dostane pozvánku znovu — na akci, na kterou už je přihlášený.
Je to jeden hook a pár řádků, ale bez něj fronta po první vlně zrušení vypadá rozbitě.

**Proč vlastní typ obsahu, a ne vlastní tabulka.** Typ obsahu dostane zdarma výpis
v administraci, vyhledávání, mazání, hromadné akce a — což je u osobních údajů podstatné —
funguje na něj standardní `wp_delete_post()`. Vlastní tabulka by znamenala psát `dbDelta`,
verzování schématu a celou administraci znovu. U desítek záznamů ročně je výkonový rozdíl
nulový. To je volba pro toho, kdo web převezme, ne pro databázi.

**Proč žádná rezervace místa na čas.** Nabízí se „první ve frontě dostane místo zamluvené
na 24 hodin". Nedělejte to. Vyžaduje to plánovač, který na sdíleném hostingu není spolehlivý
(viz níže), a vyrábí stav „místo je volné, ale nedá se zabrat", který nikdo nepochopí.
Místo toho: **při uvolnění dostane e-mail prvních N lidí ve frontě najednou a místo dostane
ten, kdo klikne první.** Nula závislostí na čase, srozumitelné pravidlo, nula kódu navíc.
Komu se nedostane, zůstane ve frontě na příště. `N` jako konstanta na začátku souboru,
výchozí 3.

### Cron — a proč ho tenhle návrh nepotřebuje

Zadání upozorňuje, že `wp_cron` běží jen při návštěvě stránky. Dva poznatky:

1. **Návrh výše cron nepotřebuje vůbec.** Notifikace se posílá synchronně v okamžiku,
   kdy pořadatel smaže účastníka — tedy při požadavku v administraci, kdy PHP běží.
   Žádná odložená úloha, žádné čekání na návštěvníka.
2. **Wedos ale systémový cron má**, i když o něm zadání nemluví:

   > „V základním nastavení můžete webhostingu nastavit až 3 různé úlohy CRONu spouštěné
   > v minimálním intervalu 1 hodiny." · „Je určený ke spouštění PHP skriptů, nikoliv
   > příkazů Bash."
   >
   > — <https://kb.vedos.cz/en/webhosting-cron/>, ověřeno 4. 11. 2026

   Spouští jen skripty na témž webhostingu, takže `wp-cron.php` zavolat umí. Doplňková
   služba CRON+ zvedá limit na 10 úloh a interval na 5 minut; **cenu CRON+ se mi zjistit
   nepodařilo**.

   Hodinový cron je k dispozici zdarma a stojí za to ho nastavit — ne kvůli frontě, ale
   kvůli naplánovaným příspěvkům, zálohám a kontrole aktualizací, které dnes na webu
   s malou návštěvností nabírají skluz.

### Co to stojí v předatelnosti — bez schovávání

Zadání chce tuhle cenu vyčíslit, tak tady je:

| Položka | Hodnota |
|---|---|
| Jednorázově | 35–47 h (etapa 1: 16–21, etapa 2: 16–22, SMTP 3–4) |
| Opakovaně | **1–2 h ročně** na otestování po velkých aktualizacích Event Tickets |
| Počet míst, kde se to může rozbít | **2 hooky + 1 metoda**, všechny ověřeně stabilní od verze 5.20.0 (6. 3. 2025) |
| Velikost | 1 soubor, odhadem 400–500 řádků i s komentáři |
| Co se stane, když tomu nový člověk nerozumí | **nic fatálního** — soubor smaže, rezervace fungují dál, jen bez odhlašovacího odkazu |
| Co se stane, když Event Tickets hook zruší | odkaz se přestane posílat; rezervace fungují dál |

**Tohle je ta obhajoba.** Není v tom, že kód je malý — čtyřicet hodin vlastního PHP malých
není. Je v tom, že **selhání degraduje na dnešní stav místo na rozbitý web**. Doplněk
nenahrazuje nic, co by bez něj nefungovalo; jen přidává vrstvu navíc. Když zmizí, zmizí
i ta vrstva a nic víc.

Aby to platilo i za dva roky, musí být v kódu i v README napsané:
- proč doplněk existuje a co se stane, když se smaže,
- **které tři řádky sahají do Event Tickets** — jmenovitě, aby se daly najít grepem,
- **jak ověřit, že žije**: založit testovací rezervaci, zkontrolovat, že přišly dva e-maily
  a že odkaz v tom druhém zruší rezervaci a vrátí místo do kapacity.

Přesně tenhle vzor už v repozitáři funguje u `bm-rubriky.php` a stojí za to ho neopouštět.

---

## E-maily a doručitelnost

**Tohle je nejzávažnější zjištění analýzy po nálezu o přihlašování. Dnešní nastavení
domény nedoručí rezervační e-maily spolehlivě ani do Gmailu, ani na Seznam.** A protože
celé zadání stojí na tom, že návštěvníkovi přijde odkaz, je to podmínka, ne doporučení.

### Co je naměřeno

Změřeno 4. 11. 2026 přes `dig`:

```
budejovickymajales.cz.  TXT  "v=spf1 include:_spf.google.com ~all"
_dmarc.budejovickymajales.cz.  TXT  — záznam neexistuje
google._domainkey.budejovickymajales.cz.  TXT  — záznam neexistuje
budejovickymajales.cz.  MX   aspmx.l.google.com (a další Google)
budejovickymajales.cz.  A    46.28.105.102   (Wedos)
novy.budejovickymajales.cz.  A  46.28.105.102   (Wedos)
```

Tři fakta z toho:

1. **SPF neopravňuje Wedos.** Povoluje jen Google. Web ale běží na Wedosu a odesílá z něj.
2. **Doména nemá DKIM.** Žádný selektor jsem nenašel.
3. **Doména nemá DMARC.**

### Proč to znamená problém

Výchozí `wp_mail()` posílá přes PHP `mail()`. Dokumentace Wedosu k tomu říká:

> „In the basic settings of the mail() function, the sender of such e-mails is a web hosting
> server, for example hcX-wdXXX.wedos.net, to which the records you set do not apply."
>
> — <https://kb.vedos.cz/en/maily-spf-dkim-dmarc/>, ověřeno 4. 11. 2026

Takže e-mail odejde buď jako `@budejovickymajales.cz` z IP, kterou SPF domény nepovoluje,
nebo jako `@…wedos.net`, kde se odesílatel neshoduje s doménou ve `From`. V obou případech
**neprojde ani SPF, ani DKIM**. A Google má od roku 2024 jako minimum pro **všechny**
odesílatele, nejen hromadné:

> „All senders: SPF or DKIM."
>
> — <https://support.google.com/a/answer/81126>, ověřeno 4. 11. 2026

Hromadné požadavky (SPF + DKIM + DMARC) se týkají až odesílatelů nad 5 000 zpráv denně,
což nás netýká — náš objem jsou stovky e-mailů ročně. Ale **minimum neplníme ani to.**

[03-overena-fakta.md](03-overena-fakta.md) tohle označovalo za „typicky končí ve spamu —
připočtěte SMTP plugin". **Teď je to doložené konkrétními DNS záznamy, ne odhad.**

### Doporučení

**Odesílat přes SMTP Google Workspace, na kterém už doména jede.** Je to jediná cesta,
u které **SPF domény odesílatele už dnes povoluje** — `include:_spf.google.com` tam je.

| Krok | Co |
|---|---|
| 1 | Nainstalovat **FluentSMTP** (zdarma, bez placené verze, 700 000 instalací, 4,8★ ze 412, poslední aktualizace 4. 11. 2026) nebo **WP Mail SMTP** (4 000 000 instalací, 4,8★, ale tlačí na placenou verzi) |
| 2 | Schránka `web@budejovickymajales.cz`, zapnout dvoufázové ověření, vytvořit **aplikační heslo** |
| 3 | V pluginu nastavit `smtp.gmail.com:587` (STARTTLS), odesílatele `web@budejovickymajales.cz` |
| 4 | V Google Workspace **zapnout DKIM** pro doménu a vložit vrácený TXT záznam do DNS |
| 5 | Přidat `_dmarc` záznam, pro začátek `v=DMARC1; p=none; rua=mailto:web@budejovickymajales.cz` |
| 6 | Otestovat na Gmail, Seznam.cz a Outlook; v přijatém e-mailu zkontrolovat `Authentication-Results` |

Doporučuju FluentSMTP: nemá placenou verzi, takže v administraci nic nebliká na upgrade,
a to je u webu bez stálého správce k nezaplacení.

**Krok 4 nepřeskakujte.** DKIM není v Google Workspace zapnutý sám od sebe. Bez něj stojí
doručitelnost jen na SPF a první změna poskytovatele ji shodí.

**Seznam.cz** je pro český festival stejně důležitý jako Gmail a chová se jinak. Testovat
na něj zvlášť.

### Kolik e-mailů to vlastně je

Při 17 akcích s rezervací a kapacitách v desítkách míst: řádově **nízké stovky potvrzení
za ročník**, plus zlomek odhlášení a notifikací z fronty. Všechno se to navíc sejde do
několika dnů před festivalem.

Pro srovnání limity: Wedos uvádí u PHP `mail()` i SMTP *„usually 500 emails per day"*
(<https://kb.vedos.cz/en/maily-blokovani/>, ověřeno 4. 11. 2026); Google Workspace má
denní limity v řádu tisíců. **Objem není problém, autentizace ano.**

### Pojistka

Do checklistu před festivalem patří jedna věta: **poslat si zkušební rezervaci na vlastní
Gmail a Seznam a zkontrolovat, že přišla do doručené pošty.** Když někdo mezitím změní DNS
nebo vyprší aplikační heslo, zjistí se to v klidu, a ne v den, kdy se otevírají rezervace.

---

## GDPR

Zpracovávají se jméno a e-mail účastníků, část z nich nezletilých. Při zavedení fronty
přibude druhý soubor stejných údajů.

### Role a právní základ

**Správce je spolek** (Budějovický Majáles, provozovatel festivalu), ne jednotlivý
dobrovolník a ne vývojář webu. To patří výslovně do zásad zpracování na webu i do
předávacího protokolu — jméno konkrétního člověka tam být nemá, protože se každý rok mění.

**Zpracovatelé**: Wedos (hosting a databáze), Google (Workspace pro e-maily), případně
další e-mailová služba. Všichni v EU nebo s odpovídajícím režimem — doporučuju tam žádného
dalšího nepřidávat a zůstat u těch dvou, které už web má.

**Právní základ rezervace je plnění smlouvy, respektive opatření před jejím uzavřením**
(čl. 6 odst. 1 písm. b GDPR), ne souhlas. Návštěvník si rezervuje místo; jméno a e-mail
jsou k tomu nezbytné — bez e-mailu nelze potvrdit ani poslat odhlašovací odkaz.
Totéž platí o frontě: člověk žádá, aby mu bylo dáno vědět, a e-mail je k tomu nutný.

**Proč je to důležité kvůli nezletilým.** Kdyby základem byl souhlas, narazí se na to,
že u dětí pod 15 let (§ 7 zákona č. 110/2019 Sb.) musí souhlas dát zákonný zástupce —
což by na rezervaci na koncert bylo absurdní a nevymahatelné. Při plnění smlouvy tahle
podmínka neplatí. **Souhlas si proto nikde nevyžadujte** — zaškrtávátko „souhlasím se
zpracováním" by právní základ nezlepšilo a jen by vyrobilo povinnost ho evidovat.

Co z toho plyne prakticky: **u rezervace nesbírejte víc než jméno a e-mail.** Jakékoli pole
navíc (věk, škola, telefon) z toho dělá jiné zpracování. Je to shodou okolností přesně to,
co free verze Event Tickets umí — omezení pluginu tady hraje pro nás.

### Retence

Dnes se nemaže nic a dluh roste každým ročníkem. [02-moduly-detail.md](02-moduly-detail.md)
to pojmenovává jako „GDPR dluh, který se každý rok zvětšuje".

**Doporučení: smazat všechny účastníky i celou frontu do 30 dnů po skončení festivalu.**

| Údaj | Doba | Kdo maže |
|---|---|---|
| Účastníci rezervací | **30 dnů po festivalu** | Admin 1 |
| Čekací fronta | **ihned po skončení akce**, nejpozději 30 dnů po festivalu | Admin 1 |
| Souhrnná čísla (kolik lidí, kolik zrušení) | bez omezení | — |

Třicet dnů je kompromis: pokrývá dořešení reklamací a ztrát a nálezů, a je krátké na to,
aby se data nestačila nabalit. Fronta může jít dřív — po akci je k ničemu.

**Souhrnná čísla nejsou osobní údaj.** Počet rezervací, počet zrušení a obsazenost se
mohou uchovat navždy a je to to jediné, co se pro plánování příštího ročníku opravdu hodí.
Před mazáním si je poznamenat — mimo jiné proto, že právě ta čísla rozhodnou o bráně
mezi etapou 1 a 2.

**Kdo maže: Admin 1** (nativní administrátor z vedení), ne dramaturgie. Důvod je praktický:
dramaturgie se mění s každou sestavou, Admin 1 ne. A role Dramaturgie podle
[05-kostra.md](05-kostra.md) stejně nemá právo na export účastníků, takže úplný přehled
nad daty má jen Admin 1.

**Jak se to vymáhá, aby to nezůstalo na papíře.** Zapsat jako položku do checklistu
„co dělat po festivalu" ([00-analyza.md](00-analyza.md), podmínky předání, bod 5). Nikoli
jako automatiku v kódu — automatické mazání, kterému nikdo nerozumí, je horší než položka
v checklistu, protože jednou smaže něco, co nemělo.

### Právo na výmaz

**Dobrá zpráva: Event Tickets je na to napojený na jádro WordPressu.** Ve zdrojovém kódu
free verze (`src/Tribe/Privacy.php`, 5.30.2):

```php
add_filter( 'wp_privacy_personal_data_exporters', [ $this, 'register_exporters' ], 10 );
add_filter( 'wp_privacy_personal_data_erasers',   [ $this, 'register_erasers' ],   10 );
```

s metodami `rsvp_exporter()` a `rsvp_eraser()`
(<https://plugins.svn.wordpress.org/event-tickets/tags/5.30.2/src/Tribe/Privacy.php>,
ověřeno 4. 11. 2026).

Znamená to, že žádost o výmaz se vyřídí **standardním nástrojem WordPressu** —
*Nástroje → Smazat osobní údaje*, zadá se e-mail, potvrdí se, hotovo. Žádný vlastní kód,
žádné hrabání v databázi, a hlavně: **je to postup, který si nový člověk najde v české
dokumentaci WordPressu**, ne v našem README.

**Z toho plyne jeden tvrdý požadavek na cestu C:** vlastní úložiště fronty se **musí**
zaregistrovat do týchž dvou filtrů. Jinak by výmaz přes standardní nástroj frontu
přeskočil a zůstala by v ní jména lidí, kteří o výmaz požádali. Je to zhruba třicet řádků
a je to v odhadu etapy 2 započítané.

To je mimochodem další argument proti [cestě F](#cesta-f-vlastní-rezervace-bez-event-tickets-a-bez-tec):
tam by se exportér i eraser musely napsat pro celý rezervační systém, ne jen pro frontu.

### Co ještě patří na web

- **Zásady zpracování osobních údajů** rozšířit o odstavec k rezervacím: co se sbírá,
  proč, jak dlouho, kdo je správce, jak požádat o výmaz. WordPress má pro to vestavěnou
  stránku a Event Tickets do ní **sám nabízí text** (`default_privacy_policy_content()`
  v témže souboru) — stačí ho přeložit, nepsat od nuly.
- **Odkaz na zásady přímo u rezervačního formuláře**, ne jen v patičce.
- Do [06-navod-pro-tym.md](06-navod-pro-tym.md) už patří věta, že seznam přihlášených
  jsou osobní údaje. Doplnit k ní, že **export CSV se nesmí posílat mailem ani nahrávat
  na Disk** — stáhnout, použít, smazat.

---

## Rizika a co by analýzu zneplatnilo

| Riziko | Dopad | Ošetření |
|---|---|---|
| E-maily nedojdou, protože se přeskočí etapa 0 | **fronta i odhlašovací odkaz jsou k ničemu** | etapa 0 je první, zkušební e-mail v checklistu před festivalem |
| Event Tickets změní nebo zruší jeden ze dvou hooků | odkaz se přestane posílat | hooky stabilní od 5.20.0; selhání degraduje na dnešní stav, ne na rozbitý web |
| Doplněk se po aktualizaci neotestuje | tiché selhání, nikdo si nevšimne | „jak ověřit, že žije" v README + položka v checklistu před festivalem |
| Nová sestava doplňku nerozumí | funkce zmizí | 1 soubor, česky, s popsaným fallbackem; ztráta je ohraničená |
| Rozhodnutí o odchodu od TEC padne až v průběhu | zahozená práce | **cesta C to přežije beze změny** — není na co čekat |
| Odejde se i od Event Tickets | cesta C padá | rozhodnout **před** etapou 1; je to otevřená otázka č. 2 |
| Rezervace se otevřou pozdě a nic se nestihne otestovat | ostrý provoz jako test | termín: hotovo a otestováno **do konce března 2027**, rezervace se otevírají v dubnu |
| Nezměří se brána a etapa 2 se postaví naslepo | 16–22 h možná zbytečně | poznamenat čísla před mazáním dat (viz retence) |

### Co by tuhle analýzu zneplatnilo

1. **Event Tickets přidá frontu nebo odhlášení do free verze.** Sleduj changelog na
   <https://wordpress.org/plugins/event-tickets/#developers>. Dnes tam ani jedno není.
2. **Výrobce zase změní ceník.** Za 14 měsíců se změnil už dvakrát (tři tiery → jeden
   balíček). Před jakýmkoli nákupem ověřit znovu.
3. **Odpověď podpory, že se fronta v placené verzi při smazání účastníka administrátorem
   nespustí.** Tím by cesta B přestala řešit i tu polovinu, kterou podle dokumentace řeší.
4. **Program odejde i od Event Tickets.** Pak padá cesta C a rozhoduje se mezi D a F.
5. **Google nebo Seznam zpřísní pravidla pro nízkoobjemové odesílatele.** Nepravděpodobné
   v horizontu jednoho ročníku, ale doporučený postup (SPF + DKIM + DMARC) je proti tomu
   odolný.
6. **Zadavatel bude chtít u rezervace sbírat víc než jméno a e-mail.** Změní to právní
   základ i rozsah: vlastní pole jsou placená funkce a free verze je neumí.

---

## Otevřené otázky na zadavatele

Očíslované, u každé doporučený default. Když se neodpoví, postupuje se podle defaultu.

**1. Stavět obojí do 2027, nebo odhlašovací odkaz teď a frontu podle naměřeného?**
> **Default: odhlašovací odkaz do března 2027, fronta po měření v červnu 2027.**
> Hodnota fronty se rovná počtu uvolněných míst a to dnes nikdo nezná. Když zadavatel
> na obojí trvá, dá se to stihnout — jen to bude postavené naslepo.

**2. Odchází se od The Events Calendar, a pokud ano, i od Event Tickets?**
> **Default: zůstat na obojím.** Důvod, který k odchodu vede (vlastní vzhled programu
> a vlastní filtrace), je v plánu vyřešený vlastním shortcodem a odchod nevyžaduje.
> Doporučená cesta odchod od TEC přežije beze změny; odchod od **Event Tickets** ji ruší,
> proto se tohle musí rozhodnout **před** začátkem etapy 1.

**3. Kupuje se balíček za 7 550 Kč ročně?**
> **Default: ne.** Vyřeší jen polovinu zadání (frontu, ne odhlášení), stojí 4,3× celý
> dnešní roční provoz webu a je to opakovaný náklad u organizace, kde se sestava mění
> po roce. Pokud by přesto padlo ano, **nejdřív se zeptat podpory**, jestli se fronta
> spustí, když účastníka smaže administrátor — v dokumentaci to není.

**4. Tvrdé smazání, nebo měkké zrušení se stavem „nepřijde"?**
> **Default: měkké zrušení.** Je to technicky jediná schůdná cesta pro nepřihlášeného
> návštěvníka (tvrdé mazání má v pluginu kontrolu oprávnění) a zanechá auditní stopu,
> kterou dnešní mazání pořadatelem nemá. Osobní údaje se uklidí hromadně při retenci,
> ne po jednom.

**5. Kolik lidí z fronty se obešle, když se uvolní jedno místo?**
> **Default: 3, místo dostane ten, kdo klikne první.** Žádná rezervace místa na čas —
> vyžadovala by plánovač a vyrábí nepochopitelný stav „volné, ale nezabratelné".
> Hodnota je konstanta na začátku souboru, dá se změnit jedním číslem.

**6. Jak dlouho se drží jména a e-maily účastníků po festivalu?**
> **Default: 30 dnů, maže Admin 1, položka v checklistu „co dělat po festivalu".**
> Fronta se maže hned po akci. Souhrnná čísla zůstávají — nejsou to osobní údaje
> a jsou to jediná data, která se pro příští ročník opravdu hodí.

**7. Smí se u rezervace sbírat něco nad jméno a e-mail (poznámka, telefon, věk)?**
> **Default: ne.** Free verze to neumí, placená to podmiňuje balíčkem a hlavně: každé
> pole navíc mění rozsah zpracování osobních údajů u publika, které je zčásti nezletilé.
> Jméno a e-mail jsou nezbytné pro plnění; cokoli dalšího už se musí zvlášť obhájit.

**8. Kdo je uveden jako správce osobních údajů a kdo za to prakticky odpovídá?**
> **Default: správcem je spolek, prakticky odpovídá Admin 1.** Do zásad zpracování
> i do předávacího protokolu patří spolek a funkce, **ne jméno konkrétního studenta** —
> to se mění každý rok a zásady by se musely přepisovat.

**9. Je přístup ke schránce `web@budejovickymajales.cz` a smí se tam zapnout dvoufázové
ověření a vytvořit aplikační heslo?**
> **Default: ano, bez toho etapa 0 nejde dokončit.** Pokud přístup není, je náhradní
> cestou SMTP Wedosu — ale to vyžaduje doplnit `include:_spf.we.wedos.net` do SPF záznamu
> domény, tedy zásah do DNS. Jedna z těch dvou cest se musí otevřít.

**10. Kdo smí zasáhnout do DNS domény (DKIM, DMARC) a jak rychle?**
> **Default: zjistit to hned, ještě v listopadu.** Doména podle
> [00-analyza.md](00-analyza.md) visí na `webmajales@gmail.com`. Bez zásahu do DNS nejde
> doplnit DKIM ani DMARC, a tím se zastaví etapa 0, na které stojí všechno ostatní.
> Tohle je nejpravděpodobnější blokátor celého harmonogramu.

---

## Zdroje

Všechno ověřeno **4. 11. 2026**, pokud není uvedeno jinak.

**Event Tickets a výrobce**
- Zdrojový kód 5.30.2 — <https://plugins.svn.wordpress.org/event-tickets/tags/5.30.2/>
  (soubory `src/Tribe/RSVP.php`, `src/Tribe/Tickets.php`, `src/Tribe/Attendees.php`, `src/Tribe/Privacy.php`)
- Stabilita hooků ověřena proti tagům 5.20.0, 5.25.0, 5.29.5 a 5.30.2 v témže repozitáři
- Fronta v placené verzi — <https://docs.nexcess.com/software/the-events-calendar/attendee-waitlist/>
- RSVP a nutnost přihlášení — <https://docs.nexcess.com/software/the-events-calendar/event-rsvps.md>
- Ceník — <https://www.liquidweb.com/software/the-events-calendar/> a <https://www.liquidweb.com/software/the-events-calendar/event-tickets/>
- Metadata pluginů — <https://api.wordpress.org/plugins/info/1.2/> (Event Tickets 5.30.3.1,
  90 000 instalací, 3,5★ z 236; The Events Calendar 6.18.3, 600 000 instalací, 4,2★ z 2 462).
  **Na webu běží Event Tickets 5.30.2 a The Events Calendar 6.18.2** — ověřeno staženým
  `readme.txt` z <https://novy.budejovickymajales.cz>. Obojí je o jednu verzi pozadu.

**Events Manager**
- readme (Premium Features, changelog 7.3.6) — <https://plugins.svn.wordpress.org/events-manager/trunk/readme.txt>
- Ceník — <https://wp-events-plugin.com/pricing/>
- Odstoupení od smlouvy — <https://wp-events-plugin.com/documentation/compliance/right-of-withdrawal/>
- Rezervace bez účtu — <https://wp-events-plugin.com/documentation/booking-options/>

**Hosting a e-maily**
- DNS změřeno příkazem `dig` proti veřejným serverům, 4. 11. 2026
- Wedos: SPF, DKIM, DMARC a chování `mail()` — <https://kb.vedos.cz/en/maily-spf-dkim-dmarc/>
- Wedos: limity odesílání — <https://kb.vedos.cz/en/maily-blokovani/>
- Wedos: cron — <https://kb.vedos.cz/en/webhosting-cron/>
- Požadavky Googlu na odesílatele — <https://support.google.com/a/answer/81126>

**Kurz**
- ČNB, kurzovní lístek č. 212 ze 4. 11. 2026: 1 USD = 21,570 Kč, 1 EUR = 24,310 Kč
  — <https://www.cnb.cz/cs/financni-trhy/devizovy-trh/kurzy-devizoveho-trhu/kurzy-devizoveho-trhu/denni_kurz.txt>

### Kde si nejsem jistý

Poctivě, ať se na to nespoléhá:

- **Chování fronty v placené verzi při smazání účastníka administrátorem.** Dokumentace
  to neříká. Je to jediná otázka, na které stojí použitelnost cesty B.
- **Co se stane s placeným pluginem po vypršení licence.** Mám jen starší odpovědi podpory
  na fóru, žádné aktuální oficiální prohlášení.
- **Cena doplňku CRON+ u Wedosu.** Nenašel jsem. Pro doporučenou cestu to nehraje roli,
  základní hodinový cron zdarma stačí.
- **Jestli se odstoupení od smlouvy v Events Manageru vztahuje i na bezplatné rezervace.**
  Dokumentace to neuvádí a právní institut, na kterém stojí, míří na úplatné smlouvy.
- **Odhady hodin.** Jsou to odhady jednoho člověka, nikým nevalidované. Spodní hranice
  platí pro někoho, kdo podobný doplněk už psal; horní pro toho, kdo se WordPress učí
  za pochodu. U cesty F je rozptyl největší a nejspíš podstřelený — administrace
  přes `WP_List_Table` se pravidelně ukáže dražší, než vypadá.
