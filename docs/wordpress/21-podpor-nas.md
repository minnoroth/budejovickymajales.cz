# 21. Podpoř nás

> **7. 11. 2026.** Zadavatel: *„Tohle je vlastně poslední stránka, která nemá
> nějaký Majálesový design… chce to nějaký fresh design, který navede
> návštěvníka co nejjednodušeji na přispění."* A ptal se, čím doplnit Donio.
> Návrh, ze kterého se vybíralo, je v artefaktu *Tři cesty, jak podpořit*.

---

## Tři cesty, seřazené

| Pořadí | Cesta | Pro koho | Co stojí |
|---|---|---|---|
| 1. | **QR platba** | kdo chce dát hned a neřešit nic | nic |
| 2. | **Donio** | kdo chce vidět příběh a cíl | provize platformy |
| 3. | **E-shop** | kdo chce něco za to | výroba zboží |

Nejrychlejší cesta je první a je **žlutá** — žlutá na tomhle webu znamená
„sem klikni". E-shop je pod nimi přes celou šířku: je to jiný druh podpory,
ne dar.

## QR platba

Česká norma (řetězec **SPAYD** v QR kódu), kterou umí přečíst každá bankovní
aplikace. Návštěvník namíří foťák, aplikace mu předvyplní číslo účtu, částku
i zprávu a on jen potvrdí — **tři doteky od „chci dát pětistovku" k hotovu.**
Nestojí to nic, nepotřebuje to žádnou službu třetí strany a funguje to
i vytištěné na plakátu.

Co QR naopak neumí: poděkovat, vést seznam dárců ani vystavit potvrzení
o daru. **Proto Donio nenahrazuje, doplňuje ho.**

**Účet:** 266541760/0300, převzatý ze stránky Kontakt a potvrzený zadavatelem.
IBAN pro SPAYD je `CZ2003000000000266541760`; kontrolní číslice ověřené
výpočtem. Zpráva pro příjemce je `DAR MAJALES`.

**Kódy jsou tři obrázky v médiích**, ne generování v prohlížeči — web se
obejde bez cizích knihoven a tahle stránka taky. Vyrábí je `qr-platba.py`
(potřebuje `segno`, stejně jako `oriznout-logo.py` potřebuje Pillow).
**Po změně účtu se musí spustit znovu.** Opakovaný běh je bezpečný: skript
staré obrázky nejdřív smaže, jinak by je WordPress nepřepsal a založil
by vedle nich kopie s příponou `-1`.

**Částky jsou 100, 300 a 500 Kč** (zadavatel 7. 11. 2026). Volba „jiná
částka" vypadla — kdo chce poslat jinou sumu, přepíše si ji v bankovnictví,
takže by to byl přepínač navíc za nic.

**Přepínání částek je na čistém CSS** (`radio` + `:checked` + `~`), ne na
JavaScriptu. Stránka tím pádem funguje i bez něj a nepřibyla žádná závislost.
Radio je schované, takže fokus z klávesnice musí být vidět na štítku — má
proto vlastní pravidlo.

> **Částky jsou na dvou místech.** `CASTKY` v `qr-platba.py` a ids
> `#bm-qr-100`, `#bm-qr-300`, `#bm-qr-500` v `bm.css`. Když se rozejdou,
> nevykreslí se **žádný** kód — selektor prostě nikoho nenajde.

### Ikonka ke zkopírování

Číslo účtu je záložní cesta pro toho, kdo se na stránku dívá z téhož
telefonu, kterým by skenoval. Opisovat šestnáct číslic z obrazovky je přesně
ten okamžik, kdy to člověk vzdá — proto je za číslem **malá ikonka, která po
kliknutí zezelená** (`palette11`, „Úspěch" z palety ročníku).

Dvě rozhodnutí, která stojí za přečtení:

- **Ikonku vyrábí až JavaScript** (`mu-plugins/kopirovat.js`), ne PHP. Kdyby
  byla v HTML a skript se nenačetl, zůstalo by na stránce tlačítko, které nic
  nedělá. Takhle se objeví jen tam, kde funguje — a kde ne, číslo se pořád
  označí jedním kliknutím (`user-select: all` v `bm.css`).
- **Kopíruje se dvěma cestami.** Nejdřív moderní `navigator.clipboard`,
  a když ho prohlížeč odmítne, zastaralý `execCommand('copy')` přes schované
  pole. Samotné moderní API nestačí: 7. 11. se ukázalo, že ve vestavěném
  prohlížeči Claude Code vrací `NotAllowedError`, takže tlačítko jen označilo
  text a vypadalo rozbitě. Stejně je na tom starší Safari a soukromé okno.
- **Kopíruje se ze schovaného pole, ne z rámečku na stránce.** V rámečku jsou
  kolem lomítka mezery kvůli čitelnosti; do bankovnictví patří číslo bez nich.
- **Zelená se zobrazí jen po skutečném zápisu do schránky.** Když selžou obě
  cesty, skript číslo aspoň označí a změní titulek. Zelená fajfka by tam lhala.
- **Ověřeno až do systémové schránky**, ne jen podle vzhledu tlačítka: po
  kliknutí vrátil `pbpaste` řetězec `266541760/0300`.

Do schránky jde číslo bez mezer kolem lomítka, ať se dá rovnou vložit do
bankovnictví.

### Na telefonu: „Otevřít v bance" neexistuje

Návrh sliboval na mobilu tlačítko, které **otevře bankovní aplikaci
s předvyplněnou platbou**. Takové tlačítko z webové stránky postavit nejde
a slib byl v návrhu dřív, než se ověřil:

- **SPAYD je norma jen pro obsah QR kódu, ne pro odkaz.** Banky ho čtou
  z kamery, ne z URL.
- **Jediná funkční cesta je Android Intent** s řetězcem v extra `spayd`
  a MIME `application/x-shortpaymentdescriptor`. Umí ji jen **nativní**
  aplikace — knihovna, která to dělá, si vyžaduje `Activity`.
- **Schéma `spayd://` je návrh jednoho pilotního projektu**, ne nic, co by
  české banky registrovaly.
- **Na iOS neexistuje ani to.** Řešení té knihovny na iOS je uložit QR
  do Fotek a načíst ho v bance z galerie.

**Takže se to dělá takhle:** na telefonu je pod kódem černé tlačítko
*Uložit kód do telefonu*. Kde prohlížeč umí sdílet soubory, otevře systémový
panel (tam je „Uložit obrázek" i aplikace, které obrázky přijímají); kde ne,
obrázek se prostě stáhne. V bankovní aplikaci se pak načte QR z galerie —
a **částka i zpráva jsou předvyplněné**, což samotné číslo účtu neumí.

Tři detaily, na kterých to stojí:

- **Je to `<a download>`, ne `<button>`.** I kdyby obsluha kliknutí selhala,
  prohlížeč pořád umí obrázek stáhnout nebo otevřít. Tlačítko by neudělalo nic.
- **Odkaz se přepíná s částkou.** Skript poslouchá na `change` přepínače
  a mění `href` i jméno souboru, jinak by se ukládal pořád kód výchozích 300 Kč.
- **Zavření systémového panelu není chyba** (`AbortError`) a nesmí spustit
  náhradní stažení.

**Kód na telefonu zůstává vidět** — je to to, co se ukládá, a dá se naskenovat
z obrazovky druhým telefonem. Štítek druhého kroku se ale mění: na monitoru
*Namiř na kód foťák*, pod 560 px *Ulož kód a načti v bance*. Jsou to dva
`<span>`y a přepíná je jediné pravidlo; na telefonu by rada s foťákem radila
nemožné.

## Rozvržení: tři kroky

Kartička byla do 7. 11. 2026 hromada prvků: pět různě širokých bloků pod
sebou, kód až pod záložním textem a na monitoru čtvrtina plochy prázdná.
Zadavatel: *„je to úplně spřeházený přes sebe… má to být super přímočarý
a hezký — tohle je místo, kde lidi žádáme o jejich příspěvek."* Vybíralo se
ze tří variant (artefakt *Kartička QR platby*), vyhrály **očíslované kroky**:

1. **Vyber částku** — přepínač
2. **Namiř na kód foťák** / **Ulož kód a načti v bance** — kód a na telefonu
   tlačítko k uložení
3. **Nebo pošli převodem** — číslo účtu a zpráva

Co z toho plyne pro kód:

- **Radia musí zůstat přímými sourozenci kroků.** Celý přepínač stojí na `~`,
  který přes úrovně nefunguje — zabalit je dovnitř kroku by ho vypnulo.
  Selektory proto míří `#bm-qr-300:checked ~ .bm-krok .bm-qr__castky …`.
- **Na telefonu má každý blok jednu šířku**, tedy celou šířku kartičky:
  štítky, kód, tlačítko i rámeček s účtem. Ověřeno na 375 px: všechny čtyři
  měří 277 px. Přesně tohle na starém stavu vadilo.
- **Štítky částek jsou mřížka s `grid-auto-flow: column`**, ne pevné tři
  sloupce — přidáním čtvrté částky se nic nerozbije.
- **Kolečko s číslem sedí na prvním řádku štítku**, ne na středu zalomeného:
  štítek má `line-height: 20px`, stejně vysoko jako kolečko.

**Na monitoru je kartička kompaktní** — kód 180 px, rámeček s účtem podle
obsahu, žádný text vedle kódu (zadavatel ho škrtl). Kartička má proto strop
360 px; bez něj by měla 550 px a obsah 200, tedy pruh prázdné žluté.
**Donio a e-shop jdou vedle ní pod sebe**, protože obě jsou nízké a kód je
vysoký: tři kartičky v jedné řadě nechávaly pod Doniem díru.

> **QR kód posílá peníze a tenhle web ho neumí ověřit.** Po každé změně účtu
> ho někdo musí načíst bankovní aplikací a zkontrolovat, že sedí číslo účtu
> i částka.

## Proč ne vložený iframe Donia

Zadavatel se ptal, jestli iframe zahodit. **Ano**, ze čtyř důvodů, z nichž
tři platí bez ohledu na to, co Donio nabízí:

1. **Nejde nastylovat.** Byla by to jediná část webu, která nevypadá jako
   Majáles — a zrovna ta, na které záleží nejvíc.
2. **Zpomalí stránku.** Cizí skripty a styly navíc, na mobilu znát.
3. **Je to cizí kód na stránce s penězi.** Co se v něm změní, se nikoho neptá.
4. Staré vložení mělo natvrdo výšku 4000 px a na mobilu bylo rozbité. Jestli
   dnes Donio nabízí rozumnější vložení, se ověřit nepovedlo — dokumentace
   k jejich widgetu se nenašla.

Tím se uzavírá otevřená otázka z
[13-struktura-a-program.md](13-struktura-a-program.md) („Donio jako iframe ne…
**Ověřit**").

## Co jsme zvážili a nepoužili

- **Platební brána** (GoPay, Comgate) — smlouva, provize a účetnictví na webu,
  který každý rok přebírá někdo jiný. Přesně ta položka, kterou jednou nikdo
  neprodlouží.
- **Darujme.cz** — dává smysl, až bude mít festival pravidelné dárce. Dnes je
  to další účet navíc.

## Kde to žije

| Soubor | Co dělá |
|---|---|
| `wordpress/vzhled/qr-platba.py` | vyrobí QR kódy a nahraje je do médií |
| `wordpress/vzhled/podpor-nas.py` | složí obsah stránky |
| `wordpress/vzhled/bm.css`, sekce *Podpoř nás* | kartičky a přepínač částek |
| `wordpress/mu-plugins/bm-kopirovat.php` | zařadí skript na stránce s číslem účtu |
| `wordpress/mu-plugins/kopirovat.js` | vyrobí ikonku a kopíruje do schránky |
| `wordpress/mu-plugins/bm-sdilet-qr.php` | zařadí skript na stránce s QR kódem |
| `wordpress/mu-plugins/sdilet-qr.js` | uloží nebo sdílí kód vybrané částky |

## Co zůstává otevřené

- **Odkaz na Donio míří na sbírku 23. ročníku.** Až bude sbírka na 2027,
  přepsat v `podpor-nas.py`.
- **Pruh „na co to jde"** (kolik stojí plakáty, ozvučení, honorář) byl
  v návrhu a na stránce není — ta čísla musí dodat tým a vymyslet si je
  nejde. Až budou, je to deset minut.
- **Variabilní symbol.** Dnes dary pozná účetní jen podle zprávy `DAR`.
  Jestli chce VS, dá se do SPAYD doplnit.
- **Načtení QR z galerie v bankovních aplikacích** je ověřené jen z popisu
  knihovny `pay-via-bank-app`, ne klikáním. Až to někdo zkusí ve své bance,
  stojí za to sem poznamenat, kde to je a kde ne.
