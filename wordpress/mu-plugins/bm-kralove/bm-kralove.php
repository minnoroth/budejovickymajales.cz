<?php
/**
 * Plugin Name: Majáles — králové
 * Description: Součet bodů kandidáta, řaditelný sloupec v administraci a zkratka [bm_kralove].
 * Version: 1.0.0
 *
 * Proč to existuje:
 * Body kandidáta se zadávají po disciplínách (opakovatelné pole „Body"), ale
 * všude — v žebříčku i v administraci — se pracuje s jejich součtem. WordPress
 * opakovatelná pole sčítat neumí a žádná zkratka na výpis kandidátů neexistuje.
 *
 * Proč se součet počítá při každém čtení:
 * Uložené odvozené číslo se dřív nebo později rozejde se skutečností. Součet se
 * proto nikdy nebere z databáze — vždy se spočítá z bodů. Uložená kopie
 * (_bm_body_celkem) slouží jedinému účelu: řazení sloupce v administraci,
 * protože podle spočítané hodnoty WordPress řadit neumí. Kdyby se kopie
 * rozešla, pozná se to jen na pořadí řádků v administraci; vypsané číslo
 * i veřejný žebříček jsou vždy správně.
 *
 * Proč je kopie napojená na zápis meta, a ne na acf/save_post:
 * Secure Custom Fields pouští acf/save_post jen u uložení z formuláře nebo
 * z editoru. Zápis přes REST API (import, skript, jiný doplněk) ho nespustí —
 * říká to komentář přímo ve zdrojáku SCF: „updates fields without firing
 * acf/save_post". Zato každý z těch zápisů jde přes update_metadata(), a na tu
 * je napojený háček níž. Jde tedy o jedinou cestu, která nejde obejít.
 */

defined( 'ABSPATH' ) || exit;

/** Typ obsahu s kandidáty. */
const BM_KRALOVE_TYP = 'kral';

/** Uložená kopie součtu. Podtržítko na začátku ji skryje z vlastních polí i z REST. */
const BM_KRALOVE_META_SOUCET = '_bm_body_celkem';

/** Opakovatelné pole s body. Podpole s počtem se jmenuje „pocet". */
const BM_KRALOVE_POLE_BODY = 'body';

add_action( 'added_post_meta', 'bm_kralove_po_zapisu_meta', 10, 3 );
add_action( 'updated_post_meta', 'bm_kralove_po_zapisu_meta', 10, 3 );
add_action( 'deleted_post_meta', 'bm_kralove_po_zapisu_meta', 10, 3 );
add_action( 'admin_init', 'bm_kralove_dopocitat_chybejici' );
add_action( 'wp_enqueue_scripts', 'bm_kralove_zarad_predem' );
add_shortcode( 'bm_kralove', 'bm_kralove_zkratka' );

add_filter( 'manage_' . BM_KRALOVE_TYP . '_posts_columns', 'bm_kralove_sloupec' );
add_action( 'manage_' . BM_KRALOVE_TYP . '_posts_custom_column', 'bm_kralove_obsah_sloupce', 10, 2 );
add_filter( 'manage_edit-' . BM_KRALOVE_TYP . '_sortable_columns', 'bm_kralove_sloupec_radit' );
add_action( 'pre_get_posts', 'bm_kralove_radit_podle_bodu' );

/**
 * Součet bodů kandidáta ze všech disciplín.
 *
 * Čte meta přímo přes jádro WordPressu, ne přes funkce Secure Custom Fields —
 * stejně jako bm-termin.php. Kdyby se plugin s poli jednou vyměnil, součet
 * funguje dál, protože tvar uložení (body = počet řádků, body_0_pocet = první
 * řádek) je daný databází, ne pluginem.
 *
 * @param int $id ID kandidáta.
 * @return int Součet bodů, nebo 0.
 */
function bm_kralove_body( $id ) {
	$radku = (int) get_post_meta( $id, BM_KRALOVE_POLE_BODY, true );

	// Záporný nebo nesmyslně velký počet řádků by znamenal poškozená data.
	// Strop drží smyčku konečnou, i kdyby se to stalo.
	if ( $radku < 1 ) {
		return 0;
	}
	$radku = min( $radku, 100 );

	$soucet = 0;
	for ( $i = 0; $i < $radku; $i++ ) {
		$soucet += (int) get_post_meta( $id, BM_KRALOVE_POLE_BODY . '_' . $i . '_pocet', true );
	}

	return $soucet;
}

/**
 * Uloží kopii součtu pro řazení v administraci.
 *
 * @param int $id ID kandidáta.
 * @return void
 */
function bm_kralove_ulozit_soucet( $id ) {
	update_post_meta( $id, BM_KRALOVE_META_SOUCET, bm_kralove_body( $id ) );
}

/**
 * Po každém zápisu bodů přepočítá uloženou kopii.
 *
 * Háček dostane jméno meta, takže se pozná zápis počtu řádků („body") i zápis
 * jednotlivého řádku („body_3_pocet"). Jméno uložené kopie se vzorku netrefí,
 * takže se háček nevolá sám od sebe donekonečna.
 *
 * Jeden uložený kandidát spustí háček několikrát (za každý řádek). To nevadí:
 * update_post_meta beze změny hodnoty do databáze nesáhne, takže skutečný
 * zápis proběhne nejvýš jednou.
 *
 * @param int    $meta_id   ID záznamu meta (nepoužívá se).
 * @param int    $id        ID příspěvku.
 * @param string $jmeno     Jméno meta.
 * @return void
 */
function bm_kralove_po_zapisu_meta( $meta_id, $id, $jmeno ) {
	if ( BM_KRALOVE_POLE_BODY !== $jmeno
		&& ! preg_match( '/^' . BM_KRALOVE_POLE_BODY . '_\d+_pocet$/', (string) $jmeno ) ) {
		return;
	}

	if ( BM_KRALOVE_TYP !== get_post_type( $id ) ) {
		return;
	}

	bm_kralove_ulozit_soucet( $id );
}

/**
 * Jednorázově doplní uloženou kopii kandidátům, kteří vznikli dřív než doplněk.
 *
 * Bez toho by řazení sloupce takové kandidáty vynechalo — WordPress při řazení
 * podle meta zahodí příspěvky, které ji nemají, a zmizeli by ze seznamu, aniž
 * by to komukoli došlo.
 *
 * Musí to být háček na admin_init: doplňky v mu-plugins nemají aktivaci.
 * Volba v databázi hlídá, aby se smyčka přes všechny kandidáty spustila jednou.
 *
 * @return void
 */
function bm_kralove_dopocitat_chybejici() {
	if ( '1.0.0' === get_option( 'bm_kralove_prepocet' ) ) {
		return;
	}

	$kandidati = get_posts( array(
		'post_type'        => BM_KRALOVE_TYP,
		'post_status'      => 'any',
		'numberposts'      => -1,
		'fields'           => 'ids',
		'suppress_filters' => true,
	) );

	foreach ( $kandidati as $id ) {
		bm_kralove_ulozit_soucet( $id );
	}

	update_option( 'bm_kralove_prepocet', '1.0.0', false );
}

/**
 * Přidá sloupec „Body celkem" před sloupec s datem.
 *
 * @param array $sloupce Sloupce seznamu.
 * @return array
 */
function bm_kralove_sloupec( $sloupce ) {
	$nove = array();

	foreach ( $sloupce as $klic => $nazev ) {
		if ( 'date' === $klic ) {
			$nove['bm_body'] = 'Body celkem';
		}
		$nove[ $klic ] = $nazev;
	}

	// Kdyby sloupec s datem chyběl (jiný doplněk ho umí odebrat), přidá se na konec.
	if ( ! isset( $nove['bm_body'] ) ) {
		$nove['bm_body'] = 'Body celkem';
	}

	return $nove;
}

/**
 * Vypíše součet. Záměrně počítaný, ne z uložené kopie — co tým v administraci
 * vidí, je vždycky pravda.
 *
 * @param string $sloupec Klíč sloupce.
 * @param int    $id      ID kandidáta.
 * @return void
 */
function bm_kralove_obsah_sloupce( $sloupec, $id ) {
	if ( 'bm_body' !== $sloupec ) {
		return;
	}

	echo esc_html( number_format_i18n( bm_kralove_body( $id ) ) );
}

/**
 * Zapne řazení sloupce.
 *
 * @param array $sloupce Řaditelné sloupce.
 * @return array
 */
function bm_kralove_sloupec_radit( $sloupce ) {
	$sloupce['bm_body'] = 'bm_body';

	return $sloupce;
}

/**
 * Přeloží řazení sloupce na řazení podle uložené kopie součtu.
 *
 * @param WP_Query $dotaz Dotaz.
 * @return void
 */
function bm_kralove_radit_podle_bodu( $dotaz ) {
	if ( ! is_admin() || ! $dotaz->is_main_query() ) {
		return;
	}

	if ( 'bm_body' !== $dotaz->get( 'orderby' ) || BM_KRALOVE_TYP !== $dotaz->get( 'post_type' ) ) {
		return;
	}

	$dotaz->set( 'meta_key', BM_KRALOVE_META_SOUCET );
	$dotaz->set( 'orderby', 'meta_value_num' );
}

/**
 * Ročník, jehož žebříček se vypíše.
 *
 * Bere nejnovější ročník, který má aspoň jednoho kandidáta — ne prostě
 * nejnovější ročník. Rozdíl je vidět na podzim: ročník 2028 už v Akcích
 * existuje, kandidáty ještě nemá, a stránka by byla prázdná. Takhle na ní
 * zůstane loňské konečné pořadí, dokud školy nenahlásí nové kandidáty.
 * Aby to nemátlo, vypisuje se nad žebříčkem vždycky rok.
 *
 * @param string $zadany Název nebo slug ročníku ze zkratky, nebo prázdno.
 * @return WP_Term|null
 */
function bm_kralove_rocnik( $zadany = '' ) {
	if ( '' !== $zadany ) {
		$rocnik = get_term_by( 'slug', $zadany, 'rocnik' );
		if ( ! $rocnik ) {
			$rocnik = get_term_by( 'name', $zadany, 'rocnik' );
		}

		return $rocnik instanceof WP_Term ? $rocnik : null;
	}

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
		if ( bm_kralove_ids( $rocnik->term_id, 1 ) ) {
			return $rocnik;
		}
	}

	return null;
}

/**
 * ID publikovaných kandidátů ročníku.
 *
 * @param int $rocnik_id ID ročníku.
 * @param int $kolik     Kolik nejvýš, -1 pro všechny.
 * @return int[]
 */
function bm_kralove_ids( $rocnik_id, $kolik = -1 ) {
	return get_posts( array(
		'post_type'        => BM_KRALOVE_TYP,
		'post_status'      => 'publish',
		'numberposts'      => $kolik,
		'fields'           => 'ids',
		'orderby'          => 'menu_order title',
		'order'            => 'ASC',
		'no_found_rows'    => true,
		'tax_query'        => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy' => 'rocnik',
				'field'    => 'term_id',
				'terms'    => $rocnik_id,
			),
		),
	) );
}

/**
 * Seřadí kandidáty podle bodů sestupně.
 *
 * Řadí se v PHP ze spočítaných součtů, ne dotazem podle uložené kopie —
 * veřejné pořadí tak nemůže být špatně ani ve chvíli, kdy se kopie rozejde.
 * Kandidátů je dvacet na ročník, takže na tom nezáleží výkonnostně.
 *
 * Při shodě bodů rozhoduje pořadí z pole „Pořadí" a pak abeceda. Bez toho by
 * se nerozhodnutí kandidáti na začátku ročníku (všichni nula) přehazovali
 * při každém načtení stránky.
 *
 * @param int[] $ids ID kandidátů, už seřazená podle Pořadí a abecedy.
 * @return array<int, array{id:int, body:int}>
 */
function bm_kralove_poradi( $ids ) {
	$radky = array();
	foreach ( $ids as $poradi => $id ) {
		$radky[] = array(
			'id'      => (int) $id,
			'body'    => bm_kralove_body( $id ),
			'vstupni' => $poradi,
		);
	}

	usort( $radky, static function ( $a, $b ) {
		if ( $a['body'] === $b['body'] ) {
			return $a['vstupni'] <=> $b['vstupni'];
		}

		return $b['body'] <=> $a['body'];
	} );

	return $radky;
}

/**
 * Kotva kandidáta pro odkaz zvenčí.
 *
 * Instagram škol i starý web odkazují na /kralove#gymji, kde „gymji" je název
 * školy bez diakritiky malými písmeny. Tahle pravidla dělá sanitize_title().
 *
 * Shodu ošetřuje druhý parametr: na starém webu mělo šest kandidátů školu „t"
 * a sdíleli jednu kotvu, takže odkaz skočil na náhodného z nich.
 *
 * @param string   $skola    Název školy.
 * @param string[] $obsazene Už použité kotvy, předává se odkazem.
 * @return string
 */
function bm_kralove_kotva( $skola, &$obsazene ) {
	$zaklad = sanitize_title( $skola );
	if ( '' === $zaklad ) {
		$zaklad = 'kandidat';
	}

	$kotva = $zaklad;
	$poradi = 2;
	while ( in_array( $kotva, $obsazene, true ) ) {
		$kotva = $zaklad . '-' . $poradi;
		$poradi++;
	}

	$obsazene[] = $kotva;

	return $kotva;
}

/**
 * Zařadí styl. Volá se ze dvou míst ze stejného důvodu jako u programu:
 * z wp_enqueue_scripts, aby se CSS stihlo do hlavičky a stránka neproblikla,
 * a ze zkratky kvůli umístění mimo obsah stránky (widget, šablona).
 *
 * @return void
 */
function bm_kralove_zarad() {
	$soubor = plugin_dir_path( __FILE__ ) . 'kralove.css';
	$verze  = file_exists( $soubor ) ? (string) filemtime( $soubor ) : '1.0.0';

	wp_enqueue_style( 'bm-kralove', plugins_url( 'kralove.css', __FILE__ ), array(), $verze );
}

/**
 * Styl se zařadí jen na stránce, která zkratku opravdu obsahuje.
 *
 * @return void
 */
function bm_kralove_zarad_predem() {
	$prispevek = get_post();

	if ( $prispevek instanceof WP_Post && has_shortcode( $prispevek->post_content, 'bm_kralove' ) ) {
		bm_kralove_zarad();
	}
}

/**
 * Vypíše žebříček kandidátů.
 *
 * Atributy:
 *   rocnik  název nebo slug ročníku; prázdno = nejnovější s kandidáty
 *   pocet   kolik kandidátů vypsat; 0 = všechny (úvodní stránka chce tři)
 *
 * @param array $atributy Atributy zkratky.
 * @return string
 */
function bm_kralove_zkratka( $atributy = array() ) {
	$a = shortcode_atts( array(
		'rocnik' => '',
		'pocet'  => 0,
	), $atributy, 'bm_kralove' );

	bm_kralove_zarad();

	$rocnik = bm_kralove_rocnik( (string) $a['rocnik'] );
	if ( ! $rocnik ) {
		return bm_kralove_prazdno();
	}

	$radky = bm_kralove_poradi( bm_kralove_ids( $rocnik->term_id ) );
	if ( ! $radky ) {
		return bm_kralove_prazdno();
	}

	// Pruh se měří k vedoucímu, ne k součtu všech bodů — tak to dělal starý web.
	$nejvic = max( array_column( $radky, 'body' ) );

	$pocet = max( 0, (int) $a['pocet'] );
	if ( $pocet > 0 ) {
		$radky = array_slice( $radky, 0, $pocet );
	}

	$obsazene = array();
	$polozky  = '';

	foreach ( $radky as $index => $radek ) {
		$polozky .= bm_kralove_polozka( $radek, $index + 1, $nejvic, $obsazene );
	}

	return '<section class="bmk">'
		. '<p class="bmk__rocnik">Průběžné pořadí — ročník ' . esc_html( $rocnik->name ) . '</p>'
		. '<ol class="bmk__seznam" itemscope itemtype="https://schema.org/ItemList">'
		. $polozky
		. '</ol></section>';
}

/**
 * Jeden kandidát.
 *
 * @param array    $radek    Řádek z bm_kralove_poradi().
 * @param int      $poradi   Pořadí v žebříčku od jedné.
 * @param int      $nejvic   Body vedoucího kandidáta.
 * @param string[] $obsazene Už použité kotvy, předává se odkazem.
 * @return string
 */
function bm_kralove_polozka( $radek, $poradi, $nejvic, &$obsazene ) {
	$id    = $radek['id'];
	$skola = (string) get_post_meta( $id, 'skola', true );
	$tema  = (string) get_post_meta( $id, 'tema', true );
	$kotva = bm_kralove_kotva( $skola, $obsazene );

	// number_format, ne sprintf — desetinná tečka v šířce nesmí záviset
	// na jazyce serveru, jinak CSS dostane „91,08%" a pruh zmizí.
	$sirka = $nejvic > 0 ? number_format( $radek['body'] / $nejvic * 100, 2, '.', '' ) : '0';

	$foto = get_the_post_thumbnail( $id, 'medium', array( 'itemprop' => 'image', 'loading' => 'lazy' ) );
	if ( ! $foto ) {
		// Prázdné místo by rozhodilo řádek. Písmeno drží rozměr a je vidět,
		// že fotka chybí — návod na to tým upozorňuje.
		$pismeno = mb_substr( wp_strip_all_tags( get_the_title( $id ) ), 0, 1 );
		$foto    = '<span class="bmk-kral__bezfotky" aria-hidden="true">' . esc_html( $pismeno ) . '</span>';
	}

	$popis = esc_html( $skola );
	if ( '' !== $tema ) {
		$popis = '<strong>' . esc_html( $skola ) . '</strong> — <em>' . esc_html( $tema ) . '</em>';
	}

	return '<li class="bmk-kral" id="' . esc_attr( $kotva ) . '"'
		. ' itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">'
		. '<span class="bmk-kral__poradi" aria-hidden="true">' . esc_html( (string) $poradi ) . '.</span>'
		. '<span class="bmk-kral__foto">' . $foto . '</span>'
		. '<span class="bmk-kral__info">'
		. '<span class="bmk-kral__jmeno" itemprop="name">' . esc_html( get_the_title( $id ) ) . '</span>'
		. '<span class="bmk-kral__skola" itemprop="description">' . $popis . '</span>'
		. '<span class="bmk-kral__graf">'
		. '<span class="bmk-kral__pruh" style="width:' . esc_attr( $sirka ) . '%" aria-hidden="true"></span>'
		. '<span class="bmk-kral__body">' . esc_html( number_format_i18n( $radek['body'] ) ) . ' b.</span>'
		. '</span></span>'
		. '<meta itemprop="position" content="' . esc_attr( (string) $poradi ) . '">'
		. '<meta itemprop="url" content="' . esc_url( get_permalink() . '#' . $kotva ) . '">'
		. '</li>';
}

/**
 * Hlášení, když kandidáti ještě nejsou. Prázdná stránka by vypadala jako chyba.
 *
 * @return string
 */
function bm_kralove_prazdno() {
	return '<section class="bmk bmk--prazdno">'
		. '<p>Kandidáty teprve sbíráme. Jakmile je školy nahlásí, objeví se tady '
		. 'průběžné pořadí i s body.</p></section>';
}
