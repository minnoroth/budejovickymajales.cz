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
