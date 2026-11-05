<?php
/**
 * Plugin Name: Majáles — králové
 * Description: Součet bodů kandidáta, řaditelný sloupec v administraci a zkratka [bm_kralove].
 * Version: 1.0.0
 *
 * Proč to existuje:
 * Body kandidáta se zadávají po disciplínách (opakovatelné pole „Body"), ale
 * všude — v žebříčku i v administraci — se pracuje s jejich součtem. WordPress
 * opakovatelná pole sčítat neumí a žádná zkratka na výpis kandidátů neexistuje.
 *
 * Proč se součet počítá při každém čtení:
 * Uložené odvozené číslo se dřív nebo později rozejde se skutečností. Součet se
 * proto nikdy nebere z databáze — vždy se spočítá z bodů. Uložená kopie
 * (_bm_body_celkem) slouží jedinému účelu: řazení sloupce v administraci,
 * protože podle spočítané hodnoty WordPress řadit neumí. Kdyby se kopie
 * rozešla, pozná se to jen na pořadí řádků v administraci; vypsané číslo
 * i veřejný žebříček jsou vždy správně.
 *
 * Proč je kopie napojená na zápis meta, a ne na acf/save_post:
 * Secure Custom Fields pouští acf/save_post jen u uložení z formuláře nebo
 * z editoru. Zápis přes REST API (import, skript, jiný doplněk) ho nespustí —
 * říká to komentář přímo ve zdrojáku SCF: „updates fields without firing
 * acf/save_post". Zato každý z těch zápisů jde přes update_metadata(), a na tu
 * je napojený háček níž. Jde tedy o jedinou cestu, která nejde obejít.
 */

defined( 'ABSPATH' ) || exit;

/** Typ obsahu s kandidáty. */
const BM_KRALOVE_TYP = 'kral';

/** Uložená kopie součtu. Podtržítko na začátku ji skryje z vlastních polí i z REST. */
const BM_KRALOVE_META_SOUCET = '_bm_body_celkem';

/** Opakovatelné pole s body. Podpole s počtem se jmenuje „pocet". */
const BM_KRALOVE_POLE_BODY = 'body';

add_action( 'added_post_meta', 'bm_kralove_po_zapisu_meta', 10, 3 );
add_action( 'updated_post_meta', 'bm_kralove_po_zapisu_meta', 10, 3 );
add_action( 'deleted_post_meta', 'bm_kralove_po_zapisu_meta', 10, 3 );
add_action( 'admin_init', 'bm_kralove_dopocitat_chybejici' );

add_filter( 'manage_' . BM_KRALOVE_TYP . '_posts_columns', 'bm_kralove_sloupec' );
add_action( 'manage_' . BM_KRALOVE_TYP . '_posts_custom_column', 'bm_kralove_obsah_sloupce', 10, 2 );
add_filter( 'manage_edit-' . BM_KRALOVE_TYP . '_sortable_columns', 'bm_kralove_sloupec_radit' );
add_action( 'pre_get_posts', 'bm_kralove_radit_podle_bodu' );

/**
 * Součet bodů kandidáta ze všech disciplín.
 *
 * Čte meta přímo přes jádro WordPressu, ne přes funkce Secure Custom Fields —
 * stejně jako bm-termin.php. Kdyby se plugin s poli jednou vyměnil, součet
 * funguje dál, protože tvar uložení (body = počet řádků, body_0_pocet = první
 * řádek) je daný databází, ne pluginem.
 *
 * @param int $id ID kandidáta.
 * @return int Součet bodů, nebo 0.
 */
function bm_kralove_body( $id ) {
	$radku = (int) get_post_meta( $id, BM_KRALOVE_POLE_BODY, true );

	// Záporný nebo nesmyslně velký počet řádků by znamenal poškozená data.
	// Strop drží smyčku konečnou, i kdyby se to stalo.
	if ( $radku < 1 ) {
		return 0;
	}
	$radku = min( $radku, 100 );

	$soucet = 0;
	for ( $i = 0; $i < $radku; $i++ ) {
		$soucet += (int) get_post_meta( $id, BM_KRALOVE_POLE_BODY . '_' . $i . '_pocet', true );
	}

	return $soucet;
}

/**
 * Uloží kopii součtu pro řazení v administraci.
 *
 * @param int $id ID kandidáta.
 * @return void
 */
function bm_kralove_ulozit_soucet( $id ) {
	update_post_meta( $id, BM_KRALOVE_META_SOUCET, bm_kralove_body( $id ) );
}

/**
 * Po každém zápisu bodů přepočítá uloženou kopii.
 *
 * Háček dostane jméno meta, takže se pozná zápis počtu řádků („body") i zápis
 * jednotlivého řádku („body_3_pocet"). Jméno uložené kopie se vzorku netrefí,
 * takže se háček nevolá sám od sebe donekonečna.
 *
 * Jeden uložený kandidát spustí háček několikrát (za každý řádek). To nevadí:
 * update_post_meta beze změny hodnoty do databáze nesáhne, takže skutečný
 * zápis proběhne nejvýš jednou.
 *
 * @param int    $meta_id   ID záznamu meta (nepoužívá se).
 * @param int    $id        ID příspěvku.
 * @param string $jmeno     Jméno meta.
 * @return void
 */
function bm_kralove_po_zapisu_meta( $meta_id, $id, $jmeno ) {
	if ( BM_KRALOVE_POLE_BODY !== $jmeno
		&& ! preg_match( '/^' . BM_KRALOVE_POLE_BODY . '_\d+_pocet$/', (string) $jmeno ) ) {
		return;
	}

	if ( BM_KRALOVE_TYP !== get_post_type( $id ) ) {
		return;
	}

	bm_kralove_ulozit_soucet( $id );
}

/**
 * Jednorázově doplní uloženou kopii kandidátům, kteří vznikli dřív než doplněk.
 *
 * Bez toho by řazení sloupce takové kandidáty vynechalo — WordPress při řazení
 * podle meta zahodí příspěvky, které ji nemají, a zmizeli by ze seznamu, aniž
 * by to komukoli došlo.
 *
 * Musí to být háček na admin_init: doplňky v mu-plugins nemají aktivaci.
 * Volba v databázi hlídá, aby se smyčka přes všechny kandidáty spustila jednou.
 *
 * @return void
 */
function bm_kralove_dopocitat_chybejici() {
	if ( '1.0.0' === get_option( 'bm_kralove_prepocet' ) ) {
		return;
	}

	$kandidati = get_posts( array(
		'post_type'        => BM_KRALOVE_TYP,
		'post_status'      => 'any',
		'numberposts'      => -1,
		'fields'           => 'ids',
		'suppress_filters' => true,
	) );

	foreach ( $kandidati as $id ) {
		bm_kralove_ulozit_soucet( $id );
	}

	update_option( 'bm_kralove_prepocet', '1.0.0', false );
}

/**
 * Přidá sloupec „Body celkem" před sloupec s datem.
 *
 * @param array $sloupce Sloupce seznamu.
 * @return array
 */
function bm_kralove_sloupec( $sloupce ) {
	$nove = array();

	foreach ( $sloupce as $klic => $nazev ) {
		if ( 'date' === $klic ) {
			$nove['bm_body'] = 'Body celkem';
		}
		$nove[ $klic ] = $nazev;
	}

	// Kdyby sloupec s datem chyběl (jiný doplněk ho umí odebrat), přidá se na konec.
	if ( ! isset( $nove['bm_body'] ) ) {
		$nove['bm_body'] = 'Body celkem';
	}

	return $nove;
}

/**
 * Vypíše součet. Záměrně počítaný, ne z uložené kopie — co tým v administraci
 * vidí, je vždycky pravda.
 *
 * @param string $sloupec Klíč sloupce.
 * @param int    $id      ID kandidáta.
 * @return void
 */
function bm_kralove_obsah_sloupce( $sloupec, $id ) {
	if ( 'bm_body' !== $sloupec ) {
		return;
	}

	echo esc_html( number_format_i18n( bm_kralove_body( $id ) ) );
}

/**
 * Zapne řazení sloupce.
 *
 * @param array $sloupce Řaditelné sloupce.
 * @return array
 */
function bm_kralove_sloupec_radit( $sloupce ) {
	$sloupce['bm_body'] = 'bm_body';

	return $sloupce;
}

/**
 * Přeloží řazení sloupce na řazení podle uložené kopie součtu.
 *
 * @param WP_Query $dotaz Dotaz.
 * @return void
 */
function bm_kralove_radit_podle_bodu( $dotaz ) {
	if ( ! is_admin() || ! $dotaz->is_main_query() ) {
		return;
	}

	if ( 'bm_body' !== $dotaz->get( 'orderby' ) || BM_KRALOVE_TYP !== $dotaz->get( 'post_type' ) ) {
		return;
	}

	$dotaz->set( 'meta_key', BM_KRALOVE_META_SOUCET );
	$dotaz->set( 'orderby', 'meta_value_num' );
}
