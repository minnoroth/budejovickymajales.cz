<?php
/**
 * Plugin Name: Majáles — carousel (zavaděč)
 * Description: Načte doplněk ze složky bm-carousel/.
 * Version: 1.0.0
 *
 * WordPress načítá z mu-plugins/ jen soubory .php ležící přímo v ní — do
 * podsložek se nedívá. Stejný důvod i stejný tvar jako bm-program-nacti.php.
 * Podmínka file_exists drží web naživu i ve chvíli, kdy někdo složku smaže.
 */

defined( 'ABSPATH' ) || exit;

$bm_carousel_soubor = __DIR__ . '/bm-carousel/bm-carousel.php';

if ( file_exists( $bm_carousel_soubor ) ) {
	require_once $bm_carousel_soubor;
}

unset( $bm_carousel_soubor );
