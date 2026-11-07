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

**Kódy jsou čtyři obrázky v médiích**, ne generování v prohlížeči — web se
obejde bez cizích knihoven a tahle stránka taky. Vyrábí je `qr-platba.py`
(potřebuje `segno`, stejně jako `oriznout-logo.py` potřebuje Pillow).
**Po změně účtu se musí spustit znovu.**

**Přepínání částek je na čistém CSS** (`radio` + `:checked` + `~`), ne na
JavaScriptu. Stránka tím pádem funguje i bez něj a nepřibyla žádná závislost.
Radio je schované, takže fokus z klávesnice musí být vidět na štítku — má
proto vlastní pravidlo.

**Na monitoru je kód první, na telefonu až za číslem účtu.** Telefon se
nevyfotí sám, takže tam je k něčemu to číslo, ne kód.

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

## Co zůstává otevřené

- **Odkaz na Donio míří na sbírku 23. ročníku.** Až bude sbírka na 2027,
  přepsat v `podpor-nas.py`.
- **Pruh „na co to jde"** (200 Kč plakáty, 500 Kč ozvučení, 1 000 Kč honorář)
  byl v návrhu a na stránce není — ta čísla musí dodat tým a vymyslet si je
  nejde. Až budou, je to deset minut.
- **Variabilní symbol.** Dnes dary pozná účetní jen podle zprávy `DAR`.
  Jestli chce VS, dá se do SPAYD doplnit.
