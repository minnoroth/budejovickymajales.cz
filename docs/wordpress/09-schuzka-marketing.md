# Schůzka s marketingovým ředitelem — příprava

Druhá schůzka. První byla „co chcete a co to stojí". Tahle je jiná: **web stojí, je prázdný
a od teď je úzké hrdlo obsah, ne kód.**

Spolek je malý, takže proti tobě sedí člověk, který o obsahu **rozhoduje a zároveň ho bude plnit
sám**. To je ta nejdůležitější věc, kterou si o schůzce pamatovat: **jeho čas je nejvzácnější
zdroj projektu.** Cílem schůzky není rozdat mu úkoly, ale ubrat mu práci a nechat si potvrdit
jen to, co opravdu rozhodnout musí.

Odškrtávej přímo tady — je to zápis ze schůzky.

---

## Stav, se kterým tam jdeš

| Co | Kolik |
|---|---|
| Místa konání | **28 — hotovo** |
| Rubriky a žánry | **51 termínů** — čeká na pročištění |
| Ročníky | 1 |
| Akce | **0** |
| Králové | 0 |
| Aktuality | 0 |
| Uživatelé | **1 — jen ty** |

**Oprava proti dřívější verzi: místa jsou hotová, ne „27 z 49".** Číslo 49 bylo z databáze
napříč ročníky. Program 2026 reálně používal **27 různých míst** (ověřeno rozborem všech 102
stránek vystoupení v archivu) a všechna už na webu jsou. Úkol „doplň 22 míst" tedy neexistuje.

**Při té kontrole se našla chyba:** chybělo **Piaristické náměstí** — s 13 vystoupeními
nejvytíženější místo festivalu. Vypadlo při dřívějším úklidu. Je zpátky (ID 194).

---

## Udělat předem

- [ ] **Založit mu účet — role Administrator.** Je vedení i obsluha v jedné osobě, omezená role
      by ho jen brzdila. Ideálně doménová schránka `@budejovickymajales.cz`, ne osobní Gmail.
- [ ] **Poslat mu [06-navod-pro-tym.md](06-navod-pro-tym.md) předem.** Pozor: tohle není návod
      pro „jeho tým", tohle je **jeho vlastní manuál**. Tak mu ho i představ.
- [ ] Mít otevřené dvě záložky: web a administraci, přihlášený.

---

## Co ukázat — 20 minut, a je to už školení

Když web plní on sám, přestává tohle být předvádění a začíná to být zaškolení. Počítej s tím,
že u kroku 3 strávíte nejvíc času, a je to tak správně.

1. **Úvodní stránka.** Ať vidí grafiku 2024 živou. Řekni rovnou, že kartičky a dlaždice jsou
   ukázková data, ne program.

2. **Přepnout barvu v paletě.** Vzhled → Přizpůsobit → Barvy → slot 1 ze zelenomodré na cokoliv.
   Náhled překreslí hlavičku, hero, tlačítka, rámečky kartiček i patičku naráz. Pak zahoď bez
   uložení. **Pro něj to znamená, že nová grafika na konci roku nebude znamenat objednávku
   u vývojáře — ani večer jeho vlastní práce.**

3. **Klávesnici jemu, a ať založí akci od začátku do konce.** Tohle je jádro schůzky. Sleduj,
   kde zaváhá — tam je návod špatně napsaný a je potřeba ho opravit. Nech ho dojít až
   k publikování a podívejte se na výsledek na webu.

4. **Aktualita.** Druhý typ obsahu, který bude psát nejčastěji. Dvě minuty, a hned ji uvidí
   na úvodní stránce.

5. **Mobil.** Otevři web na telefonu. Většina návštěvníků festivalu je na mobilu.

---

## Co vysvětlit — tři věty, víc ne

**„Kritérium není hezký web, ale web, který za rok převezme cizí člověk za jeden večer."**
Proto WordPress a ne vlastní kód, proto čtyři pluginy a ne dvacet, proto se barvy mění
klikáním. Současný web je ukázka toho, co se stane, když se tohle deset let nedodržuje.

**„Co není v kalendáři, není na webu."** Program se nepíše do stránky, zadává se jako akce.
Stránka Program si ho vytáhne sama. Totéž králové, partneři, aktuality.

**„Všechno mechanické udělám za tebe. Ty piš jen to, co nikdo jiný napsat nemůže."**
Tohle je ta věta, kvůli které schůzka dopadne dobře. Nedávej mu seznam úkolů — dej mu seznam
věcí, kterých se nemusí dotknout.

---

## Co udělám za něj

Projdi to nahlas. Každá položka je hodina jeho času, kterou nemusí obětovat.

- **Pročistit 51 termínů rubrik a žánrů** na seznam, na kterém se dohodneme. Je to skript,
  ne klikání.
- **Nahrát partnery i s logy**, když dodá originály a rozdělení do sekcí.
- **Doplnit adresy k místům**, kde chybí (dnes má většina jen město).
- **Založit ročník 2027** a nastavit, co se má na úvodu ukazovat mimo festivalový týden.
- **Opravit návod** podle toho, kde zaváhá u kroku 3.

---

## Rozhodnutí

Všechna jsou jeho — rozhoduje i vykonává. Seřazeno podle toho, co ho stojí nejvíc.
Nenos tam seznam padesáti otázek z [01-otazky-na-schuzku.md](01-otazky-na-schuzku.md).

- [ ] **Rezervace: 27 hodin mojí práce, ale hlavně tvoje starost navždy.** Zruší si rezervaci
      návštěvník sám odkazem v e-mailu (~15 h křehkého kódu), nebo stačí, že ji zruší pořadatel
      v seznamu (zdarma)? A poznámky u rezervací — měl je někdo v 2026 zapnuté a četl je?
      Když nula, ušetří se 12 h. **Pro něj to není jen cenovka: co z toho bude během festivalu
      ručně obsluhovat?**
- [ ] **Texty pro Pro návštěvníky, Přístupnost a Dopravu — do kdy je napíše?** Tohle za něj
      nikdo neudělá a je to nejčastější důvod skluzu. Chtěj datum, ne „zařídím".
- [ ] **Kdo je druhý člověk s přístupem?** Dnes je jediný, kdo web umí. Když onemocní
      festivalový týden, nikdo nepřidá změnu v programu. Stačí druhý účet a hodina ukázky.
- [ ] **Účty a doména.** Doména, hosting i databáze visí na osobním Gmailu
      `webmajales@gmail.com`. Když ten účet zmizí, nepůjde zaplatit fakturu ani prodloužit
      doménu — a pak je jedno, kdo má administrátora WordPressu. Nejlevnější pojistka:
      **záloha mimo Wedos** a **připomínka na prodloužení domény v kalendáři spolku**,
      ne v e-mailu jedné osoby. Podrobně v [00-analyza.md](00-analyza.md), kapitola Role a účty.
- [ ] **Rubriky: 9, nebo 6?** V datech 2026 jich je 9 (navíc Vnitřní scéna, Volba krále, Online),
      zadání mluví o 6. **Každá rubrika navíc je rozhodnutí u každé akce, kterou zadá.**
      Méně rubrik = méně práce každý rok.
- [ ] **Rozpad složených žánrů.** `punk/rock` je rozpadlý na dva, takže akce ponese víc žánrů.
      Plus sloučení `nu metal → rock`, `post punk → punk`, `bedroom rap → Rap`,
      `electronica → elektronika`. Týká se každého zadání akce, proto to chce jeho ano.
- [ ] **Bezbariérovost u místa, ne u akce.** 28 míst se vyplní jednou a platí napořád, jinak
      je to zaškrtnutí u každého vystoupení každý rok. Výjimky půjdou nastavit u akce.
      Tohle je čistá úspora jeho času — čekám rychlé ano.
- [ ] **Pole „Země původu"** se na webu nikde nezobrazuje. Používá se v tiskovinách nebo
      na sítích? Když ne, zruším ho a formulář akce bude o políčko kratší.
- [ ] **Loga partnerů v originále.** Na webu jsou zmenšeniny, některé jpg s bílým pozadím —
      na barevném pásu vypadají špatně. Má originály?
- [ ] **Font.** Web jede na **Archivu** z Google Fonts, lokálně hostovaném, místo Archivu
      od Ilyi Bazhanova z tištěné grafiky, u kterého se nepodařilo dohledat licenci. Zeptej se,
      odkud grafička Archiv vzala a jestli spolek má **webovou** licenci na GT Walsheim
      (Grilli Type licencuje podle počtu zobrazení). Když ani jedno, Archivo zůstává.

---

## Čím skončit

- [ ] **Jméno a kontakt správce webu do návodu.** V [06-navod-pro-tym.md](06-navod-pro-tym.md)
      je prázdná tabulka „Koho se zeptat". Nejspíš tam půjde on sám — tím spíš je potřeba
      doplnit i zástup.
- [ ] **Jedna aktualita, kterou napíše ještě tenhle týden.** Není na ní závislé žádné
      rozhodnutí, trvá deset minut a hned ji uvidí na úvodní stránce. Nejlepší první úkol,
      protože dokazuje celou smyčku.
- [ ] **Datum, kdy napíše texty tří stránek.**
- [ ] **Druhý účet** — jméno.

---

## Na co se tě nejspíš zeptá a co odpovědět

**„Kdy to bude hotové?"** Web je hotový na to, aby se plnil. Ostrý přechod doporučuju
podzim/zima 2026, ať je hotovo dřív, než se v březnu 2027 začne zadávat program.
Během festivalu se nemigruje.

**„Co to bude stát?"** Vývoj je pro-bono. Provoz zhruba 1 750 Kč ročně. Jediná otevřená
cenovka jsou rezervace — 27 hodin.

**„Kolik práce to bude pro mě?"** Program 2027 se zadává tak jako tak, ať už do starého
systému, nebo sem. Tady navíc odpadne správa webu a rebranding. Jednorázově ho čeká pročtení
návodu a texty tří stránek; všechno mechanické udělám já.

**„Co se stane s loňským programem?"** Zůstane zamrazený na archivní subdoméně, stejně jako
dnes funguje `2019.budejovickymajales.cz`. Migrovat 102 loňských akcí je archivní práce
s nulovou provozní hodnotou — ušetří ~25 hodin.

**„Proč na stránce Program není výpis akcí?"** Zkratka `[tribe_events]` je jen v placené verzi
The Events Calendar. Zatím tam je tlačítko do kalendáře. Až budou akce, rozhodneme se: koupit
Pro, nebo napsat vlastní blok. Dokud je kalendář prázdný, nespěchá to.

**„A ty filtry podle dne a kategorie z návrhu?"** Ve výchozích pohledech kalendáře jsou jen
zčásti. Dodělat je znamená sahat na šablony pluginu — rozbije se to při první aktualizaci
a je to přímo proti pravidlu „převzatelné za jeden večer". Nedoporučuju.
