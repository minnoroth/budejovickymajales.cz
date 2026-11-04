<?php
/**
 * Plugin Name: Majáles — dědičnost rubrik
 * Description: Když se u akce zaškrtne žánr, doplní se i kategorie nad ním.
 * Version: 1.1.0
 *
 * Proč to existuje:
 * Barva akce v programu se bere z kategorie (Hudba), ne ze žánru (disco).
 * WordPress ale při zaškrtnutí potomka rodiče nezaškrtne, takže akce snadno
 * skončí bez kategorie a v programu je bezbarvá. Nikdo si toho nevšimne,
 * dokud se nekouká na výpis.
 *
 * Doplněk je „must-use": nejde ho omylem vypnout a přežije změnu šablony.
 * Používá jen jádrové funkce WordPressu, takže ho nerozbije aktualizace
 * The Events Calendar.
 *
 * Proč hook set_object_terms a ne save_post:
 * REST API ukládá rubriky až po save_post, takže na save_post by doplněk
 * fungoval jen ve formuláři administrace a ne při zadávání přes API.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'set_object_terms', 'bm_doplnit_nadrazene_rubriky', 10, 4 );

/**
 * Ke každému zaškrtnutému žánru přidá jeho nadřazené kategorie.
 *
 * Nic nemaže — jen doplňuje. Když je všechno na místě, do databáze nesáhne.
 */
function bm_doplnit_nadrazene_rubriky( $object_id, $terms, $tt_ids, $taxonomy ) {
	if ( 'tribe_events_cat' !== $taxonomy ) {
		return;
	}

	// Vlastní zápis níž spustí tenhle hook znovu — jednou stačí.
	static $bezi = false;
	if ( $bezi ) {
		return;
	}

	$prirazene = wp_get_object_terms( $object_id, $taxonomy, array( 'fields' => 'ids' ) );
	if ( is_wp_error( $prirazene ) || empty( $prirazene ) ) {
		return;
	}

	$predkove = array();
	foreach ( $prirazene as $term_id ) {
		foreach ( get_ancestors( $term_id, $taxonomy, 'taxonomy' ) as $predek ) {
			$predkove[] = (int) $predek;
		}
	}

	$chybejici = array_diff( array_unique( $predkove ), $prirazene );
	if ( empty( $chybejici ) ) {
		return;
	}

	$bezi = true;
	// Čtvrtý parametr true = přidat, ne přepsat.
	wp_set_object_terms( $object_id, array_values( $chybejici ), $taxonomy, true );
	$bezi = false;
}
