<?php
/**
 * Plugin Name: Majáles — králové (zavaděč)
 * Description: Načte doplněk ze složky bm-kralove/.
 * Version: 1.0.0
 *
 * Proč to existuje:
 * WordPress načítá z mu-plugins/ jen soubory .php ležící přímo v ní — do
 * podsložek se nedívá. Králové mají vedle PHP ještě vlastní styl, takže do
 * jednoho souboru nepatří. Stejný důvod i stejný tvar jako bm-program-nacti.php.
 *
 * Podmínka file_exists drží web naživu i ve chvíli, kdy někdo složku smaže.
 * Bez ní by smazání shodilo celý web včetně administrace.
 */

defined( 'ABSPATH' ) || exit;

$bm_kralove_soubor = __DIR__ . '/bm-kralove/bm-kralove.php';

if ( file_exists( $bm_kralove_soubor ) ) {
	require_once $bm_kralove_soubor;
}

unset( $bm_kralove_soubor );
