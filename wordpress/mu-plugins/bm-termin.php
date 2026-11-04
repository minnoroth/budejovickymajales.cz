<?php
/**
 * Plugin Name: Majáles — termín ročníku
 * Description: Zkratka [bm_termin] vypíše termín festivalu z aktivního ročníku.
 * Version: 1.0.0
 *
 * Proč to existuje:
 * Termín byl na úvodní stránce napsaný natvrdo. Když tým v Ročnících změnil
 * datum, úvodní stránka dál hlásala loňský termín a nikdo si toho nevšiml —
 * návod přitom slibuje, že se pole „Text na homepage" na úvod propisuje.
 *
 * Čte term meta přímo přes jádro WordPressu, ne přes funkce Secure Custom
 * Fields. Kdyby se plugin s poli jednou vyměnil, zkratka funguje dál.
 */

defined( 'ABSPATH' ) || exit;

add_shortcode( 'bm_termin', 'bm_termin_rocniku' );

/**
 * Aktivní ročník je ten s nejvyšším názvem — názvy jsou roky (2027, 2028).
 * Návod zakazuje zakládat dva ročníky se stejným rokem, takže je to jednoznačné.
 */
function bm_aktivni_rocnik() {
	$terminy = get_terms( array(
		'taxonomy'   => 'rocnik',
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'DESC',
		'number'     => 1,
	) );

	if ( is_wp_error( $terminy ) || empty( $terminy ) ) {
		return null;
	}

	return $terminy[0];
}

/**
 * Vypíše termín festivalu. Přednost má ruční „Text na homepage", protože
 * tým v něm řeší i výjimky („30. 5. – 5. 6. 2027", „jen jeden den").
 * Když je prázdný, termín se poskládá z datumů.
 */
function bm_termin_rocniku() {
	$rocnik = bm_aktivni_rocnik();
	if ( ! $rocnik ) {
		return '';
	}

	$rucni = get_term_meta( $rocnik->term_id, 'text_na_homepage', true );
	if ( is_string( $rucni ) && '' !== trim( $rucni ) ) {
		return esc_html( trim( $rucni ) );
	}

	// Secure Custom Fields ukládá datum jako Ymd, například 20270509.
	$od = get_term_meta( $rocnik->term_id, 'datum_od', true );
	$do = get_term_meta( $rocnik->term_id, 'datum_do', true );

	$od_ts = $od ? strtotime( $od ) : false;
	$do_ts = $do ? strtotime( $do ) : false;

	if ( ! $od_ts || ! $do_ts ) {
		return esc_html( $rocnik->name );
	}

	// „9. 5. – 14. 5. 2027" — rok jen jednou, na konci.
	return esc_html( sprintf(
		'%s – %s',
		wp_date( 'j. n.', $od_ts ),
		wp_date( 'j. n. Y', $do_ts )
	) );
}
