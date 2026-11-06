<?php
/**
 * Izolovaný test osvěžoven. Stubuje jen to, co doplněk při načtení volá,
 * takže běží bez WordPressu i bez databáze.
 *
 * Spuštění (PHP na stroji být nemusí, stačí Docker):
 *   docker run --rm -v "$PWD":/t -w /t php:8.3-cli php overeni.php
 *
 * Testy stojí na **skutečných datech dnešního webu** (devět podniků,
 * 24 položek). Zvlášť na těch třech, kvůli kterým se v září doporučovalo
 * opakovatelné pole nedělat: Klika má jen slevu bez ceny, Masné krámy
 * jednu položku bez druhu a Široko se dělí na alko a nealko.
 */

/*
 * Jen z příkazové řádky. Soubor se nahrává na server spolu se zbytkem složky
 * a bez téhle pojistky ho kdokoli spustí přes
 * /wp-content/mu-plugins/<doplnek>/overeni.php a dostane výpis testů.
 * Zjištěno 5. 11. 2026 na stagingu, kde to tak opravdu bylo.
 */
if ( 'cli' !== PHP_SAPI ) {
	exit;
}

define( 'ABSPATH', __DIR__ );

$GLOBALS['meta'] = array();

function add_action() {}
function add_filter() {}
function add_shortcode() {}
function register_post_type() {}
function get_post_meta( $id, $jmeno, $jedno = false ) {
	return $GLOBALS['meta'][ $id ][ $jmeno ] ?? '';
}
function get_posts( $a ) { return array(); }
function esc_html( $s ) { return $s; }
function esc_attr( $s ) { return $s; }
function esc_url( $s ) { return $s; }

require __DIR__ . '/bm-osvezovny.php';

$chyb  = 0;
$testu = 0;
function tvrd( $popis, $ocekavano, $skutecnost ) {
	global $chyb, $testu;
	$testu++;
	$ok = $ocekavano === $skutecnost;
	if ( ! $ok ) {
		$chyb++;
	}
	printf(
		"%s  %-62s očekáváno %s, vráceno %s\n",
		$ok ? 'OK  ' : 'CHYBA',
		$popis,
		var_export( $ocekavano, true ),
		var_export( $skutecnost, true )
	);
}

/** Zkratka: z pole řádků udělá meta tak, jak je ukládá Secure Custom Fields. */
function zaloz( $id, $radky ) {
	$meta = array( 'polozky' => (string) count( $radky ) );
	foreach ( $radky as $i => $radek ) {
		foreach ( array( 'nazev', 'popis', 'cena', 'druh' ) as $podpole ) {
			$meta[ 'polozky_' . $i . '_' . $podpole ] = $radek[ $podpole ] ?? '';
		}
	}
	$GLOBALS['meta'][ $id ] = $meta;
}

/** Jen popisy skupin, na porovnání se čte líp než celé pole. */
function popisy( $skupiny ) {
	return array_column( $skupiny, 'popis' );
}


/* ── 1. Široko: jediný podnik, který se dělí na alko a nealko ─────────── */

zaloz( 1, array(
	array( 'nazev' => 'Tequila Sunrise',         'cena' => '100 Kč', 'druh' => 'alko' ),
	array( 'nazev' => 'Mangová Mimosa',          'cena' => '80 Kč',  'druh' => 'alko' ),
	array( 'nazev' => 'Bor Ton',                 'cena' => '90 Kč',  'druh' => 'alko',
	       'popis' => 'Borovička s tonicem' ),
	array( 'nazev' => 'Vis Fizz',                'cena' => '80 Kč',  'druh' => 'alko' ),
	array( 'nazev' => 'Okurkový Fizz',           'cena' => '70 Kč',  'druh' => 'nealko' ),
	array( 'nazev' => 'Domácí Jahodová Matcha',  'cena' => '80 Kč',  'druh' => 'nealko' ),
	array( 'nazev' => 'Domácí Mangová Matcha',   'cena' => '80 Kč',  'druh' => 'nealko' ),
) );

$siroko = bm_osvezovny_polozky( 1 );
tvrd( 'Široko má sedm položek', 7, count( $siroko ) );

$skupiny = bm_osvezovny_skupiny( $siroko );
// Nealko je v nabídce pole první, takže jde nahoru — bez ohledu na to,
// že v řádcích je až za alkem.
tvrd( 'Široko: dva druhy => nadpisy, nealko první', array( 'Nealko', 'Alko' ), popisy( $skupiny ) );
tvrd( 'Široko: nealko má tři položky', 3, count( $skupiny[0]['polozky'] ) );
tvrd( 'Široko: alko má čtyři položky',  4, count( $skupiny[1]['polozky'] ) );


/* ── 2. Mezi plátky: tři drinky, žádný druh ──────────────────────────── */

/* Přesně ten případ, kvůli kterému je druh nepovinný. Zařadit „Květnový
   infarkt" (yerba maté, mangový džus, espresso a trochu zlata) nejde. */
zaloz( 2, array(
	array( 'nazev' => 'Květnový infarkt', 'cena' => '105 Kč',
	       'popis' => 'Yerba maté, mangový džus, espresso a trochu zlata' ),
	array( 'nazev' => 'Májový les',       'cena' => '95 Kč',  'popis' => 'Grepová matcha s filtrem' ),
	array( 'nazev' => 'Pír',              'cena' => '155 Kč', 'popis' => 'Aquavit vol.2, hruškové pyré a prosecco' ),
) );

$skupiny = bm_osvezovny_skupiny( bm_osvezovny_polozky( 2 ) );
tvrd( 'Mezi plátky: žádný druh => jediná skupina bez nadpisu', array( '' ), popisy( $skupiny ) );
tvrd( 'Mezi plátky: všechny tři položky zůstaly', 3, count( $skupiny[0]['polozky'] ) );


/* ── 3. Jeden druh nestačí na nadpisy ────────────────────────────────── */

/* Osm z devíti podniků má nabídku jednolitou. Nadpis „Nealko" nad jejich
   třemi drinky nic nerozlišuje a jen zabírá místo. */
zaloz( 3, array(
	array( 'nazev' => 'Limonády',   'cena' => '70 Kč', 'druh' => 'nealko' ),
	array( 'nazev' => 'Flash Batch','cena' => '70 Kč', 'druh' => 'nealko' ),
) );
tvrd( 'jeden druh => skupina bez nadpisu', array( '' ), popisy( bm_osvezovny_skupiny( bm_osvezovny_polozky( 3 ) ) ) );


/* ── 4. Klika: sleva bez ceny ────────────────────────────────────────── */

/* Druhý důvod, proč se v září doporučovalo opakovatelné pole nedělat.
   Cena je text a smí být prázdná, takže se položka vypíše celá. */
zaloz( 4, array(
	array( 'nazev' => 'Sleva 20 % na vše', 'popis' => 'heslo: Majáles', 'cena' => '' ),
) );
$klika = bm_osvezovny_polozky( 4 );
tvrd( 'Klika: položka bez ceny se nezahodí', 1, count( $klika ) );
tvrd( 'Klika: cena zůstane prázdná', '', $klika[0]['cena'] );
tvrd( 'Klika: prázdná cena se nevypíše', false, str_contains( bm_osvezovny_polozka( $klika[0] ), 'bmo-polozka__cena' ) );


/* ── 5. Smíchaný druh a prázdno ──────────────────────────────────────── */

/* Položky bez druhu jdou vždycky na konec a nikdy nedostanou nadpis —
   nevíme, jak by se jmenoval. */
zaloz( 5, array(
	array( 'nazev' => 'Pivo',    'druh' => 'alko' ),
	array( 'nazev' => 'Překvapení' ),
	array( 'nazev' => 'Gyros',   'druh' => 'jidlo' ),
) );
$skupiny = bm_osvezovny_skupiny( bm_osvezovny_polozky( 5 ) );
// Když kartička nadpisy má, musí je mít všechny skupiny. Nepojmenovaná
// položka pod poslední skupinou vypadá, jako by tam spadla omylem.
tvrd( 'pořadí skupin je dané nabídkou pole', array( 'Alko', 'Jídlo', 'Ostatní' ), popisy( $skupiny ) );
tvrd( 'položka bez druhu je poslední', 'Překvapení', $skupiny[2]['polozky'][0]['nazev'] );

// Opačný případ: kartička bez nadpisů je plochý seznam a „Ostatní" by v ní
// bylo jediné slovo navíc. Tohle je Mezi plátky na dnešním webu.
zaloz( 51, array(
	array( 'nazev' => 'Pivo', 'druh' => 'alko' ),
	array( 'nazev' => 'Překvapení' ),
) );
$plocha = bm_osvezovny_skupiny( bm_osvezovny_polozky( 51 ) );
tvrd( 'jeden druh + nezařazené => jediný plochý seznam', array( '' ), popisy( $plocha ) );
tvrd( 'plochý seznam drží pořadí řádků', array( 'Pivo', 'Překvapení' ), array_column( $plocha[0]['polozky'], 'nazev' ) );

zaloz( 52, array( array( 'nazev' => 'Jen tohle' ) ) );
tvrd( 'samé nezařazené => žádný nadpis', array( '' ), popisy( bm_osvezovny_skupiny( bm_osvezovny_polozky( 52 ) ) ) );


/* ── 6. Poškozená a rozdělaná data ───────────────────────────────────── */

$GLOBALS['meta'][6] = array();
tvrd( 'podnik bez nabídky', array(), bm_osvezovny_polozky( 6 ) );

$GLOBALS['meta'][7] = array( 'polozky' => '' );
tvrd( 'počet řádků je prázdný řetězec', array(), bm_osvezovny_polozky( 7 ) );

// Řádek bez názvu je nedopsaný koncept. Odrážka s cenou a bez názvu
// vypadá jako chyba vykreslení.
zaloz( 8, array(
	array( 'nazev' => 'Kofola', 'cena' => '45 Kč' ),
	array( 'nazev' => '',       'cena' => '99 Kč' ),
) );
tvrd( 'řádek bez názvu se vynechá', 1, count( bm_osvezovny_polozky( 8 ) ) );

// Zmenšený opakovač: zbyly meta po smazaných řádcích, počet říká 1.
$GLOBALS['meta'][9] = array(
	'polozky' => '1',
	'polozky_0_nazev' => 'Limonáda',
	'polozky_1_nazev' => 'Smazaná',
	'polozky_2_nazev' => 'Taky smazaná',
);
tvrd( 'osiřelé řádky po zmenšení opakovače se nevypíšou', 1, count( bm_osvezovny_polozky( 9 ) ) );

// Strop proti poškozenému počtu řádků — smyčka musí skončit.
$GLOBALS['meta'][10] = array( 'polozky' => '100000', 'polozky_0_nazev' => 'Jediná' );
tvrd( 'nesmyslný počet řádků smyčku neutrhne', 1, count( bm_osvezovny_polozky( 10 ) ) );

// Mezery okolo hodnot: tým je do políčka dostane snadno a prázdný
// řetězec s mezerou by prošel kontrolou „je vyplněno".
$GLOBALS['meta'][11] = array(
	'polozky' => '1', 'polozky_0_nazev' => '  Datel  ', 'polozky_0_cena' => ' 70 Kč ',
);
$orez = bm_osvezovny_polozky( 11 );
tvrd( 'mezery okolo názvu se oříznou', 'Datel', $orez[0]['nazev'] );
tvrd( 'mezery okolo ceny se oříznou', '70 Kč', $orez[0]['cena'] );

$GLOBALS['meta'][12] = array( 'polozky' => '1', 'polozky_0_nazev' => '   ' );
tvrd( 'název ze samých mezer se nepočítá', array(), bm_osvezovny_polozky( 12 ) );


/* ── 7. Druh, který přibyl v nabídce a chybí v bm_osvezovny_druhy() ──── */

/* Nesmí se ztratit — jde na konec s popiskem odvozeným z klíče. */
zaloz( 13, array(
	array( 'nazev' => 'Víno',   'druh' => 'alko' ),
	array( 'nazev' => 'Dort',   'druh' => 'sladke_pecivo' ),
) );
tvrd( 'neznámý druh se vypíše na konci', array( 'Alko', 'Sladke pecivo' ), popisy( bm_osvezovny_skupiny( bm_osvezovny_polozky( 13 ) ) ) );


/* ── 8. Odkaz na mapu ────────────────────────────────────────────────── */

tvrd(
	'vyplněný odkaz se použije beze změny',
	'https://maps.app.goo.gl/vDEReLX97DQE28FSA',
	bm_osvezovny_mapa( 'https://maps.app.goo.gl/vDEReLX97DQE28FSA', 'Široko' )
);
tvrd(
	'bez odkazu se podnik vyhledá podle názvu',
	'https://www.google.com/maps/search/?api=1&query=Peak%20of%20matcha%20%C4%8Cesk%C3%A9%20Bud%C4%9Bjovice',
	bm_osvezovny_mapa( '', 'Peak of matcha' )
);
tvrd(
	'mezery okolo odkazu nevyrobí odkaz s mezerou',
	'https://www.google.com/maps/search/?api=1&query=Datel%20%C4%8Cesk%C3%A9%20Bud%C4%9Bjovice',
	bm_osvezovny_mapa( '   ', 'Datel' )
);


/* ── 9. Značkování, na kterém stojí filtr ────────────────────────────── */

/* Filtr v osvezovny.js čte data-druh. Kdyby atribut zmizel, filtr
   by tiše přestal fungovat a nikdo by si toho nevšiml. */
$html = bm_osvezovny_polozka( array( 'nazev' => 'Pivo', 'popis' => '', 'cena' => '55 Kč', 'druh' => 'alko' ) );
tvrd( 'položka nese data-druh', true, str_contains( $html, 'data-druh="alko"' ) );
tvrd( 'položka bez druhu nese prázdné data-druh', true,
	str_contains( bm_osvezovny_polozka( array( 'nazev' => 'X', 'popis' => '', 'cena' => '', 'druh' => '' ) ), 'data-druh=""' ) );

/* Popisky tlačítek v osvezovny.js musí sedět na volby pole. */
tvrd( 'druhy jsou tři a v pořadí nealko, alko, jídlo',
	array( 'nealko', 'alko', 'jidlo' ), array_keys( bm_osvezovny_druhy() ) );


printf( "\n%s  %d testů, %d chyb\n", $chyb ? 'SELHALO' : 'HOTOVO ', $testu, $chyb );
exit( $chyb ? 1 : 0 );
