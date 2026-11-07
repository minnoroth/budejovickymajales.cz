<?php
/**
 * Plugin Name: Majáles — uložit QR kód
 * Description: Na telefonu nabídne uložení QR platby, protože naskenovat se nedá.
 * Version: 1.0.0
 *
 * Proč to existuje:
 * Telefon se nevyfotí sám, takže QR kód na stránce Podpoř nás je na mobilu
 * k ničemu. Tenhle doplněk tam přidá odkaz, kterým se kód uloží do telefonu
 * a v bankovní aplikaci načte z galerie. Je to jediná cesta, která opravdu
 * funguje — platbu z odkazu ve webu žádná česká banka nepřijme.
 *
 * Proč tak málo PHP:
 * Soubor jen zařadí skript, a to jen na stránce, kde nějaký kód je.
 * Odkaz si vyrobí skript sám — viz komentář v sdilet-qr.js.
 *
 * Co se stane, když se tenhle soubor smaže:
 * Kód na stránce zůstane, zmizí jen odkaz na jeho uložení. Číslo účtu
 * s ikonkou ke zkopírování funguje dál.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'bm_sdilet_qr_zarad' );

/**
 * Zařadí skript na stránce, která obsahuje QR kód.
 *
 * @return void
 */
function bm_sdilet_qr_zarad() {
	$prispevek = get_post();

	if ( ! $prispevek instanceof WP_Post ) {
		return;
	}
	if ( false === strpos( $prispevek->post_content, 'bm-qr__kod' ) ) {
		return;
	}

	$soubor = plugin_dir_path( __FILE__ ) . 'sdilet-qr.js';

	wp_enqueue_script(
		'bm-sdilet-qr',
		plugins_url( 'sdilet-qr.js', __FILE__ ),
		array(),
		file_exists( $soubor ) ? (string) filemtime( $soubor ) : '1.0.0',
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
