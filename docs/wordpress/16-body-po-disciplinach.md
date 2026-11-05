# Přesné body po disciplínách v žebříčku králů — analýza

Zadání od zadavatele (5. 11. 2026): *„rozdělení bodů je super, že je vidět, ale není tam
vidět, kolik je přesně bodů za jakou kategorii. Teoreticky kdyby se to dalo např.
zobrazit tooltipem při najetí myší, nebo kliknutí na mobilu, bylo by to super."*

---

## Shrnutí na tři věty

Na počítači **tooltip už existuje** — každý dílek pruhu má `title`, takže se po najetí
myší ukáže „Studentské jamy: 2 100 b.". Na mobilu to ale nejde dodělat tak, jak si to
zadavatel představuje: **u většiny kandidátů je dílek široký jednotky pixelů** a na
takový cíl se prstem netrefí nikdo. Doporučuju proto čísla nevázat na najetí ani na
klik a vypsat je rovnou pod pruh.

---

## 1. Co už funguje dnes

Každý dílek má atribut `title` s názvem disciplíny a počtem bodů. Prohlížeč z něj udělá
systémový tooltip. Platí u toho tři věci:

- objeví se **až po zhruba sekundě** nečinnosti myši,
- vypadá, jak si určí operační systém — s vzhledem webu se nedá nic dělat,
- **na dotykovém displeji se nezobrazí vůbec**, protože tam není nad čím stát.

Rozpis je navíc v textu pro odečítače obrazovky (`.bmk-jen-pro-ctecku`), takže pro
nevidomé návštěvníky je ta informace dostupná už teď.

## 2. Měření: jak široký ten dílek doopravdy je

Tohle je jádro celé analýzy. Vzal jsem **skutečná data z dnešního webu** — dvacet
kandidátů ročníku 2024 — a spočítal, jak široký by byl jeden ze čtyř dílků.

Rozložení bodů je hodně nerovnoměrné:

| Pořadí | Body | Podíl k vedoucímu |
|---|---|---|
| 1.–4. | 5 898 – 5 174 | 100 – 88 % |
| 5. | 2 403 | 41 % |
| 6.–14. | 1 904 – 939 | 32 – 16 % |
| 15.–20. | 0 | 0 % |

Šest kandidátů z dvaceti má **nulu**, takže nemají jediný dílek a není na co najet.

Šířka jednoho ze čtyř dílků při dnešním rozložení:

| Pořadí | Počítač (okno 1280) | Mobil (375) |
|---|---|---|
| 1.–4. | 194 – 221 px | 28 – 32 px |
| 5. | 90 px | 13 px |
| 9. | 57 px | 8 px |
| 14. | 35 px | **5 px** |
| 15.–20. | 0 px | 0 px |

**Na mobilu je od pátého místa níž dílek užší než 13 px.** Doporučená nejmenší plocha
na dotek je 44 px (Apple) nebo 48 px (Google). Nejsme blízko — jsme o řád vedle.

> Jinými slovy: tooltip na klik by fungoval pro první čtyři kandidáty a pro zbytek
> startovního pole ne. To je horší než ho nemít, protože nefunguje nahodile.

## 3. Možnosti

### A — Nechat jen systémový `title` (dnešní stav)
Nula práce. Na počítači to jde, na mobilu ne. Marketingovou připomínku neřeší.

### B — Vlastní tooltip v CSS na `:hover` a `:focus`
Asi 25 řádků CSS, žádný JavaScript. Vypadá podle webu, objeví se hned, funguje
i z klávesnice. **Na mobilu pořád nic** — problém s 5 px zůstává.

### C — Tooltip v JavaScriptu s podporou kliknutí ⚠️
Přesně to, co zadavatel navrhl. Naráží na měření výš: cíl o šířce 5 px. Dalo by se to
obejít tím, že by se klepalo na celý pruh a ukázaly by se všechny čtyři hodnoty naráz —
jenže to už není tooltip nad dílkem, to je varianta E. Navíc by to byl první JavaScript
v doplňku, který je dnes celý na serveru.

### D — Vypsat čísla natrvalo pod pruh ⭐
Pod pruhem řádek s barevnými tečkami a čísly:

```
● 2 100   ● 1 800   ● 1 500   ● 498
```

Názvy disciplín nese legenda nad výpisem, takže se nemusí opakovat u každého jména.

- funguje **všude stejně** — myš, dotek, klávesnice, odečítač
- **nic se nemusí objevit**, takže není co hledat ani čím překvapit
- žádný JavaScript, asi 20 řádků CSS a pět řádků PHP
- odolné vůči tomu 5px problému, protože se na nic netrefuje

Cena: jeden řádek navíc u každého kandidáta. Odhad šířky při čtyřech disciplínách je
zhruba 180 px, takže se i na telefonu vejde na jednu řádku a karta se nezvýší.

### E — Rozbalit na vyžádání (`<details>`)
Pod každým kandidátem ovládátko „Rozpis bodů", které rozbalí čísla. Nativní HTML, bez
JavaScriptu, funguje na dotek i z klávesnice, cíl je přes celou šířku karty.

Je to stejný vzor, jaký má dostat stránka s častými otázkami, takže by se web
neučil druhý jazyk. Proti: dvacet rozbalovátek pod sebou je vizuální šum a informace
zůstává schovaná — marketing ji chce vidět.

### F — Čísla přímo do dílků
Hezké u prvních čtyř, nečitelné od pátého a na mobilu nepoužitelné. Zmiňuju jen proto,
aby bylo jasné, že to bylo zvážené.

## 4. Doporučení

**Varianta D**, a ponechat systémový `title` jako bonus na počítači — pojmenuje
disciplínu, zatímco tečka u čísla ji jen obarví.

Důvod je ten, že osmdesát procent návštěv chodí z mobilu a každá varianta postavená na
najetí nebo na trefě do dílku tam selže právě u těch kandidátů, kterých je nejvíc.
Trvale vypsané číslo je nudnější, ale je vidět vždy a u všech.

Odhad: **1,5 hodiny** i s úpravou návodu a ověřením na obou šířkách.

## 5. Co se musí ověřit při implementaci

| Věc | Proč |
|---|---|
| Šířka řádku na 320 px | Nejužší telefony. Když se čísla zalomí, karta povyroste — ověřit, že se to nerozsype. |
| Kandidát s nulou | Nemá disciplíny; řádek s čísly se nesmí vykreslit prázdný. |
| Jedna disciplína | Nejčastější případ na začátku ročníku — jedno číslo vedle jedné tečky nesmí vypadat jako chyba. |
| Dlouhá čísla | Pět číslic plus mezera (`12 345`) u všech čtyř disciplín je nejhorší případ. |
| Odečítač obrazovky | Skrytý rozpis `.bmk-jen-pro-ctecku` by se zdvojil — při vypsání čísel ho odebrat. |

## 6. Otázka na zadavatele

Varianta D vypisuje **jen čísla** a spoléhá na legendu nad výpisem. Druhá možnost je
psát i zkrácené názvy (`jamy 2 100 · hra 1 800 · výzvy 1 500 · ostatní 498`) — je to
srozumitelnější bez očního kontaktu s legendou, ale na mobilu se to zalomí na dva až
tři řádky a dvacet karet tím povyroste zhruba o tisíc pixelů scrollování.
