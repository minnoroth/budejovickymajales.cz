<?php
/**
 * Plugin Name: Majáles — zkopírovat do schránky
 * Description: K číslu účtu doplní ikonku ke zkopírování. Jen tam, kde je co kopírovat.
 * Version: 1.0.0
 *
 * Proč to existuje:
 * Na stránce Podpoř nás je číslo účtu záložní cesta pro toho, kdo nemůže
 * načíst QR kód — typicky protože se na stránku dívá z téhož telefonu, kterým
 * by skenoval. Opisovat šestnáct číslic z obrazovky je přesně ten okamžik,
 * kdy člověk přispívání vzdá.
 *
 * Proč tak málo PHP:
 * Soubor jen zařadí skript, a to jen na stránce, kde se třída opravdu
 * vyskytuje. Ikonku si vyrobí skript sám — viz komentář v kopirovat.js.
 *
 * Co se stane, když se tenhle soubor smaže:
 * Číslo účtu zůstane na stránce i nadále a půjde označit jedním kliknutím
 * (user-select: all v bm.css). Zmizí jen ikonka.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'bm_kopirovat_zarad' );

/**
 * Zařadí skript na stránce, která obsahuje kopírovatelnou hodnotu.
 *
 * @return void
 */
function bm_kopirovat_zarad() {
	$prispevek = get_post();

	if ( ! $prispevek instanceof WP_Post ) {
		return;
	}
	if ( false === strpos( $prispevek->post_content, 'bm-kopirovatelne' ) ) {
		return;
	}

	$soubor = plugin_dir_path( __FILE__ ) . 'kopirovat.js';

	wp_enqueue_script(
		'bm-kopirovat',
		plugins_url( 'kopirovat.js', __FILE__ ),
		array(),
		file_exists( $soubor ) ? (string) filemtime( $soubor ) : '1.0.0',
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
