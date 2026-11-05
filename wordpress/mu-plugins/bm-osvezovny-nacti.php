<?php
/**
 * Plugin Name: Majáles — osvěžovny (zavaděč)
 * Description: Načte doplněk ze složky bm-osvezovny/.
 * Version: 1.0.0
 *
 * WordPress načítá z mu-plugins/ jen soubory .php ležící přímo v ní — do
 * podsložek se nedívá. Stejný důvod i stejný tvar jako bm-program-nacti.php.
 * Podmínka file_exists drží web naživu i ve chvíli, kdy někdo složku smaže.
 */

defined( 'ABSPATH' ) || exit;

$bm_osvezovny_soubor = __DIR__ . '/bm-osvezovny/bm-osvezovny.php';

if ( file_exists( $bm_osvezovny_soubor ) ) {
	require_once $bm_osvezovny_soubor;
}

unset( $bm_osvezovny_soubor );
