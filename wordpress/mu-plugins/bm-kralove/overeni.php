<?php
/**
 * Izolovaný test součtu bodů. Stubuje jen to, co doplněk při načtení volá,
 * takže běží bez WordPressu i bez databáze.
 *
 * Spuštění (PHP na stroji být nemusí, stačí Docker):
 *   docker run --rm -v "$PWD":/t -w /t php:8.2-cli php overeni.php
 *
 * První test počítá se skutečnými daty kandidáta ze stagingu (790 bodů).
 * Když se změní tvar, ve kterém Secure Custom Fields ukládá opakovatelné
 * pole, spadne právě ten test — a je to jediné včasné varování, které máme.
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
$GLOBALS['typ']  = array();

function add_action() {}
function add_filter() {}
function get_post_meta( $id, $jmeno, $jedno = false ) {
	return $GLOBALS['meta'][ $id ][ $jmeno ] ?? '';
}
function update_post_meta( $id, $jmeno, $hodnota ) {
	$GLOBALS['meta'][ $id ][ $jmeno ] = $hodnota;
}
function get_post_type( $id ) {
	return $GLOBALS['typ'][ $id ] ?? false;
}
function get_option( $k ) { return false; }
function update_option( $k, $v, $a = null ) {}
function get_posts( $a ) { return array(); }
function number_format_i18n( $n ) { return (string) $n; }
function esc_html( $s ) { return $s; }
function add_shortcode() {}

/**
 * Náhrada za sanitize_title. Jádro WordPressu dělá totéž: odstraní diakritiku,
 * zmenší písmena a mezery nahradí pomlčkami. Tady je proto, aby test běžel bez
 * WordPressu — skutečnou podobu kotev ověřuje až vykreslená stránka.
 */
function sanitize_title( $s ) {
	$s = iconv( 'UTF-8', 'ASCII//TRANSLIT//IGNORE', (string) $s );
	$s = strtolower( (string) $s );
	$s = preg_replace( '/[^a-z0-9]+/', '-', $s );

	return trim( (string) $s, '-' );
}

require __DIR__ . '/bm-kralove.php';

$chyb = 0;
function tvrd( $popis, $ocekavano, $skutecnost ) {
	global $chyb;
	$ok = $ocekavano === $skutecnost;
	if ( ! $ok ) {
		$chyb++;
	}
	printf( "%s  %-58s očekáváno %s, vráceno %s\n", $ok ? 'OK  ' : 'CHYBA', $popis, var_export( $ocekavano, true ), var_export( $skutecnost, true ) );
}

// 1. Skutečný kandidát ze stagingu: 100 + 200 + 400 + 90 = 790.
$GLOBALS['typ'][217]  = 'kral';
$GLOBALS['meta'][217] = array(
	'body'            => '4',
	'body_0_segment'  => 'studentske_jamy', 'body_0_pocet' => '100',
	'body_1_segment'  => 'kralovska_hra',   'body_1_pocet' => '200',
	'body_2_segment'  => 'plneni_vyzev',    'body_2_pocet' => '400',
	'body_3_segment'  => 'ostatni',         'body_3_pocet' => '90',
);
tvrd( 'Daniel Neubauer (100+200+400+90)', 790, bm_kralove_body( 217 ) );

// 2. Kandidát bez bodů.
$GLOBALS['typ'][1]  = 'kral';
$GLOBALS['meta'][1] = array();
tvrd( 'kandidát bez vyplněných bodů', 0, bm_kralove_body( 1 ) );

// 3. Prázdný počet řádků uložený jako prázdný řetězec.
$GLOBALS['meta'][2] = array( 'body' => '' );
$GLOBALS['typ'][2]  = 'kral';
tvrd( 'počet řádků je prázdný řetězec', 0, bm_kralove_body( 2 ) );

// 4. Řádek s prázdným počtem se nesmí počítat jako nic jiného než nula.
$GLOBALS['meta'][3] = array( 'body' => '2', 'body_0_pocet' => '50', 'body_1_pocet' => '' );
$GLOBALS['typ'][3]  = 'kral';
tvrd( 'jeden řádek má prázdný počet', 50, bm_kralove_body( 3 ) );

// 5. Zmenšený opakovač: zbyly meta po smazaných řádcích, počet říká 2.
$GLOBALS['meta'][4] = array(
	'body' => '2', 'body_0_pocet' => '10', 'body_1_pocet' => '20',
	'body_2_pocet' => '999', 'body_3_pocet' => '999',
);
$GLOBALS['typ'][4] = 'kral';
tvrd( 'osiřelé řádky po zmenšení opakovače se nepočítají', 30, bm_kralove_body( 4 ) );

// 6. Strop proti poškozenému počtu řádků — smyčka musí skončit.
$GLOBALS['meta'][5] = array( 'body' => '100000', 'body_0_pocet' => '7' );
$GLOBALS['typ'][5]  = 'kral';
tvrd( 'nesmyslný počet řádků smyčku neutrhne', 7, bm_kralove_body( 5 ) );

// 7. Háček na meta: zápis bodu přepočítá uloženou kopii.
$GLOBALS['meta'][217]['_bm_body_celkem'] = 0;
bm_kralove_po_zapisu_meta( 1, 217, 'body_2_pocet' );
tvrd( 'zápis body_2_pocet přepočítá kopii', 790, $GLOBALS['meta'][217]['_bm_body_celkem'] );

// 8. Háček ignoruje cizí meta — jinak by se volal sám donekonečna.
$GLOBALS['meta'][217]['_bm_body_celkem'] = 1;
bm_kralove_po_zapisu_meta( 1, 217, '_bm_body_celkem' );
tvrd( 'zápis vlastní kopie háček nespustí', 1, $GLOBALS['meta'][217]['_bm_body_celkem'] );

// 9. Háček ignoruje jiný typ obsahu se stejně pojmenovaným polem.
$GLOBALS['typ'][9]  = 'post';
$GLOBALS['meta'][9] = array( 'body' => '1', 'body_0_pocet' => '5' );
bm_kralove_po_zapisu_meta( 1, 9, 'body_0_pocet' );
tvrd( 'článek s polem body se neobohatí o kopii', false, isset( $GLOBALS['meta'][9]['_bm_body_celkem'] ) );

// 10. Jméno pole, které se vzorku podobá, ale není jím.
$GLOBALS['meta'][10] = array( 'body' => '1', 'body_0_pocet' => '5' );
$GLOBALS['typ'][10]  = 'kral';
bm_kralove_po_zapisu_meta( 1, 10, 'body_0_pocet_navic' );
tvrd( 'podobné jméno pole háček nespustí', false, isset( $GLOBALS['meta'][10]['_bm_body_celkem'] ) );

// 11. Sloupec se vkládá před datum a pořadí ostatních zůstává.
$sloupce = bm_kralove_sloupec( array( 'cb' => '', 'title' => 'Název', 'date' => 'Datum' ) );
tvrd( 'pořadí sloupců', array( 'cb', 'title', 'bm_body', 'date' ), array_keys( $sloupce ) );

// 12. Seznam bez sloupce s datem.
$sloupce = bm_kralove_sloupec( array( 'cb' => '', 'title' => 'Název' ) );
tvrd( 'bez sloupce Datum se přidá na konec', array( 'cb', 'title', 'bm_body' ), array_keys( $sloupce ) );

// 13. Pořadí: řadí se podle bodů sestupně.
$GLOBALS['typ'][101] = 'kral'; $GLOBALS['meta'][101] = array( 'body' => '1', 'body_0_pocet' => '100' );
$GLOBALS['typ'][102] = 'kral'; $GLOBALS['meta'][102] = array( 'body' => '1', 'body_0_pocet' => '300' );
$GLOBALS['typ'][103] = 'kral'; $GLOBALS['meta'][103] = array( 'body' => '1', 'body_0_pocet' => '200' );
$poradi = bm_kralove_poradi( array( 101, 102, 103 ) );
tvrd( 'řazení podle bodů sestupně', array( 102, 103, 101 ), array_column( $poradi, 'id' ) );

// 14. Shoda bodů drží vstupní pořadí (Pořadí a abeceda z dotazu).
$GLOBALS['typ'][104] = 'kral'; $GLOBALS['meta'][104] = array();
$GLOBALS['typ'][105] = 'kral'; $GLOBALS['meta'][105] = array();
$poradi = bm_kralove_poradi( array( 105, 104 ) );
tvrd( 'při shodě bodů zůstane vstupní pořadí', array( 105, 104 ), array_column( $poradi, 'id' ) );

// 15. Kotva: diakritika pryč, malá písmena.
$obsazene = array();
tvrd( 'kotva z ČAG', 'cag', bm_kralove_kotva( 'ČAG', $obsazene ) );
tvrd( 'kotva z Husova 9', 'husova-9', bm_kralove_kotva( 'Husova 9', $obsazene ) );

// 16. Shoda kotev — šest škol „t" na starém webu sdílelo jednu kotvu.
$obsazene = array();
$kotvy = array();
for ( $i = 0; $i < 6; $i++ ) {
	$kotvy[] = bm_kralove_kotva( 't', $obsazene );
}
tvrd( 'šest stejných škol dostane šest kotev', array( 't', 't-2', 't-3', 't-4', 't-5', 't-6' ), $kotvy );

// 17. Prázdná škola kotvu mít musí, jinak by odkaz nikam nevedl.
$obsazene = array();
tvrd( 'prázdná škola', 'kandidat', bm_kralove_kotva( '', $obsazene ) );
tvrd( 'druhá prázdná škola', 'kandidat-2', bm_kralove_kotva( '', $obsazene ) );

// 18. Kandidát bez jediného bodu musí uloženou kopii dostat taky — bez ní by
// ho řazení sloupce ze seznamu vyhodilo, místo aby ho zařadilo naspod.
$GLOBALS['typ'][201]  = 'kral';
$GLOBALS['meta'][201] = array();
bm_kralove_ulozit_soucet( 201 );
tvrd( 'kandidát bez bodů dostane kopii s nulou', 0, $GLOBALS['meta'][201]['_bm_body_celkem'] ?? null );

// 19. Rozpad po disciplínách u skutečného kandidáta.
tvrd(
	'rozpad bodů po disciplínách',
	array(
		array( 'klic' => 'studentske_jamy', 'popis' => 'Studentské jamy', 'pocet' => 100 ),
		array( 'klic' => 'kralovska_hra',   'popis' => 'Královská hra',   'pocet' => 200 ),
		array( 'klic' => 'plneni_vyzev',    'popis' => 'Plnění výzev',    'pocet' => 400 ),
		array( 'klic' => 'ostatni',         'popis' => 'Ostatní',         'pocet' => 90 ),
	),
	bm_kralove_segmenty( 217 )
);

// 20. Pořadí dílků je dané tabulkou, ne pořadím řádků v administraci —
// jinak by se pruhy dvou kandidátů nedaly porovnat.
$GLOBALS['typ'][301]  = 'kral';
$GLOBALS['meta'][301] = array(
	'body'           => '2',
	'body_0_segment' => 'ostatni',         'body_0_pocet' => '10',
	'body_1_segment' => 'studentske_jamy', 'body_1_pocet' => '20',
);
tvrd( 'dílky se řadí podle tabulky, ne podle řádků',
	array( 'studentske_jamy', 'ostatni' ),
	array_column( bm_kralove_segmenty( 301 ), 'klic' ) );

// 21. Dvakrát stejná disciplína se sečte do jednoho dílku.
$GLOBALS['typ'][302]  = 'kral';
$GLOBALS['meta'][302] = array(
	'body'           => '2',
	'body_0_segment' => 'kralovska_hra', 'body_0_pocet' => '30',
	'body_1_segment' => 'kralovska_hra', 'body_1_pocet' => '70',
);
tvrd( 'dva řádky stejné disciplíny dají jeden dílek',
	array( array( 'klic' => 'kralovska_hra', 'popis' => 'Královská hra', 'pocet' => 100 ) ),
	bm_kralove_segmenty( 302 ) );

// 22. Řádek bez vybrané disciplíny o body nepřijde.
$GLOBALS['typ'][303]  = 'kral';
$GLOBALS['meta'][303] = array( 'body' => '1', 'body_0_segment' => '', 'body_0_pocet' => '55' );
tvrd( 'řádek bez disciplíny spadne do Ostatní', 55, bm_kralove_body( 303 ) );
tvrd( 'a dostane barvu Ostatní', 'ostatni', bm_kralove_segmenty( 303 )[0]['klic'] ?? null );

// 23. Disciplína přidaná do nabídky, kterou doplněk ještě nezná.
$GLOBALS['typ'][304]  = 'kral';
$GLOBALS['meta'][304] = array(
	'body'           => '2',
	'body_0_segment' => 'nocni_hra',       'body_0_pocet' => '40',
	'body_1_segment' => 'studentske_jamy', 'body_1_pocet' => '10',
);
$neznamy = bm_kralove_segmenty( 304 );
tvrd( 'neznámá disciplína jde na konec', 'Nocni hra', $neznamy[1]['popis'] ?? null );
tvrd( 'a body se neztratí', 50, bm_kralove_body( 304 ) );

// 24. Disciplína s nulou se nevykresluje — nulový dílek není vidět
// a v legendě by jen mátl.
$GLOBALS['typ'][305]  = 'kral';
$GLOBALS['meta'][305] = array(
	'body'           => '2',
	'body_0_segment' => 'studentske_jamy', 'body_0_pocet' => '0',
	'body_1_segment' => 'kralovska_hra',   'body_1_pocet' => '80',
);
tvrd( 'disciplína s nulou se vynechá',
	array( 'kralovska_hra' ),
	array_column( bm_kralove_segmenty( 305 ), 'klic' ) );

echo $chyb ? "\n$chyb selhalo\n" : "\nvše prošlo\n";
exit( $chyb ? 1 : 0 );
