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

**Řešení.** Zkratka `[bm_program]` vykreslí program ročníku. Jako jediný doplněk má vlastní
složku, protože vedle PHP potřebuje ještě dva skripty a styl. Celá logika je v JavaScriptu
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

> **Zbývá dokončit:** pole *V carouselu* je pořád v administraci. Musí se smazat v SCF
> → Field Groups → *Akce – nastavení webu* a pak znovu vyexportovat `bm-scf-all.json`.
> Dokud se to nestane, je v administraci mrtvé zaškrtávátko.

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
