# 18. Hlavička a barvy sekcí

> **6. 11. 2026.** Zadavatel: *„Tohle je hrozný, vůbec to není Majálesový.“*
> Hlavička byla jediné místo webu, kde chyběl černý obrys a plakátový stín.
> Tenhle dokument popisuje, co se s tím udělalo a proč to vypadá zrovna takhle.

---

## Co se změnilo

| | Dřív | Teď |
|---|---|---|
| Pozadí hlavičky | zelenomodrá (palette1) | papír (palette7) s černou spodní hranou |
| Položky menu | bílý text, aktivní žlutě podtržený | černé štítky s obrysem, aktivní vyplněný svou barvou |
| Rozbalovátko | černý obdélník s vlasovými linkami | bílá kartička zavěšená pod štítkem |
| Mobilní panel | černý list s linkami | papír se štítky, jako na počítači |
| Logo | bílá kapka | invertovaná na černou (kromě úvodu) |
| Nadpis podstránky | černý | podtržený barvou své sekce |

Tři návrhy, ze kterých zadavatel vybral, jsou v artefaktu *Hlavička Majálesu*.

## Jedna barva na sekci

Každá položka hlavní úrovně menu má svou barvu z palety a stránka pod ní ji zdědí:

| Pořadí | Položka | Barva |
|---|---|---|
| 1. | Program | žlutá (palette15) |
| 2. | Králové | korálová (palette10) |
| 3. | Osvěžovny | levandulová (palette14) |
| 4. | O festivalu a vše pod ním | zelenomodrá (palette1) |

**Barvu rozdává `bm-barva-sekce.php` podle pořadí v menu, ne podle ID stránek.**
Seznam ID v CSS („stránka 85 je korálová“) je přesně ten zápis, kterému za rok nikdo
neporozumí a který se po přestavbě menu tiše rozejde. Pořadí v menu naopak tým vidí
a přetahuje myší. Přehodí položky a barvy se přehodí s nimi.

Doplněk vypíše do hlavičky stránky jeden `<style>`: pravidlo `--bm-polozka` pro každou
položku a `--bm-sekce` pro `<body>`. CSS v `bm.css` o pořadí ani o ID nic neví.

Stránky mimo menu (Podpoř nás, Zpracování osobních údajů, úvod) barvu nedostanou
a jejich nadpis zůstane černý bez podtržení. Je to schválně: barva znamená „tady
v menu jsi“, a tam návštěvník není.

## Proč je barva ZA textem, a ne v něm

Zadavatel chtěl obarvit písmena nadpisu. Nejde to — paleta ročníku je stavěná na
podklad. Naměřené kontrasty:

| Barva | Jako text na papíře | Pod černým písmem |
|---|---|---|
| Žlutá | **1,1 : 1** | 14,9 : 1 |
| Levandulová | **1,8 : 1** | 9,4 : 1 |
| Korálová | **2,6 : 1** | 6,5 : 1 |
| Zelenomodrá | **2,8 : 1** | 5,9 : 1 |
| Zelenomodrá tmavá | 5,4 : 1 | 3,1 : 1 |

Práh je 3 : 1 pro velký tučný nadpis a 4,5 : 1 pro běžný text. **Jako písmo neprojde
žádná barva ročníku kromě tmavé zelené** — a ta jediná se zase nedá použít jako podklad
pod černou. Proto podtržení: barva jako zvýrazňovač pod černým nadpisem.

Ze tří variant (blok, podtrh, nadlinka) zadavatel vybral **podtrh**: snese dlouhý
nadpis i zalomení na dva řádky a na telefonu zabere nejmíň místa.

**Nadpis je proto `display: inline`.** U blokového prvku by se podtržení vykreslilo
jednou přes celou výšku rámečku, takže dvouřádkový nadpis by přeťal jeden pruh v půlce.
Jako řádkový prvek dostane každý řádek svůj pruh (`box-decoration-break: clone`).
Důsledek: svislé okraje na nadpisu neplatí, mezeru pod ním drží `.bm-lead`.

## Úvodní stránka je výjimka

Nad fotkou je hlavička průhledná (rozhodnuto 5. 11., vzor korzonarodni.cz). Tam tedy:

- **žádná černá spodní hrana** — přeťala by fotku,
- **logo zůstává bílé**, invert se vypíná,
- **štítky mají bílou výplň** — prázdný obrys by se na fotce ztratil.

Úvod zároveň nemá položku v menu, takže nemá ani barvu sekce. Nadpis v heru je bílý
na fotce a podtržení se nevykresluje.

## Logo

`filter: brightness(0)` funguje **dneska**, protože značka je čistě bílá kapka
v průhledném PNG. Jakmile logo dostane barvu nebo nápis, invert ho rozbije. Je to
berlička do doby, než tým dodá tmavou variantu souboru — pak stačí smazat jedno
pravidlo v `bm.css` a nahrát druhý obrázek.

## Kde to žije

| Soubor | K čemu |
|---|---|
| `wordpress/mu-plugins/bm-barva-sekce.php` | rozdává barvy podle pořadí v menu |
| `wordpress/mu-plugins/bm-menu-rozbalovatko.php` | sundá href z položky s adresou „#“ |
| `wordpress/vzhled/bm.css`, sekce *Hlavička* | štítky, rozbalovátko, mobilní panel, podtržení |
| `wordpress/vzhled/nastaveni.py` | pozadí hlavičky, barvy písma, mobilní panel |
| `wordpress/vzhled/menu.py` | pořadí položek, a tím i pořadí barev |

## Past, na kterou se došláplo

**Kadence píše do stránky vlastní pravidla s delšími selektory.** Rozbalovátko má
`.header-navigation .header-menu-container ul ul.sub-menu` (tři třídy a dva prvky).
Kratší zápis `.site-header .primary-menu-container .sub-menu` má tříd stejně, ale
prvků míň, takže prohraje na váze — barva se neprojeví a vypadá to, jako by pravidlo
v souboru vůbec nebylo. Pravidla pro rozbalovátko proto kopírují tvar těch od Kadence
a přidávají `.site-header` navíc.

**Záloha barvy se píše až do `var()` na místě použití.** Deklarace
`--bm-polozka: …` přímo na odkazu je pro ten prvek silnější než hodnota zděděná
z položky menu, takže by zálohu nikdy nepustila ke slovu a menu by zůstalo
jednobarevné. Správně je `var(--bm-polozka, var(--global-palette15))`.

## Čtyři věci z prvního proklikání (6. 11. 2026)

**Logo sedělo o 8 px níž než štítky.** Kadence dává obalu loga odsazení 16 px shora
a 0 zdola; hlavička se pak sice zarovná na střed, ale kolem loga je nesymetrická
mezera. Odsazení se v `bm.css` nuluje.

**Rozbalovátko se zavíralo cestou dolů.** Kartička visela 36 px pod položkou a v té
mezeře nebylo nic, takže kurzor opustil položku dřív, než došel do nabídky.
Mezeru překlenuje neviditelný `::before` samotné kartičky — patří jí, takže
položka zůstane „pod myší“. (Mezera se vzápětí zkrátila na 8 px, viz níž.)

**Obsah nestál na stejné svislici jako nadpis.** Skupiny mají `contentSize` 1160 px,
jenže obal stránky je širší (1242 px při 1440), takže WordPress bloky uvnitř
centroval a přidával jim 41 px z každé strany. Nadpis je `display: inline` a ten se
centrovat nedá, takže jediný zůstal vlevo — a rozdíl byl vidět. Na mobilu je obal
užší než 1160 px, proto se to tam neprojevilo. Řeší to pravidlo, které blokům uvnitř
skupin podstránky ruší `max-width`; pásy přes celou šířku na úvodu jsou z něj vyjmuté,
jinak by se hero roztáhlo přes celé okno.

**Titulkový pás Kadence byl pořád zelený.** Stránky, které si nadpis nedávají do
obsahu (O festivalu, Historie, Kontakt, Aktuality), dostávají nadpis od Kadence
v samostatném pásu. Ten teď má bílé pozadí, černý nadpis a stejné podtržení jako
nadpisy v obsahu, takže obě skupiny stránek vypadají stejně.

## Druhé kolo: O festivalu jen rozbaluje (6. 11. 2026)

Nejdřív se zrušil prázdný rozcestník `/o-festivalu/` tím, že se na něj přenesl
text z podstránky *O nás*. Jenže tím položka menu pořád někam vedla a zadavatel
ze záznamu obrazovky pojmenoval, co mu na tom vadí: *„To ‚O festivalu‘ by mělo být
jen rozbalit/zabalit a ‚O nás‘ tedy bude zpátky samostatná položka."* Na mobilu
navíc nešlo poznat, jestli klepnutím rozbaluje, nebo odchází na stránku.

**Položka je teď vlastní odkaz s adresou `#` a doplněk z ní sundá `href`.**
`<a>` bez adresy není odkaz: myš nad ním nemá ruku, klepnutí nic neudělá
a tabulátor ho přeskočí — klávesnice jde rovnou na tlačítko se šipkou, které
Kadence kreslí vedle a které umí seznam rozbalit. Rozbalování tedy nepotřebovalo
ani řádek JavaScriptu; stačilo odkaz zrušit.

Proč `#` a ne prázdná adresa: prázdnou REST u vlastního odkazu odmítne a `#` je
zároveň to, co by do pole napsal člověk v administraci. V menu je tak vidět, že
položka nikam nevede.

**Na mobilu rozbaluje celý řádek.** Kadence kreslí popisek a tlačítko se šipkou
vedle sebe; tlačítko se v `bm.css` roztáhne přes celý řádek a šipka se v něm
odsune doprava. Vybírá se podle `:has(> a:not([href]))`, takže rozbalovátko,
jehož popisek je skutečný odkaz, se tím nerozbije.

**Kartička visí 8 px pod štítkem, ne 36.** Původní mezera ji posadila až pod
černou linku hlavičky, jenže takhle daleko vypadala jako samostatný plovoucí blok
a nebylo poznat, ke komu patří. Teď linku překrývá. Zvýraznění štítku se zároveň
přesunulo z `a:hover` na `li:hover`, takže štítek zůstane vybarvený i ve chvíli,
kdy je myš uvnitř rozbaleného seznamu.

**„O nás“ je zpátky** jako samostatná položka s původní adresou
`/o-festivalu/o-nas/`. Rozbalovátko má devět položek.

**Stránka `/o-festivalu/` je v konceptech.** Je nadřazená podstránkám a drží jim
adresy `/o-festivalu/…`, takže se nesmí smazat — adresy by se změnily. Vydaná být
nemusí: v menu na ni nic nevede a zůstal na ní jen zástupný text („Úvodní text
dodá zadavatel“); kopie textu *O nás*, která na ní krátce byla, je pryč, aby
na webu nebyly dvě stejné stránky. Ověřeno, že podstránky
při konceptovém rodiči odpovídají dál 200; `/o-festivalu/` samo vrací 404.

Jednorázový skript `o-festivalu.py`, který předtím obsah přenášel, je smazaný —
reprodukoval by stav, který padl.

## Třetí kolo: stav „tady jsi“ a vzdušnost pásu

**Položka, na jejíž stránce návštěvník je, byla v rozbalovátku nečitelná.**
Kadence jí dává tmavě šedou výplň (`palette4`) a bílý text, jenže bílý text
přebíjelo naše pravidlo o barvě položek — výsledkem bylo černé písmo na tmavě
šedé. Teď se nastavuje obojí najednou: **černá výplň, bílé písmo**. Záměrně ne
barvou sekce: tou je vybarvená první položka (*Praktické info*) a dvě stejné
výplně v jednom seznamu by si lezly do zelí. Stejná značka je i v mobilním
panelu, kde předtím nebyla žádná.

**Nadpis plaval uprostřed prázdna.** Titulkový pás od Kadence má 200 px na výšku
s nadpisem na střed a pod pásem je ještě 80 px před obsahem — mezi nadpisem
a prvním odstavcem tedy bylo 154 px. Na stránkách stavěných skriptem je přitom
nadpis v obsahu a text začíná hned pod ním. Pás proto dostal odsazení jen shora
(96 px, na mobilu 48) a obsah se k němu přisunul na 20 px, tedy na stejnou
vzdálenost, jakou drží `.bm-lead`. Nadpis *O nás* tím sedí na 187 px, nadpis
*Program* na 197 — obě skupiny stránek vypadají stejně.

Obsah se vybírá sousedním selektorem `.entry-hero + .content-area`, ne třídou na
`<body>`: výpis příspěvků (Aktuality) má tutéž třídu `content-title-style-above`,
ale pás nekreslí, a přisunutím by se nalepil na hlavičku.

## Aktuality dostaly barvu dodatečně

Výpis příspěvků není stránka a jednotlivý článek v menu vůbec není, takže
`is_singular()` u obou vracelo cizí ID a nadpis zůstával bez podtržení.
Obojí teď v menu zastupuje stránka nastavená jako „stránka s příspěvky“.

## Co zůstává otevřené

- **Žlutá znamená dvě věci.** Program má žlutou sekci a zároveň je na něm žlutá
  kategorie *Filmy*. Buď se to přijme, nebo Program dostane barvu, kterou žádná
  kategorie nemá — jenže všechny barvy palety už nějaká kategorie používá.
- **Bílé štítky nad fotkou** jsou čitelné, ale bezbarvé. Až bude hotová finální fotka,
  stojí za to se podívat, jestli tam nemůže být barva sekce.
- **Výpis Aktualit má nadpis na střed a bez podtržení.** Je to archivní nadpis
  (`.archive-title`), ne titulkový pás, takže ho pravidla pro podtržení minou.
  Proměnná `--bm-sekce` na té stránce nastavená je, jen ji nikdo nečte. Jednotlivý
  článek podtržený je.
- **Stránky, jejichž obsah nestavěl skript** (O nás, Historie, Kontakt…), mají
  odstavce přes celou šířku obsahu — řádky jsou dlouhé přes 130 znaků. Stránky ze
  skriptu drží šířku přes `.bm-lead`. Až tým texty projde, stojí za to sjednotit.
