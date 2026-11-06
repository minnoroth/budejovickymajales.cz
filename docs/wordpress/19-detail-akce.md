# 19. Detail akce přímo v programu

> **6. 11. 2026.** Zadavatel: *„Vadí mi tam ten proklik navíc na events.
> Šel by udělat plnohodnotný detail akce i s rezervací?"*
>
> Odpověď je ano a je rozdělená na dvě části. **Část A je hotová** a je
> v tomhle commitu. **Část B čeká na SMTP** — na tu samou bránu, na které
> stojí [`bm-rezervace.php`](../../wordpress/mu-plugins/bm-rezervace.php).

---

## Co modál uměl a co umí teď

Modál v programu existoval už předtím, jen byl vyhladovělý.

| | Dřív | Teď |
|---|---|---|
| Popis | výřez useknutý v půlce věty | celý text i s formátováním |
| Místo | jméno v řádku s časem | vlastní blok s městem a odkazem do map |
| Rezervace | štítek „Rezervace" a nic víc | kolik zbývá míst a dokdy jde rezervovat |
| Kalendář | nebyl | soubor `.ics` jedním klepnutím |
| Adresa | neměnila se | `?akce=<id>`, dá se poslat odkazem |
| Mobil | stejné pořadí jako na monitoru | rezervace hned pod nadpisem |

## Celý popis nestál ani jeden dotaz navíc

Nejdražší věc na programu jsou dotazy na Wedos — každý stojí přes vteřinu.
Celý popis akce přitom chodí **ve stejné odpovědi**, kterou program stahuje
kvůli časům a místům; dosud se zahazoval ve prospěch pole `excerpt`. Stejně
tak město místa. Část A tedy nepřidala k načtení stránky nic.

Jediný nový dotaz je stav rezervace a ten se posílá **až když se detail
otevře**, a jen u akce, která rezervaci má. Loni se to týkalo sedmnácti akcí
ze sta dvou. Odpověď se drží po dobu návštěvy, takže druhé otevření téhož
detailu nestojí nic — ověřeno, nula dotazů.

## Popis je jediné cizí HTML v programu

Celý zbytek programu prochází přes `esc()`. Popis ne — jinak by se na stránce
objevilo `<p>`. Pouští se proto přes výčet povolených značek v
`program-view.js`:

- **projde** `p br strong b em i ul ol li h3 h4 blockquote a`, u odkazu jediný
  atribut `href`, a to jen `http(s):`, `mailto:` nebo adresa na tomhle webu;
- **zmizí i s obsahem** `script style noscript template iframe object embed
  svg math` — co je uvnitř, je kód, ne text;
- **zmizí jen značka, text zůstane** u všeho ostatního (typicky `div`
  a `span` z editoru);
- prázdné odstavce, které po tom zbydou, se zahodí, ať v modálu nezůstanou
  nevysvětlené mezery.

Čistí se v `<template>`, který prohlížeč neparsuje do stránky — obrázek
v popisu si tedy nestáhne nic dřív, než projde výčtem.

**Ověřeno proti živému webu, ne jen v hlavě.** Do zkušební akce se dočasně
vložil popis se `<script>`, `<img onerror>`, `<iframe>`, `onclick`,
`onmouseover`, `style` a odkazem na `javascript:`. Na stránce se nespustilo
nic, zůstaly jen povolené značky a odkaz bez adresy. Popis se pak vrátil
na původní.

## Odkaz do map se skládá z názvu a města

Všech **28 míst má prázdnou ulici i souřadnice** — ověřeno 6. 11. 2026 na
`tribe/events/v1/venues`. Odkaz proto míří na vyhledávání Google Maps
s dotazem `<název>, <město>`, což u pojmenovaného místa v Budějovicích stačí.
Až tým adresy doplní, přidá se do dotazu jedno pole a odkaz se zpřesní sám.

## Rezervace: proč jen stav, a ne rovnou formulář

Formulář v modálu **jde udělat** a je ověřený, ne odhadnutý. Blok Event
Tickets se dá naklonovat do `<dialog>`, zavolat na něm
`tribe.tickets.rsvp.manager.setup()` a překreslí se až na formulář se jménem,
e-mailem a počtem hostů — je to ta samá cesta, kterou plugin používá sám po
každém svém AJAXu.

Přesto se to teď nenasazuje, ze dvou důvodů:

1. **SMTP.** Rezervace stojí na potvrzovacím e-mailu. Doména dnes nemá pro
   Wedos platný SPF ani DKIM, takže potvrzení nemusí dojít. Zvýraznit
   rezervaci v programu a nechat potvrzení padat do spamu je horší než dnešní
   proklik. Je to ta samá brána, kvůli které čeká i `bm-rezervace.php` —
   viz [12-rezervace-analyza.md](12-rezervace-analyza.md), etapa 0.
2. **Vazba na cizí plugin.** Doplněk programu si zakládá na tom, že sahá jen
   na veřejné REST endpointy. Formulář by vyžadoval dotáhnout na `/program/`
   JS i CSS Event Tickets a opřít se o jeho vnitřek. Je to obhajitelné, ale
   je to rozhodnutí navíc a nemá se dělat ve stejném kroku jako zbytek.

Do té doby vede z bloku rezervace tlačítko na stránku akce. **Proklik tedy
mizí u pěti akcí ze šesti hned a u zbytku s částí B.**

### Co se kde bere

| Co | Odkud | Kdy |
|---|---|---|
| popis, plakát, čas, kategorie, žánr | `tribe/events/v1/events` | už stažené |
| místo, město | `tribe/events/v1/events` | už stažené |
| bezbariérovost | `wp/v2/tribe_venue` | už stažené |
| odkaz do map, soubor `.ics` | skládá se v prohlížeči | — |
| kapacita, obsazenost, konec rezervací | `tribe/tickets/v1/tickets?post_id=` | při otevření detailu |

## Pět stavů rezervace

Volno · poslední místa (do tří zbývajících, štítek zčervená) · plno ·
rezervace skončily · bez rezervace (blok se vůbec nevykreslí). Čtyři z nich
umí část A — i bez formuláře návštěvník pozná, jestli má cenu se o místo
ucházet.

**`available_until` chodí bez časové zóny** („2027-05-11 19:00:00") — je to
pražský čas, ve kterém ho pořadatel zadal. Přes `Date()` se neprohání, jen se
poskládá na zobrazení; jinak by se čas v prohlížeči v jiné zóně posunul.

## Adresa modálu

Otevřený detail píše do adresy `?akce=<id>` (přes `replaceState`, aby
tlačítko zpět vedlo z programu pryč, ne postupně zavíralo detaily).
Program takovou adresu uměl otevřít už předtím, jen ji nikdo neuměl získat.
Zavření detailu parametr zase uklidí.

**Stránky `/event/…` zůstávají, ale z webu na ně nic nevede.** Jsou kanonické pro
vyhledávače — Google chce pro každou akci vlastní adresu a `?akce=437` je z jeho
pohledu pořád jedna a tatáž stránka — a drží odkazy, které už někde kolují.
Návštěvník na ně ale nenarazí: kartičky v „Nepřehlédni" na úvodu od 7. 11. 2026
míří na `/program/?akce=<id>`, do menu stránky akcí nikdy nepatřily a v modálu
na ně vede jen drobný odkaz dole. Výjimka trvá do části B: tlačítko „Rezervovat
místo" tam posílá, protože formulář zatím žije jen tam.

`/events/list|month|today/` zůstávají jako záchranná brzda — jsou to ty odkazy
v bloku, který se ukáže, když se program nevykreslí.

## Mobil

Šuplík odspodu program uměl už předtím, mění se dvě věci: nahoře přibyl
úchyt a **rezervace jde nad popis**. Na telefonu je to to jediné, kvůli čemu
se detail otevírá, a palec na něj musí dosáhnout bez rolování. Pořadí v HTML
zůstává logické kvůli čtečkám, přerovnává se `order` ve flexu.

## Odstupy drží jedna mezera, ne okraje bloků

Po prvním proklikání zadavatel upozornil, že v modálu něco chybí. Změřeno:
odstupy se skládaly z pěti různých `margin`-ů (6, 10, 0, 14, 18 px) a nově
přidaný blok s místem žádný neměl, takže **štítky na něm seděly bez mezery**.

Teď je to jedna vlastnost: `.bmp-detail__telo` je svislý flex s `gap: 16px`
a všechny bloky mají `margin: 0`. Čas, nadpis a štítky jsou jedna hlavička
(`.bmp-detail__hlava`) s vlastní, těsnější mezerou 8 px — patří k sobě.
Přidat do detailu další blok tím pádem nejde pokazit.

**Plakát má strop výšky 240 px.** V poměru 16:9 zabral na širokém modálu
přes polovinu jeho výšky a informace, kvůli kterým se detail otevírá, spadly
pod okraj.

## Co zůstává otevřené

- **Část B — formulář rezervace v modálu.** Čeká na SMTP (etapa 0).
- **Adresy míst.** Dokud nejsou vyplněné, odkaz do map hledá podle jména.
- **Plakát je přes celou šířku modálu**, až k jeho rámečku. U běžné fotky to
  vypadá jako hlavička kartičky; u obrázku, který má vlastní bílé pozadí
  (třeba snímek obrazovky), splyne s rámečkem. Jestli má mít odsazení,
  je rozhodnutí pro zadavatele.
- **Ověřovací skript.** `overeni.mjs` prohání datovou vrstvu zmrazenými daty
  prototypu; `nactiRezervaci()` v nich není, protože potřebuje síť. Ověřuje
  se zatím ručně proti stagingu.
