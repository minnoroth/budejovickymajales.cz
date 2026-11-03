# Schůzka s content managerem — příprava

Druhá schůzka. První byla „co chcete a co to stojí". Tahle je jiná: **web stojí, je prázdný
a od teď je úzké hrdlo obsah, ne kód.** Tomu odpovídá i rozložení času.

Odškrtávej přímo tady — je to zápis ze schůzky.

---

## Stav, se kterým tam jdeš

| Co | Kolik |
|---|---|
| Místa konání | **27** (ve starých datech jich je 49) |
| Rubriky a žánry | **51 termínů** — čeká na pročištění |
| Ročníky | 1 |
| Akce | **0** |
| Králové | 0 |
| Aktuality | 0 |
| Uživatelé | **1 — jen ty** |

Vzhled, menu, role a návod hotové. Obsah ne. To je celé sdělení schůzky.

---

## Udělat před schůzkou

- [ ] **Založit mu účet.** Bez něj si nemůže nic zkusit a schůzka skončí u sledování tvojí
      obrazovky. Potřebuju jméno a e-mail, roli dej **Dramaturgie** (ne administrátora) —
      ať rovnou vidí, co ta role umí a neumí.
- [ ] **Poslat mu [06-navod-pro-tym.md](06-navod-pro-tym.md) předem.** Ne aby ho četl,
      ale aby věděl, že existuje. 944 řádků v češtině se screenshoty postupu.
- [ ] Mít otevřené dvě záložky: web a administraci, přihlášený.

---

## Co ukázat — 15 minut, v tomhle pořadí

1. **Úvodní stránka** <https://novy.budejovickymajales.cz> — ať vidí grafiku 2024 živou.
   Řekni rovnou, že kartičky a dlaždice jsou **ukázková data**, ne program.

2. **Ten jeden trik, kvůli kterému to celé dává smysl.** Vzhled → Přizpůsobit → Barvy →
   přepni slot 1 ze zelenomodré na cokoliv → náhled překreslí hlavičku, hero, tlačítka,
   rámečky kartiček i patičku naráz. Pak to zahoď bez uložení.
   **Tohle je ta věta „nová grafika na konci roku vás nebude stát vývojáře".**

3. **Ať si sám založí akci.** Ne předvádět — dát mu klávesnici. Akce → Přidat.
   Uvidíš za dvě minuty přesně to, co by jinak zjistil až v březnu sám.

4. **Co jeho role nevidí.** Žádné Pluginy, Vzhled, Uživatelé. Vysvětli proč:
   ne nedůvěra, ale aby omylem nerozbil něco, co neumí vrátit.

5. **Mobil.** Otevři web na telefonu. Většina návštěvníků festivalu je na mobilu.

---

## Co vysvětlit — tři věty, víc ne

**„Kritérium není hezký web, ale web, který za rok převezme cizí člověk za jeden večer."**
Proto WordPress a ne vlastní kód, proto čtyři pluginy a ne dvacet, proto se barvy mění
klikáním. Současný web je ukázka toho, co se stane, když se tohle deset let nedodržuje.

**„Co není v kalendáři, není na webu."** Program se nepíše do stránky, zadává se jako akce.
Stránka Program si ho vytáhne sama. Totéž králové, partneři, aktuality.

**„Od teď jsem na vás."** Kód je dál než obsah. Dokud nebudou místa, žánry a partneři,
nemá co ukazovat ani nejlepší šablona.

---

## Co z něj potřebuješ — osm rozhodnutí

Řazeno podle toho, co blokuje plnění obsahu. Nenos tam seznam z
[01-otazky-na-schuzku.md](01-otazky-na-schuzku.md), to je 50 otázek a utopí to schůzku.

### Blokuje obsah hned

- [ ] **Které rubriky jsou živé?** Zadání mluví o 6, v datech 2026 jich je 9 (navíc Vnitřní
      scéna, Volba krále, Online). Které jsou pozůstatek covidu a můžou zmizet?
      Bez toho se nedá pročistit 51 termínů ani dodělat dlaždice na úvodu.
- [ ] **Rozpad složených žánrů.** `punk/rock` je dnes rozpadlý na dva, takže akce ponese
      víc žánrů. Spolu s tím sloučení `nu metal → rock`, `post punk → punk`,
      `bedroom rap → Rap`, `electronica → elektronika`. Potvrdí to dramaturgie?
- [ ] **Bezbariérovost u místa, ne u akce.** 49 míst se vyplní jednou a platí napořád,
      jinak to je 102 zaškrtnutí každý rok. Výjimky půjdou nastavit u akce. OK?
- [ ] **Pole „Země původu"** se dnes na webu nikde nezobrazuje. Používá se v tiskovinách
      nebo na sítích, nebo ho zrušíme?
- [ ] **Loga partnerů v originále.** To, co je na webu, jsou zmenšeniny, některé jpg
      s bílým pozadím — na barevném pásu vypadají hrozně. Existují originály?
- [ ] **Kdo napíše texty pro Pro návštěvníky, Přístupnost a Dopravu a do kdy?**
      Tohle je nejčastější důvod skluzu. Chtěj jméno a datum, ne „zařídíme".

### Blokuje rozpočet, ne obsah

- [ ] **Rezervace: zruší si ji návštěvník sám, nebo stačí pořadatel v seznamu?**
      Pořadatel = zdarma. Návštěvník = ~15 h křehkého kódu. A **poznámky u rezervací**:
      kolik akcí je v 2026 reálně mělo zapnuté a četl je někdo? Když nula, ušetří se 12 h.
      Dohromady 27 hodin z ~50hodinového rozpočtu — ptej se na to konkrétně.
- [ ] **Font.** Web teď jede na **Archivu** z Google Fonts, lokálně hostovaném, místo
      Archivu od Ilyi Bazhanova z tištěné grafiky, u kterého se nepodařilo dohledat licenci.
      Ptej se na dvě věci: odkud grafička Archiv vzala (faktura, e-mail?) a jestli má spolek
      webovou licenci na GT Walsheim. Když ani jedno, Archivo zůstává a je to v pořádku.

---

## Co tam naopak neřešit

Hosting, doména, Google Workspace a IČO spolku **nejsou věc content managera**. Patří vedení.
Zmiň je jednou větou a požádej, ať to předá dál:

> „Doména, hosting i databáze visí na osobním Gmailu `webmajales@gmail.com`. Když ten účet
> zmizí, nepůjde zaplatit fakturu ani prodloužit doménu — a pak je jedno, kdo má administrátora
> WordPressu. Nejlevnější pojistka je **záloha mimo Wedos** a **připomínka na prodloužení
> domény v kalendáři spolku**, ne v e-mailu jedné osoby."

Podrobně v [00-analyza.md](00-analyza.md), kapitola Role a účty.

---

## Čím schůzku ukončit

- [ ] **Jméno správce webu do návodu.** V [06-navod-pro-tym.md](06-navod-pro-tym.md) je
      prázdná tabulka „Koho se zeptat" — jméno, telefon, kam psát dotazy, reakce
      o festivalovém víkendu. Bez toho je návod neúplný.
- [ ] **Jeden konkrétní úkol s datem.** Nejlepší je „dopln zbylých 22 míst konání" —
      je to mechanické, nepotřebuje to rozhodnutí a hned po tom bude program zadatelný.
- [ ] **Termín dvouhodinového školení** s lidmi, kteří obsah opravdu plní. Ne s vedením.

---

## Na co se tě nejspíš zeptá a co odpovědět

**„Kdy to bude hotové?"** Web je hotový na to, aby se plnil. Ostrý přechod doporučuju
podzim/zima 2026, ať je hotovo dřív, než se v březnu 2027 začne zadávat program.
Během festivalu se nemigruje.

**„Co se stane s loňským programem?"** Zůstane zamrazený na archivní subdoméně, stejně
jako už dnes funguje `2019.budejovickymajales.cz`. Migrovat 102 loňských akcí je archivní
práce s nulovou provozní hodnotou — ušetří ~25 hodin.

**„Proč na stránce Program není výpis akcí?"** Zkratka `[tribe_events]` je jen v placené
verzi The Events Calendar. Zatím tam je tlačítko do kalendáře. Až budou akce, rozhodneme se:
koupit Pro, nebo napsat vlastní blok. Dokud je kalendář prázdný, nespěchá to.

**„A ty filtry podle dne a kategorie z návrhu?"** Ve výchozích pohledech kalendáře jsou jen
zčásti. Dodělat je znamená sahat na šablony pluginu — rozbije se to při první aktualizaci
a je to přímo proti pravidlu „převzatelné za jeden večer". Nedoporučuju.
