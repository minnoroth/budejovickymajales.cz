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
 * Od 7. 11. 2026 si PHP tytéž veřejné endpointy zavolá i samo (vnitřně, přes
 * rest_do_request) a výsledek vloží do stránky, aby program nečekal na tři
 * požadavky za sebou. Pravidlo se tím neporušuje: mluví se pořád jen REST
 * API, jen bez odbočky přes síť. Podrobnosti dole u BM_PROGRAM_CACHE.
 *
 * Proč je záchranný blok rovnou v HTML a ne v <noscript>:
 * Kryje tři selhání naráz — vypnutý JavaScript, chybu ve skriptu i nedostupné
 * REST API. Ve <noscript> by poslední dvě nezabraly. Skript ho odstraní,
 * teprve až se program opravdu vykreslí.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'bm_program_zarad_predem' );
add_shortcode( 'bm_program', 'bm_program_zkratka' );


/* ──────────────────────────────────────────────────────────────────────────
   Program vložený rovnou do stránky

   Proč: každý požadavek na WordPress stojí na sdíleném hostingu kolem
   1,2 vteřiny, a to bez ohledu na množství dat — naměřeno 7. 11. 2026:
   statický soubor 0,13 s, 2,6 kB z REST 1,34 s, 544 kB z REST 1,20 s. Je to
   nastartování PHP a pluginů, ne přenos. Program potřeboval tři takové
   požadavky za sebou (termín ročníku se musí znát dřív, než se dá zeptat na
   akce), takže se vykresloval kolem čtyř vteřin.

   Stránka si svou 1,2 s platí tak jako tak. Když se do ní program vloží
   rovnou, vykreslí se v témže okamžiku a žádný další požadavek není.

   Co se tím NEMĚNÍ: javascript dál umí celou cestu přes REST. Vložená data
   jsou zkratka, ne nová závislost — když chybí, jsou poškozená nebo moc
   velká, program se doptá jako dřív a návštěvník nic nepozná.
   ────────────────────────────────────────────────────────────────────────── */

const BM_PROGRAM_CACHE = 'bm_program_vlozena_data';

// Která stránka nese zkratku. Hledá se podle obsahu, ne podle ID — ID
// stránky je na každé instalaci jiné a za rok by nikdo nevěděl, co znamená.
const BM_PROGRAM_STRANKA = 'bm_program_stranka';

// Záloha pro případ, že by některá cesta úprav neodpálila zneplatnění níž.
const BM_PROGRAM_PLATNOST = 15 * MINUTE_IN_SECONDS;

// Přes tenhle strop se do stránky radši nevkládá nic a program se doptá
// po svém. Ročník 2026 má 102 akcí a vejde se zhruba do 100 kB.
const BM_PROGRAM_STROP = 300 * 1024;

/**
 * Pole akce, která se vkládají do stránky.
 *
 * JE TO SMLOUVA S JAVASCRIPTEM. Čte je `upravAkci()` v program-data.js
 * a mapování akcí v program-view.js (hledej „AKCE = (DATA.akce"). Když
 * pohled začne používat další pole kalendáře, musí přibýt i sem — jinak
 * ho uvidí jen při načtení přes REST a ve vložených datech bude chybět.
 *
 * Proč se vůbec ořezává: syrová akce z kalendáře má přes 3 kB, z toho
 * polovinu v polích, která nikdo nečte (global_id_lineage, rest_url,
 * cost_details, organizer…). Ořezaná má 578 B.
 */
const BM_PROGRAM_POLE_AKCE = array(
	'id', 'title', 'description', 'excerpt', 'url', 'all_day', 'featured',
	'hide_from_listings', 'status', 'start_date_details', 'end_date_details',
	'ticketed',
);

add_filter( 'wp_robots', 'bm_program_skryj_nativni_pohledy' );

add_action( 'save_post_page', 'bm_program_zapomen_stranku' );
add_action( 'save_post_tribe_events', 'bm_program_zapomen' );
add_action( 'save_post_tribe_venue', 'bm_program_zapomen' );
add_action( 'deleted_post', 'bm_program_zapomen' );
add_action( 'created_term', 'bm_program_zapomen' );
add_action( 'edited_term', 'bm_program_zapomen' );
add_action( 'delete_term', 'bm_program_zapomen' );

/**
 * Zahodí vloženou kopii programu. Visí na úpravách akcí, míst a termínů;
 * co by se přes ně nepropsalo, dožene patnáctiminutová platnost.
 *
 * @return void
 */
function bm_program_zapomen() {
	delete_transient( BM_PROGRAM_CACHE );
}

/**
 * Schová nativní pohledy kalendáře před vyhledávači.
 *
 * Vypnout se nesmí — jsou to ty odkazy v záchranném bloku. Ve vyhledávání
 * ale nemají co dělat: `/events/list/` je druhý, horší program („Akce
 * na 11 května, 2027 – 14 května, 2027") a přetahoval by si návštěvníky
 * s tím naším. Zůstávají tedy dostupné, jen se na ně neodkazuje a nejsou
 * k nalezení.
 *
 * Stránky jednotlivých akcí se NESCHOVÁVAJÍ. Ty jsou naopak jediné, co
 * o programu ve vyhledávání vypovídá — mají vlastní adresu, kanonický
 * odkaz a schema.org/Event. Kdyby se noindex rozlil i na ně, přišel by
 * festival o akce ve vyhledávání.
 *
 * Dává se jen „noindex", ne „noindex, follow". Procházet odkazy je výchozí
 * chování, takže „follow" nic nepřidá — a dokud má celý web zaškrtnuté
 * odrazování vyhledávačů, přimíchalo by se k jeho „nofollow" a vzniklo by
 * protiřečící si „nofollow, follow".
 *
 * @param array<string,mixed> $pravidla Pravidla pro meta robots.
 * @return array<string,mixed>
 */
function bm_program_skryj_nativni_pohledy( $pravidla ) {
	if ( is_post_type_archive( 'tribe_events' ) || is_tax( 'tribe_events_cat' ) ) {
		$pravidla['noindex'] = true;
	}

	return $pravidla;
}

/**
 * Zahodí uloženou stránku s programem. Visí na uložení stránky — kdyby
 * někdo zkratku přesunul jinam, odkazy z úvodu by jinak vedly na starou.
 *
 * @return void
 */
function bm_program_zapomen_stranku() {
	delete_transient( BM_PROGRAM_STRANKA );
}

/**
 * ID stránky, která obsahuje zkratku [bm_program].
 *
 * @return int ID stránky, nebo 0, když žádná není.
 */
function bm_program_stranka() {
	$ulozene = get_transient( BM_PROGRAM_STRANKA );
	if ( false !== $ulozene ) {
		return (int) $ulozene;
	}

	$nalezeno = 0;
	$stranky  = get_posts( array(
		'post_type'        => 'page',
		'post_status'      => 'publish',
		'numberposts'      => 200,
		'suppress_filters' => false,
	) );

	foreach ( $stranky as $stranka ) {
		if ( has_shortcode( $stranka->post_content, 'bm_program' ) ) {
			$nalezeno = (int) $stranka->ID;
			break;
		}
	}

	set_transient( BM_PROGRAM_STRANKA, $nalezeno, DAY_IN_SECONDS );

	return $nalezeno;
}

/**
 * Adresa, na které se akce otevře — detail v programu, ne samostatná
 * stránka akce.
 *
 * Do 7. 11. 2026 vedly kartičky na úvodu na /event/…, protože program se
 * načítal kolem čtyř vteřin a posílat na něj někoho kvůli jedné akci bylo
 * zpomalení. Teď je program ve stránce hned, takže detail je rychlejší
 * i úplnější. Stránky akcí zůstávají — jsou kanonické pro vyhledávače
 * a drží sdílené odkazy — jen na ně z webu nic nevede.
 *
 * Když se stránka s programem nenajde, vrací se adresa akce jako dřív.
 *
 * @param int $id_akce ID akce.
 * @return string
 */
function bm_program_odkaz_na_akci( $id_akce ) {
	$stranka = bm_program_stranka();

	if ( ! $stranka ) {
		return (string) get_permalink( $id_akce );
	}

	return add_query_arg( 'akce', (int) $id_akce, get_permalink( $stranka ) );
}

/**
 * Jeden dotaz na vlastní REST API, bez HTTP a bez druhého startu WordPressu.
 *
 * @param string              $cesta     Například „/wp/v2/rocnik".
 * @param array<string,mixed> $parametry Parametry dotazu.
 * @return array<mixed>|null Tělo odpovědi, nebo null při chybě.
 */
function bm_program_rest( $cesta, $parametry = array() ) {
	$pozadavek = new WP_REST_Request( 'GET', $cesta );
	foreach ( $parametry as $klic => $hodnota ) {
		$pozadavek->set_param( $klic, $hodnota );
	}

	$odpoved = rest_do_request( $pozadavek );
	if ( $odpoved->is_error() ) {
		return null;
	}

	$data = rest_get_server()->response_to_data( $odpoved, false );

	return is_array( $data ) ? $data : null;
}

/**
 * Datum ze Secure Custom Fields na ISO.
 *
 * POZOR: stejné pravidlo má `naIso()` v program-data.js. Je tu podruhé
 * schválně — PHP musí znát rozsah ročníku dřív, než se může zeptat na akce,
 * takže bez něj se dotaz nedá složit. Kdyby se obě verze rozešly, projeví se
 * to tím, že se vloží jiný ročník, ne nesmyslná data.
 *
 * @param mixed $hodnota Hodnota z pole termínu.
 * @return string ISO datum, nebo prázdný řetězec.
 */
function bm_program_iso( $hodnota ) {
	$text = trim( (string) $hodnota );

	if ( preg_match( '/^\d{8}$/', $text ) ) {
		return substr( $text, 0, 4 ) . '-' . substr( $text, 4, 2 ) . '-' . substr( $text, 6, 2 );
	}
	if ( preg_match( '/^\d{4}-\d{2}-\d{2}/', $text ) ) {
		return substr( $text, 0, 10 );
	}

	return '';
}

/**
 * Rozsah dotazu na akce jednoho ročníku.
 *
 * Konec je o den dál v 6:00, aby nevypadly ranní sety po půlnoci. Hranici
 * 6:00 pak dodrží až filtr v javascriptu — TEC si hodinu stejně přepíše
 * na 23:59:59. Totéž dělá `strankaAkci()` v program-data.js.
 *
 * @param array<string,mixed> $termin Termín ročníku s klíči datum_od a datum_do.
 * @return array{0:string,1:string} Začátek a konec rozsahu.
 */
function bm_program_rozsah( $termin ) {
	$konec = gmdate( 'Y-m-d', strtotime( $termin['datum_do'] . ' +1 day' ) );

	return array( $termin['datum_od'] . ' 00:00:00', $konec . ' 06:00:00' );
}

/**
 * Poskládá program nejnovějšího ročníku, který nějaký má.
 *
 * Stejné pravidlo jako `vyberRocnik()` v program-data.js: rozhoduje datum
 * akce, ne zaškrtnutý ročník.
 *
 * @return array<string,mixed>|null Data pro javascript, nebo null.
 */
function bm_program_sestav() {
	$terminy = bm_program_rest( '/wp/v2/rocnik', array(
		'per_page' => 100,
		'_fields'  => 'id,name,slug,acf',
	) );
	if ( ! $terminy ) {
		return null;
	}

	// Nejnovější napřed. Názvy jsou roky, stejné pravidlo má bm-termin.php.
	usort( $terminy, static function ( $a, $b ) {
		return strnatcmp( (string) $b['name'], (string) $a['name'] );
	} );

	$vitez = null;
	$akce  = array();

	foreach ( $terminy as $termin ) {
		$acf = isset( $termin['acf'] ) && is_array( $termin['acf'] ) ? $termin['acf'] : array();
		$od  = bm_program_iso( isset( $acf['datum_od'] ) ? $acf['datum_od'] : '' );
		$do  = bm_program_iso( isset( $acf['datum_do'] ) ? $acf['datum_do'] : '' );
		if ( '' === $od || '' === $do ) {
			continue;
		}

		list( $zacatek, $konec ) = bm_program_rozsah( array( 'datum_od' => $od, 'datum_do' => $do ) );

		$stranka = bm_program_rest( '/tribe/events/v1/events', array(
			'per_page'   => 50,
			'page'       => 1,
			'start_date' => $zacatek,
			'end_date'   => $konec,
			'status'     => 'publish',
		) );

		if ( ! $stranka || empty( $stranka['total'] ) ) {
			continue;
		}

		$vitez = $termin;
		$akce  = isset( $stranka['events'] ) ? $stranka['events'] : array();

		// Strop stránkování je u kalendáře 50 i tehdy, když se požádá o víc.
		$stranek = min( (int) ( isset( $stranka['total_pages'] ) ? $stranka['total_pages'] : 1 ), 40 );
		for ( $i = 2; $i <= $stranek; $i++ ) {
			$dalsi = bm_program_rest( '/tribe/events/v1/events', array(
				'per_page'   => 50,
				'page'       => $i,
				'start_date' => $zacatek,
				'end_date'   => $konec,
				'status'     => 'publish',
			) );
			if ( $dalsi && ! empty( $dalsi['events'] ) ) {
				$akce = array_merge( $akce, $dalsi['events'] );
			}
		}
		break;
	}

	if ( ! $vitez ) {
		// Ročník bez programu není chyba — javascript na to má stav
		// „Program se připravuje". Vložíme tedy termín a prázdné akce.
		$vitez = isset( $terminy[0] ) ? $terminy[0] : null;
	}

	return array(
		'rocnik'    => $vitez,
		'akce'      => array_map( 'bm_program_orez_akci', $akce ),
		'kategorie' => bm_program_vsechny( '/wp/v2/tribe_events_cat', 'id,name,slug,parent,acf' ),
		'mista'     => bm_program_vsechny( '/wp/v2/tribe_venue', 'id,slug,link,title,acf' ),
	);
}

/**
 * Všechny stránky jednoho seznamu z wp/v2. Konec se pozná kratší odpovědí.
 *
 * @param string $cesta Cesta endpointu.
 * @param string $pole  Seznam polí pro _fields.
 * @return array<mixed>
 */
function bm_program_vsechny( $cesta, $pole ) {
	$vse = array();

	for ( $stranka = 1; $stranka <= 10; $stranka++ ) {
		$davka = bm_program_rest( $cesta, array(
			'per_page' => 100,
			'page'     => $stranka,
			'_fields'  => $pole,
		) );
		if ( ! $davka ) {
			break;
		}
		$vse = array_merge( $vse, $davka );
		if ( count( $davka ) < 100 ) {
			break;
		}
	}

	return $vse;
}

/**
 * Ořeže akci na pole, která javascript opravdu čte. Viz BM_PROGRAM_POLE_AKCE.
 *
 * @param array<string,mixed> $akce Syrová akce z kalendáře.
 * @return array<string,mixed>
 */
function bm_program_orez_akci( $akce ) {
	$orez = array();

	foreach ( BM_PROGRAM_POLE_AKCE as $klic ) {
		if ( array_key_exists( $klic, $akce ) ) {
			$orez[ $klic ] = $akce[ $klic ];
		}
	}

	// Vnořené objekty se ořezávají zvlášť — plné mají přes kilobajt.
	$misto = isset( $akce['venue'] ) && is_array( $akce['venue'] ) ? $akce['venue'] : array();
	if ( $misto ) {
		$orez['venue'] = array_intersect_key( $misto, array_flip( array( 'id', 'slug', 'venue', 'city' ) ) );
	}

	$obraz = isset( $akce['image'] ) && is_array( $akce['image'] ) ? $akce['image'] : array();
	if ( ! empty( $obraz['url'] ) ) {
		$orez['image'] = array( 'url' => $obraz['url'] );
	}

	$orez['categories'] = array_map( static function ( $kategorie ) {
		return array_intersect_key( $kategorie, array_flip( array( 'id', 'name', 'slug', 'parent' ) ) );
	}, isset( $akce['categories'] ) && is_array( $akce['categories'] ) ? $akce['categories'] : array() );

	$orez['tags'] = array_map( static function ( $stitek ) {
		return array_intersect_key( $stitek, array_flip( array( 'id', 'name' ) ) );
	}, isset( $akce['tags'] ) && is_array( $akce['tags'] ) ? $akce['tags'] : array() );

	return $orez;
}

/**
 * Vložená data programu, z mezipaměti nebo čerstvá.
 *
 * Skládá se vždycky jako nepřihlášený návštěvník. Kdyby to běželo pod
 * redaktorem, mohly by se do mezipaměti dostat rozepsané akce a pak by se
 * ukazovaly všem.
 *
 * @return array<string,mixed>|null
 */
function bm_program_data() {
	$cerstve = isset( $_GET['program'] ) && 'cerstvy' === $_GET['program']; // phpcs:ignore WordPress.Security.NonceVerification

	if ( ! $cerstve ) {
		$ulozene = get_transient( BM_PROGRAM_CACHE );
		if ( is_array( $ulozene ) ) {
			return $ulozene;
		}
	}

	$puvodni = get_current_user_id();
	wp_set_current_user( 0 );

	try {
		$data = bm_program_sestav();
	} catch ( Throwable $e ) {
		$data = null;
	}

	wp_set_current_user( $puvodni );

	if ( is_array( $data ) ) {
		set_transient( BM_PROGRAM_CACHE, $data, BM_PROGRAM_PLATNOST );
	}

	return $data;
}

/**
 * Data jako blok JSON do stránky.
 *
 * JSON_HEX_TAG je tu kvůli bezpečnosti, ne kvůli kráse: bez něj by
 * `</script>` v popisu akce ukončilo blok a zbytek popisu by se stal
 * HTML stránky. S ním je z lomené závorky < a ukončit blok nejde.
 *
 * @return string
 */
function bm_program_vlozena_data() {
	$data = bm_program_data();
	if ( ! is_array( $data ) ) {
		return '';
	}

	$json = wp_json_encode(
		$data,
		JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
	);

	if ( ! is_string( $json ) || strlen( $json ) > BM_PROGRAM_STROP ) {
		return '';
	}

	return '<script type="application/json" id="bm-program-vlozena-data">' . $json . '</script>';
}

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
			// Tahle věta visí na stránce i během načítání, které při studené
			// cache trvá několik sekund. Nesmí proto znít jako hlášení o chybě
			// — musí dávat smysl, ať se program nakonec vykreslí, nebo ne.
			. '<p>Program se načítá. Celý je dostupný i v kalendáři akcí:</p>'
			. '<ul>' . $polozky . '</ul>'
			. '</div>'
			. bm_program_vlozena_data()
			. '</div>';
	} catch ( Throwable $e ) {
		// I když selže zařazení souborů, musí ze zkratky vypadnout odkaz
		// do kalendáře. Prázdná stránka Program je horší než strohý seznam.
		return '<div class="bmp-nouze"><h2>Program</h2>'
			. '<p><a href="' . esc_url( home_url( '/events/list/' ) ) . '">Výpis všech akcí v kalendáři</a></p></div>';
	}
}
