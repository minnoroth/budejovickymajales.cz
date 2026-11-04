<?php
/**
 * Plugin Name: Majáles — program a lineup
 * Description: Zkratka [bm_program] vykreslí program ročníku. PHP jen zařadí soubory a vypíše záchranný blok.
 * Version: 1.0.0
 *
 * Proč to existuje:
 * Zkratka [tribe_events] je jen v placené verzi The Events Calendar a nativní
 * pohledy neumí časovou osu patnácti scén vedle sebe. Program je přitom
 * celoročně nejnavštěvovanější stránka webu, takže na ni nestačí odkaz do
 * kalendáře. Návrh je v docs/wordpress/11-lineup-navrh/.
 *
 * Proč je tenhle soubor tak hloupý:
 * Všechna logika je v JavaScriptu a čte veřejné REST endpointy. PHP nevolá
 * jedinou funkci The Events Calendar, nesahá na šablony pluginu a nezapisuje
 * do databáze. Když TEC přepíše vnitřnosti, nás se to netýká — rozbít nás umí
 * jen změna REST API, a to je vidět hned.
 *
 * Proč je záchranný blok rovnou v HTML a ne v <noscript>:
 * Kryje tři selhání naráz — vypnutý JavaScript, chybu ve skriptu i nedostupné
 * REST API. Ve <noscript> by poslední dvě nezabraly. Skript ho odstraní,
 * teprve až se program opravdu vykreslí.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'bm_program_zarad_predem' );
add_shortcode( 'bm_program', 'bm_program_zkratka' );

/**
 * Zařadí styl a skripty. Volá se ze dvou míst, protože každé samo o sobě
 * má díru: z wp_enqueue_scripts kvůli tomu, aby se CSS stihlo do hlavičky
 * a stránka neproblikla, a ze zkratky kvůli umístění mimo obsah stránky
 * (widget, šablona). Druhé volání nic nestojí, wp_enqueue_* je idempotentní.
 */
function bm_program_zarad() {
	$slozka = plugin_dir_path( __FILE__ );
	$adresa = plugins_url( '', __FILE__ );

	// Verze = čas změny souboru. Na Wedosu není nic, čím jinak shodit cache.
	$verze = static function ( $soubor ) use ( $slozka ) {
		return file_exists( $slozka . $soubor ) ? (string) filemtime( $slozka . $soubor ) : '1.0.0';
	};

	$v_paticce = array(
		'in_footer' => true,
		'strategy'  => 'defer',
	);

	wp_enqueue_style( 'bm-program', $adresa . '/program.css', array(), $verze( 'program.css' ) );
	wp_enqueue_script( 'bm-program-data', $adresa . '/program-data.js', array(), $verze( 'program-data.js' ), $v_paticce );
	wp_enqueue_script( 'bm-program-view', $adresa . '/program-view.js', array( 'bm-program-data' ), $verze( 'program-view.js' ), $v_paticce );

	// Základ REST API. Musí jít „before", ne „after" — odložený skript se
	// spustí až po dokreslení stránky, takže „after" by běželo dřív než on.
	wp_add_inline_script(
		'bm-program-data',
		'window.bmProgram = window.bmProgram || {}; window.bmProgram.zaklad = ' . wp_json_encode( esc_url_raw( rest_url() ) ) . ';',
		'before'
	);
}

/**
 * Soubory se zařadí jen na stránce, která zkratku opravdu obsahuje. Jinde
 * by se zbytečně stahovalo přes 1 500 řádků JS a CSS.
 */
function bm_program_zarad_predem() {
	$prispevek = get_post();

	if ( $prispevek instanceof WP_Post && has_shortcode( $prispevek->post_content, 'bm_program' ) ) {
		bm_program_zarad();
	}
}

/**
 * Vypíše prázdný kořen a v něm záchranný blok. Nic víc — celý program si
 * do kořene vykreslí program-view.js a blok odstraní, až se mu to povede.
 */
function bm_program_zkratka() {
	try {
		bm_program_zarad();

		// Nativní pohledy The Events Calendar se nikdy nevypínají, protože
		// tohle jsou ty odkazy, na kterých stojí záchranná brzda. Adresy jdou
		// v nastavení TEC přejmenovat; kdyby se to stalo, opraví se tady.
		$odkazy = array(
			'/events/list/'  => 'Výpis všech akcí',
			'/events/month/' => 'Kalendář po měsících',
			'/events/today/' => 'Dnešní den',
		);

		$polozky = '';
		foreach ( $odkazy as $cesta => $popis ) {
			$polozky .= '<li><a href="' . esc_url( home_url( $cesta ) ) . '">' . esc_html( $popis ) . '</a></li>';
		}

		return '<div id="bm-program" class="bmp" data-bm-zaklad="' . esc_url( rest_url() ) . '">'
			. '<div id="bm-nouze" class="bmp-nouze">'
			. '<h2>Program</h2>'
			. '<p>Interaktivní program se nenačetl. Celý program je dostupný i v kalendáři akcí:</p>'
			. '<ul>' . $polozky . '</ul>'
			. '</div></div>';
	} catch ( Throwable $e ) {
		// I když selže zařazení souborů, musí ze zkratky vypadnout odkaz
		// do kalendáře. Prázdná stránka Program je horší než strohý seznam.
		return '<div class="bmp-nouze"><h2>Program</h2>'
			. '<p><a href="' . esc_url( home_url( '/events/list/' ) ) . '">Výpis všech akcí v kalendáři</a></p></div>';
	}
}
