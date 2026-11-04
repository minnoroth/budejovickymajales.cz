<?php
/**
 * Plugin Name: Majáles — program (zavaděč)
 * Description: Načte doplněk ze složky bm-program/.
 * Version: 1.0.0
 *
 * Proč to existuje:
 * WordPress načítá z mu-plugins/ jen soubory .php ležící přímo v ní — do
 * podsložek se nedívá. Program má vedle PHP ještě dva skripty a styl, takže
 * do jednoho souboru nepatří. Tenhle zavaděč je ta jediná spojka mezi
 * WordPressem a složkou bm-program/.
 *
 * Proč ta podmínka file_exists:
 * Návrh programu stojí na testu izolace „smaž složku bm-program/ a web jede
 * dál". Bez podmínky by smazání složky shodilo celý web na fatální chybu,
 * a to včetně administrace — tedy přesný opak toho, co má test ověřovat.
 */

defined( 'ABSPATH' ) || exit;

$bm_program_soubor = __DIR__ . '/bm-program/bm-program.php';

if ( file_exists( $bm_program_soubor ) ) {
	require_once $bm_program_soubor;
}

unset( $bm_program_soubor );
