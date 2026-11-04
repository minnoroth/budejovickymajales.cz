# 08 — Vzhled na stagingu

Stav k 26. 10. 2026. Návrh podle grafiky ročníku 2024 je překlopený do WordPressu
na <https://novy.budejovickymajales.cz>. Dokument říká, **kde se co mění** — a hlavně
co se dá naklikat bez vývojáře.

## Pravidlo, na kterém vzhled stojí

Barvy se nikde nepíšou natvrdo. Všechno jede přes **globální paletu Kadence** a CSS
na ni ukazuje jen přes `var(--global-paletteN)`.

Rebranding na nový ročník je proto **přepsání patnácti polí v Customizeru**, ne
přepisování stylů. Když přijde nová grafika na konci roku 2026, tohle je celý zásah.

| Slot | Barva 2024 | K čemu je |
|---|---|---|
| 1 | `#1EA198` zelenomodrá | hlavička, hero, tlačítka, odkazy |
| 2 | `#0C6F69` tmavší | odkaz při najetí myší |
| 3–6 | šedé | text od nejsilnějšího po linky |
| 7–9 | světlé | pozadí a bílá |
| 10 | `#EE7270` korálová | druhý barevný pás (Volba krále) |
| 11–14 | — | systémové stavy (úspěch, chyba…), vzhled se jich netýká |
| 15 | `#F8E862` žlutá | akcent — tlačítka, běžící pás, aktivní položka menu |

Levandulová `#B6B4DA` se do palety nevešla a bydlí jako proměnná `--bm-levandulova`
v doplňkovém CSS. Je to jediná výjimka z pravidla a je tam napsaná proč.

## Kde se co nastavuje

| Co | Kde v administraci |
|---|---|
| Barvy | Vzhled → Přizpůsobit → **Barvy** → Globální paleta |
| Velikost nadpisů | Vzhled → Přizpůsobit → Typografie → **Nadpisy** |
| Logo a název v hlavičce | Vzhled → Přizpůsobit → **Hlavička** → Logo |
| Barva hlavičky | Hlavička → Hlavní řádek → Pozadí |
| Tlačítko E-shop | Hlavička → **Tlačítko** (popisek, odkaz, barva) |
| Menu | Vzhled → **Menu** → Hlavní menu |
| Titulkový pás stránek | Přizpůsobit → **Stránky** → Titulek stránky |
| Patička | Přizpůsobit → **Patička** → Copyright |
| Doplňkové CSS | Přizpůsobit → **Doplňkové CSS** |

## Písmo

**Archivo** z Google Fonts (licence OFL), **hostované lokálně** — ověřeno, že stránka
nedělá žádný požadavek na `fonts.googleapis.com`. To je rozdíl proti starému webu,
kde se Space Mono tahal z Google CDN, což je problém s GDPR.

Stažené řezy: 400, 700 a **800**. Osmistovku Kadence stáhne samo, protože ji
používají nadpisy — kdyby se váha nadpisů snížila, řez z serveru zase zmizí.

Archivo je **náhrada** za font Archiv od Ilyi Bazhanova z tištěné grafiky, u kterého
se nepodařilo dohledat licenci. Podrobnosti v [`wordpress/branding/README.md`](../../wordpress/branding/README.md).

## Logo

Tiskové logo má kolem znaku široký průhledný rám, kvůli kterému se v hlavičce
scvrkne na polovinu výšky řádku. V médiích je proto **oříznutá varianta**:

| Soubor | Kde se používá |
|---|---|
| `majales-znak-bile.png` | hlavička (na zelenomodré) a patička (na tmavé) |
| `majales-znak-cerne.png` | rezerva pro světlá pozadí |

Ořez dělá [`wordpress/vzhled/oriznout-logo.py`](../../wordpress/vzhled/oriznout-logo.py).

Znak nemá nápis, proto je v hlavičce vedle něj **název webu jako text** — odchylka
od návrhu, kde byl plný logotyp. Až bude logotyp k dispozici, stačí ho nahrát
jako logo a v Hlavička → Logo přepnout rozvržení na „jen logo".

## Co je hotové

- **Úvod** — hero, běžící pás, tři kartičky „Nepřehlédni", čtyři dlaždice kategorií,
  korálový pás Volba krále, Aktuality (napojené na příspěvky) a levandulový pás partnerů.
- **Program** — zelenomodrý pás s titulkem a odkaz do kalendáře akcí.
- **Menu** — Program / Králové / Partneři / Praktické info / O festivalu / Aktuality
  s podpoložkami, plus žluté tlačítko na e-shop.
- **Titulkový pás** na všech ostatních stránkách a u příspěvků.
- **Patička** — tmavá, se znakem a jednou větou.
- **Kalendář akcí** — barvy pluginu dotažené na paletu ročníku a od 4. 11. 2026
  i celý vzhled výpisů: seznam, měsíc, den a detail akce. Je v samostatném
  [`bm-akce.css`](../../wordpress/vzhled/bm-akce.css) a popisuje ho
  [10-vypis-akci-navrh.md](10-vypis-akci-navrh.md).

## Co hotové není

**Výpis akcí na stránce Program.** Zkratka `[tribe_events]` je jen v placené verzi
The Events Calendar, takže stránka na kalendář zatím jen odkazuje tlačítkem.
Možnosti: koupit Events Calendar Pro, nebo napsat vlastní blok. Dokud nejsou
nahrané akce, nespěchá to.

**Filtry programu z návrhu** (den, kategorie, místo, bezbariérovost) jsou ve
výchozích pohledech kalendáře jen částečně. Dodělávat je znamená sahat na šablony
pluginu — to se rozbije při první aktualizaci a odporuje pravidlu „převzatelné
za jeden večer". Nedoporučuju.

**Dlouhé řádky na textových stránkách.** Obsah má šířku 1160 px, což je na souvislý
text moc. Jednotlivou stránku jde přepnout na užší rozvržení v jejím nastavení vpravo.

**Prázdné sekce.** „Nepřehlédni" a dlaždice kategorií mají zatím vymyšlená data
z ročníku 2024 jako ukázku stylu. Až budou v kalendáři skutečné akce, nahradí se.

## Jak to postavit znovu

Skripty v [`wordpress/vzhled/`](../../wordpress/vzhled/) staví celý vzhled od nuly.
Čekají cestu k souboru s přihlašovacími údaji v proměnné `BM_WP`
(údaje do repozitáře nepatří — repozitář je veřejný).

```bash
export BM_WP=~/bm-wordpress/staging/wp.txt
cd wordpress/vzhled
python3 nastaveni.py    # paleta, písmo, hlavička, patička, titulky, CSS
python3 logo.py         # znaky do médií, logo hlavičky, text patičky
python3 menu.py         # hlavní menu (jen jednou, jinak vznikne druhé)
python3 uvod.py         # obsah úvodní stránky
python3 program.py      # obsah stránky Program
```

Soubor s údaji má tvar `klíč = hodnota` a potřebuje `web`, `user`, `pass`
(aplikační heslo z Uživatelé → Profil) a `adminpass` (jen pro Customizer).

Zdrojem pravdy pro doplňkové CSS jsou **dva** soubory, které `nastaveni.py` spojí
v tomhle pořadí (konstanta `CSS_SOUBORY`): [`bm.css`](../../wordpress/vzhled/bm.css)
a za ním [`bm-akce.css`](../../wordpress/vzhled/bm-akce.css) s kalendářem.
Když je někdo upraví přímo v administraci, nezapomeňte změnu vrátit i sem —
jinak příští běh `nastaveni.py` úpravu přepíše.

## Dvě pasti, na které jsem narazil

**Customizer chce plochou mapu.** `customize_save` očekává
`customized={"id": hodnota}`. Obalení do `{"value": …}` shodí Kadence na
`wp_kses(Array)` a vrátí HTTP 500 bez vysvětlení.

**WordPress centruje obsah přes `!important`.** Rozvržení „constrained" dává každému
bloku `margin-left: auto !important`. Zúžený odstavec se proto bez `!important`
nedá ukotvit vlevo a uteče doprostřed — v `bm.css` je to u příslušného pravidla
poznamenané.
