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

**Řešení.** Zkratka `[bm_program]` vykreslí program ročníku. Má vlastní složku, protože
vedle PHP potřebuje ještě dva skripty a styl. Celá logika je v JavaScriptu
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

## bm-kralove/ — průběžné pořadí králů

**Problém.** Body kandidáta se zadávají po disciplínách (opakovatelné pole **Body**), ale
všude se pracuje s jejich součtem. WordPress opakovatelná pole sčítat neumí a žádná
zkratka na výpis kandidátů neexistuje. Stránka `/kralove/` byla prázdná, přestože na ni
z úvodu vede korálový pás s tlačítkem **Průběžné pořadí**.

**Řešení.** Zkratka `[bm_kralove]` vypíše žebříček: pořadí, fotka, jméno, škola, téma,
pruh v poměru k vedoucímu a součet bodů. Pruh je **dělený po disciplínách** — každá má
svou barvu a nad výpisem je legenda, takže je vidět, za co body jsou. Vedle toho
přidává do administrace řaditelný sloupec **Body celkem**.

**Jak se skládá délka pruhu.** Celý pruh je poměr bodů kandidáta k vedoucímu, dílky
uvnitř jsou poměry disciplín k bodům toho kandidáta. Složené dohromady to vyjde: dílek
zabírá na obrazovce stejnou část jako jeho podíl na nejvyšším součtu. Barvy nejsou
v PHP, ale v CSS podle atributu `data-segment` — jinak by je rebranding nepřebarvil.

**Proč jsou čísla pod pruhem, a ne v něm.** Marketing chtěl vidět přesné body za každou
disciplínu, nejlépe tooltipem po najetí nebo po klepnutí. Měření na skutečném rozložení
bodů ročníku 2024 ukázalo, že to nejde: od pátého místa níž je dílek pruhu na mobilu
užší než 13 px, u šesti kandidátů z dvaceti je nulový. Doporučená nejmenší plocha na
dotek je 44 px. Čísla se proto vypisují natrvalo pod pruhem, kde je nemusí nikdo hledat
ani se do nich trefovat. Zvážené varianty jsou v
[16-body-po-disciplinach.md](../../docs/wordpress/16-body-po-disciplinach.md).

**Proč se součet počítá při každém čtení.** Uložené odvozené číslo se dřív nebo později
rozejde se skutečností. Součet se proto nikdy nebere z databáze — vždycky se spočítá
z bodů. Uložená kopie `_bm_body_celkem` slouží jedinému účelu: řazení sloupce
v administraci, protože podle spočítané hodnoty WordPress řadit neumí. Kdyby se kopie
rozešla, pozná se to jen na pořadí řádků v administraci; vypsané číslo i veřejný
žebříček jsou vždycky správně.

**Proč je kopie napojená na zápis meta, a ne na `acf/save_post`.** Secure Custom Fields
pouští `acf/save_post` jen u uložení z formuláře nebo z editoru. Zápis přes REST API
(import, skript, jiný doplněk) ho nespustí — píše to komentář přímo ve zdrojáku SCF:
*„updates fields without firing `acf/save_post`"*. Zato každý z těch zápisů jde přes
`update_metadata()`, a na tu je doplněk napojený. Je to jediná cesta, která nejde obejít.

**Kotvy.** Školy a Instagram odkazují na `/kralove#gymji`. Kotva se skládá z názvu školy
přes `sanitize_title()` (bez diakritiky, malá písmena). Při shodě škol se čísluje
(`gymji`, `gymji-2`) — na starém webu mělo šest kandidátů školu `t` a sdíleli jednu
kotvu, takže odkaz skočil na náhodného z nich.

**Který ročník se vypíše.** Nejnovější, který má aspoň jednoho kandidáta — ne prostě
nejnovější ročník. Jinak by stránka byla od ledna do března prázdná. Rok se proto
vypisuje nad výpisem.

**Proč to nerozbije aktualizace.** Čte post meta přímo přes jádro WordPressu
(`get_post_meta`), ne přes funkce Secure Custom Fields. Kdyby se plugin s poli jednou
vyměnil, součet funguje dál — tvar uložení (`body` = počet řádků, `body_0_pocet` = první
řádek) je daný databází, ne pluginem.

**Jak ověřit, že žije.** Načti `/kralove/` — musí být vidět žebříček s pruhy, ne věta
o tom, že se kandidáti teprve sbírají. Logiku součtu, řazení a kotev ověří bez
WordPressu i bez databáze `bm-kralove/overeni.php`:

```bash
cd wordpress/mu-plugins/bm-kralove
docker run --rm -v "$PWD":/t -w /t php:8.2-cli php overeni.php
```
## bm-carousel/ — zvýrazněné akce na úvodu

**Problém.** Sekce „Nepřehlédni" na úvodní stránce byly tři **natvrdo napsané
kartičky** v `wordpress/vzhled/uvod.py`. Nebyla to data — byl to text, který někdo
přepsal rukou z programu. Když se akce zrušila nebo přesunula, úvodní stránka o tom
nevěděla. Totéž u dlaždic „Co tě zajímá?": počty akcí byly napsané `43 / 22 / 14 / 7`,
zatímco skutečnost byla `42 / 18 / 7 / 7`.

**Řešení.** Zkratka `[bm_carousel]` složí pruh kartiček z akcí označených **Top
představení**, seřazených podle data, nejvýš osm. Zkratka
`[bm_pocet_akci kategorie=hudba]` vypíše skutečný počet akcí v kategorii.

**Proč PHP, a ne JavaScript jako program.** Carousel je první věc pod hlavičkou. Kdyby
se dokresloval z REST API, byla by nejdůležitější část úvodu při načtení prázdná
a vyhledávač by v ní neviděl nic.

Cena: kartička akce existuje dvakrát — jednou v PHP tady, jednou v JavaScriptu
v `bm-program`. Je to vědomá duplicita. Značkování je proto schválně stejné
(`.bm-karta` z `bm.css`), aby se vzhled nerozcházel na třech místech.

**Proč nativní „Top představení", a ne vlastní pole.** Byly na to dva přepínače —
nativní *Feature Event* z The Events Calendar, který čte program, a pole *V carouselu*,
které nečetlo nic. Dvě zaškrtávátka se stejným významem, z nichž jedno je tiše mrtvé,
je přesně ta věc, na které se někdo při předávání webu zasekne.

Pole *V carouselu* je **smazané** (5. 11. 2026) i s uloženými hodnotami u akcí.
Skupiny polí drží Secure Custom Fields jako interní typ `acf-field-group` registrovaný
s `show_in_rest => false`, takže se na ně zvenčí nedá sáhnout — smazal to jednorázový
doplněk v `mu-plugins/`, který se hned po doběhnutí odstranil.

**Nadpis je uvnitř zkratky**, ne ve stránce. Když není co ukázat, zmizí celá sekce —
holý nadpis nad prázdnem vypadá jako rozbitá stránka.

**Barvy kategorií** nese atribut `data-kat` a tabulka v `bm.css`. V tomhle doplňku
schválně nejsou, byla by to třetí kopie téže tabulky.

**Proč to nerozbije aktualizace.** Nevolá jedinou PHP funkci The Events Calendar. Čte
jen post meta přes jádro WordPressu: `_tribe_featured` (ověřeno ve zdrojáku TEC,
`src/Tribe/Featured_Events.php`), `_EventStartDate` a `_EventVenueID`.

**Jak ověřit, že žije.** Zaškrtni u akce v termínu ročníku **Top představení** a načti
úvodní stránku — kartička se musí objevit. Když nemá žádná akce příznak, sekce
„Nepřehlédni" na úvodu vůbec není, a je to tak správně.

## bm-pozadi.php — fotka na pozadí úvodu

**Problém.** Úvodní pás byl jednobarevně zelený. Zadavatel chtěl mít možnost dát za něj
fotku přes celé pozadí (vzor: korzonarodni.cz), ale tak, aby ji šlo vyměnit bez sahání
do kódu.

**Řešení.** Náhledový obrázek stránky **Úvod** se použije jako pozadí pásu. Žádné nové
políčko — je to stejné ovládání, jakým se dává fotka ke králi nebo k akci. Nahrát fotku
znamená nastavit náhledový obrázek, zrušit ji znamená ho odebrat.

**Proč přes tmavý přechod.** Text v pásu je bílý a dopředu se neví, jaká fotka tam bude.
Přechod drží čitelnost u jakékoli fotky a horní část obrázku nechá skoro nedotčenou.

**Proč zelená zůstává pod fotkou.** Než se obrázek stáhne — a když se nestáhne vůbec —
musí být pás čitelný. Barva z palety je proto dál pozadím a fotka leží na ní.

**Jak ověřit, že žije.** Nastav u stránky Úvod náhledový obrázek a načti úvodní stránku.
Když ho odebereš, pás se vrátí k zelené.
