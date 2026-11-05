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
	'body'          => '4',
	'body_0_pocet'  => '100',
	'body_1_pocet'  => '200',
	'body_2_pocet'  => '400',
	'body_3_pocet'  => '90',
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

echo $chyb ? "\n$chyb selhalo\n" : "\nvše prošlo\n";
exit( $chyb ? 1 : 0 );
