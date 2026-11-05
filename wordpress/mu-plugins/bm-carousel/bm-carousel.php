<?php
/**
 * Plugin Name: Majáles — carousel na úvodu
 * Description: Zkratka [bm_carousel] vypíše vodorovný pruh zvýrazněných akcí.
 * Version: 1.0.0
 *
 * Proč to existuje:
 * Sekce „Nepřehlédni" na úvodní stránce byly tři natvrdo napsané kartičky.
 * Nebyla to data — byl to text, který někdo přepsal rukou z programu. Když se
 * akce zrušila nebo přesunula, úvodní stránka o tom nevěděla.
 *
 * Proč PHP, a ne JavaScript jako program:
 * Carousel je první věc pod hlavičkou. Kdyby se dokresloval z REST API jako
 * program, byla by nejdůležitější část úvodu při načtení prázdná a vyhledávač
 * by v ní neviděl nic. Vykreslení na serveru tohle obojí řeší.
 *
 * Cena, kterou to stojí: kartička akce existuje dvakrát — jednou tady v PHP
 * pro úvod, jednou v JavaScriptu v bm-program pro program. Je to vědomá
 * duplicita, ne opomenutí; alternativa je prázdný úvod při načtení.
 * Značkování je proto schválně stejné jako u ručních kartiček (.bm-karta
 * v bm.css), aby se vzhled nerozcházel na třech místech.
 *
 * Proč nativní „Top představení", a ne vlastní pole:
 * Byly na to dva přepínače — nativní Feature Event z The Events Calendar,
 * který čte program, a pole „V carouselu", které nečetlo nic. Dvě zaškrtávátka
 * se stejným významem, z nichž jedno je tiše mrtvé, je přesně ta věc, na které
 * se někdo při předávání webu zasekne. Zůstal nativní příznak.
 */

defined( 'ABSPATH' ) || exit;

/** Nejvýš tolik kartiček. Návod radí „nedávej do carouselu všechno". */
const BM_CAROUSEL_MAX = 8;

/**
 * Příznak „Top představení" z The Events Calendar.
 * Ověřeno ve zdrojáku: src/Tribe/Featured_Events.php zapisuje
 * update_post_meta( $id, '_tribe_featured', true ), tedy '1'.
 * Odškrtnutí meta rovnou maže, takže se nikdy neukládá nula.
 */
const BM_CAROUSEL_META_TOP = '_tribe_featured';

add_action( 'wp_enqueue_scripts', 'bm_carousel_zarad_predem' );
add_shortcode( 'bm_carousel', 'bm_carousel_zkratka' );

/**
 * Zařadí styl. Dvě volání ze stejného důvodu jako u programu: z hlavičky,
 * aby stránka neproblikla, a ze zkratky kvůli umístění mimo obsah stránky.
 *
 * @return void
 */
function bm_carousel_zarad() {
	$slozka = plugin_dir_path( __FILE__ );
	$verze  = static function ( $soubor ) use ( $slozka ) {
		// Verze = čas změny souboru. Na Wedosu není nic, čím jinak shodit cache.
		return file_exists( $slozka . $soubor ) ? (string) filemtime( $slozka . $soubor ) : '1.0.0';
	};

	wp_enqueue_style( 'bm-carousel', plugins_url( 'carousel.css', __FILE__ ), array(), $verze( 'carousel.css' ) );

	// Skript jen dokresluje tečky pod pruhem. Bez něj zůstane pruh posuvný
	// prstem i kolečkem, takže se odkládá do patičky a nic nezdržuje.
	wp_enqueue_script(
		'bm-carousel',
		plugins_url( 'carousel.js', __FILE__ ),
		array(),
		$verze( 'carousel.js' ),
		array( 'in_footer' => true, 'strategy' => 'defer' )
	);
}

/**
 * @return void
 */
function bm_carousel_zarad_predem() {
	$prispevek = get_post();

	if ( $prispevek instanceof WP_Post && has_shortcode( $prispevek->post_content, 'bm_carousel' ) ) {
		bm_carousel_zarad();
	}
}

/**
 * Termín ročníku, ze kterého se akce berou.
 *
 * Stejné pravidlo jako na stránce Program: akce se nevybírají podle taxonomie
 * Ročník, ale podle toho, jestli padnou do termínu ročníku. Tým totiž ročník
 * u akce běžně nezaškrtne a program by se tím rozpadl.
 *
 * Bere se nejnovější ročník, který nějaké zvýrazněné akce má. Po skončení
 * festivalu tak na úvodu zůstane poslední program místo prázdného místa.
 *
 * @return array{od:string, do:string}|null
 */
function bm_carousel_termin() {
	$rocniky = get_terms( array(
		'taxonomy'   => 'rocnik',
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'DESC',
	) );

	if ( is_wp_error( $rocniky ) ) {
		return null;
	}

	foreach ( $rocniky as $rocnik ) {
		$termin = bm_carousel_okno( $rocnik->term_id );
		if ( $termin && bm_carousel_akce( $termin, 1 ) ) {
			return $termin;
		}
	}

	return null;
}

/**
 * Časové okno ročníku.
 *
 * Konec je den po datum_do v 6:00 ráno, ne o půlnoci. Bez toho by vypadly
 * noční akce posledního dne — koncert od 23:00 do 1:30 začíná ještě
 * v termínu, ale končí už mimo něj.
 *
 * Secure Custom Fields ukládá datum jako Ymd (20270509). Čte se přes jádro
 * WordPressu, ne přes funkce SCF — stejně jako v bm-termin.php.
 *
 * @param int $rocnik_id ID ročníku.
 * @return array{od:string, do:string}|null
 */
function bm_carousel_okno( $rocnik_id ) {
	$od = get_term_meta( $rocnik_id, 'datum_od', true );
	$do = get_term_meta( $rocnik_id, 'datum_do', true );

	$od_ts = $od ? strtotime( (string) $od ) : false;
	$do_ts = $do ? strtotime( (string) $do ) : false;

	if ( ! $od_ts || ! $do_ts ) {
		return null;
	}

	// wp_date, ne gmdate: _EventStartDate je místní čas webu. gmdate by
	// u půlnoci v letním čase vrátil předchozí den a ročník by začínal
	// o den dřív — chyba, která se pozná až tím, že v carouselu chybí akce.
	// +1 den přes strtotime, ne přes DAY_IN_SECONDS, kvůli změně času.
	return array(
		'od' => wp_date( 'Y-m-d', $od_ts ) . ' 00:00:00',
		'do' => wp_date( 'Y-m-d', strtotime( '+1 day', $do_ts ) ) . ' 06:00:00',
	);
}

/**
 * Zvýrazněné akce v okně, seřazené podle začátku.
 *
 * Datum se čte z _EventStartDate, což je místní čas akce — tedy ten, se kterým
 * se porovnává termín ročníku. _EventStartDateUTC by u nočních akcí posunul den.
 *
 * @param array{od:string, do:string} $okno  Časové okno.
 * @param int                         $kolik Kolik nejvýš.
 * @return int[]
 */
function bm_carousel_akce( $okno, $kolik = BM_CAROUSEL_MAX ) {
	return get_posts( array(
		'post_type'        => 'tribe_events',
		'post_status'      => 'publish',
		'numberposts'      => $kolik,
		'fields'           => 'ids',
		'meta_key'         => '_EventStartDate', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'orderby'          => 'meta_value',
		'order'            => 'ASC',
		'no_found_rows'    => true,
		'meta_query'       => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'relation' => 'AND',
			array(
				'key'     => BM_CAROUSEL_META_TOP,
				'value'   => '1',
				'compare' => '=',
			),
			array(
				'key'     => '_EventStartDate',
				'value'   => array( $okno['od'], $okno['do'] ),
				'compare' => 'BETWEEN',
				'type'    => 'DATETIME',
			),
		),
	) );
}

/**
 * Vypíše carousel i s nadpisem.
 *
 * Nadpis je schválně uvnitř zkratky, ne nad ní ve stránce: když není co
 * ukázat, nesmí po sekci zůstat viset holý nadpis nad prázdnem. Takhle
 * zmizí celá sekce najednou.
 *
 * Atributy:
 *   nadpis  text nadpisu; prázdno = bez nadpisu
 *
 * @param array $atributy Atributy zkratky.
 * @return string
 */
function bm_carousel_zkratka( $atributy = array() ) {
	$a = shortcode_atts( array( 'nadpis' => 'Nepřehlédni' ), $atributy, 'bm_carousel' );

	bm_carousel_zarad();

	$okno = bm_carousel_termin();
	if ( ! $okno ) {
		return '';
	}

	$akce = bm_carousel_akce( $okno );
	if ( ! $akce ) {
		return '';
	}

	$nadpis = '' !== trim( (string) $a['nadpis'] )
		? '<h2 class="wp-block-heading">' . esc_html( trim( (string) $a['nadpis'] ) ) . '</h2>'
		: '';

	$karty = '';
	foreach ( $akce as $id ) {
		$karty .= bm_carousel_karta( $id );
	}

	// tabindex drží pruh ovladatelný z klávesnice — scroll-snap sám o sobě
	// ovladatelný není a bez toho se k akcím nedostane nikdo, kdo nemyší.
	return $nadpis
		. '<div class="bmc" tabindex="0" role="group" aria-label="Zvýrazněné akce programu">'
		. $karty . '</div>';
}

/**
 * Jedna kartička. Značkování je schválně stejné jako u ručních kartiček
 * v uvod.py, aby se styl držel na jednom místě v bm.css.
 *
 * @param int $id ID akce.
 * @return string
 */
function bm_carousel_karta( $id ) {
	$kat   = bm_carousel_kategorie( $id );
	$misto = bm_carousel_misto( $id );
	$kdy   = bm_carousel_kdy( $id );

	$obraz = get_the_post_thumbnail( $id, 'medium_large', array( 'loading' => 'lazy', 'alt' => '' ) );
	$obraz = $obraz
		? '<div class="bm-karta__obraz bmc-karta__obraz">' . $obraz . '</div>'
		: '<div class="bm-karta__obraz bmc-karta__obraz bmc-karta__obraz--bez"></div>';

	$popisky = array_filter( array( $kdy, $misto ) );

	return '<article class="bm-karta bmc-karta" data-kat="' . esc_attr( $kat['slug'] ) . '">'
		. '<a class="bmc-karta__odkaz" href="' . esc_url( get_permalink( $id ) ) . '">'
		. '<div class="bm-karta__pruh"></div>'
		. $obraz
		. '<div class="bm-karta__telo">'
		. '<p class="bm-karta__meta">' . esc_html( implode( ' · ', $popisky ) ) . '</p>'
		. '<h3 class="bm-karta__nazev">' . esc_html( get_the_title( $id ) ) . '</h3>'
		. '<div class="bmc-karta__stitky">'
		. ( $kat['nazev'] ? '<span class="bm-stitek">' . esc_html( $kat['nazev'] ) . '</span>' : '' )
		. '</div></div></a></article>';
}

/**
 * Nadřazená kategorie akce — ta nese barvu. Žánr (disco) barvu nemá,
 * má ji kategorie nad ním (Hudba); doplňuje ji bm-rubriky.php.
 *
 * @param int $id ID akce.
 * @return array{slug:string, nazev:string}
 */
function bm_carousel_kategorie( $id ) {
	$terminy = get_the_terms( $id, 'tribe_events_cat' );

	if ( ! is_array( $terminy ) ) {
		return array( 'slug' => '', 'nazev' => '' );
	}

	foreach ( $terminy as $termin ) {
		if ( 0 === (int) $termin->parent ) {
			return array( 'slug' => $termin->slug, 'nazev' => $termin->name );
		}
	}

	$prvni = reset( $terminy );

	return array( 'slug' => $prvni->slug, 'nazev' => $prvni->name );
}

/**
 * Název místa konání. Čte ID z meta The Events Calendar a dotáhne titulek —
 * žádná funkce pluginu se nevolá, takže to přežije jeho aktualizaci.
 *
 * @param int $id ID akce.
 * @return string
 */
function bm_carousel_misto( $id ) {
	$misto_id = (int) get_post_meta( $id, '_EventVenueID', true );

	return $misto_id ? (string) get_the_title( $misto_id ) : '';
}

/**
 * Kdy akce je — „neděle 18:00". Den v týdnu je pro návštěvníka během
 * festivalu čitelnější než datum.
 *
 * @param int $id ID akce.
 * @return string
 */
function bm_carousel_kdy( $id ) {
	$zacatek = (string) get_post_meta( $id, '_EventStartDate', true );
	$ts      = $zacatek ? strtotime( $zacatek ) : false;

	if ( ! $ts ) {
		return '';
	}

	// Názvy dnů se v češtině píšou malým písmenem. wp_date vrací „Úterý",
	// mb_strtolower z toho udělá „úterý" i s diakritikou.
	$den = wp_date( 'l', $ts );

	return mb_strtolower( $den, 'UTF-8' ) . ' ' . wp_date( 'G:i', $ts );
}
