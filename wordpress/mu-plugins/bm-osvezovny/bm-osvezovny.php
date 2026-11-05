<?php
/**
 * Plugin Name: Majáles — osvěžovny
 * Description: Typ obsahu Osvěžovna, jeho pole a zkratka [bm_osvezovny].
 * Version: 1.0.0
 *
 * Proč to existuje:
 * Osvěžovny byly ručně napsaná stránka — devět nadpisů, pod každým odrážky
 * s cenami vlepenými do věty. Vzhled držel `<style>` blok **uvnitř obsahu
 * stránky** s natvrdo zapsanou loňskou modrou. Při rebrandingu by se paleta
 * přepsala a osvěžovny zůstaly modré, protože o paletě nevědí. A kdokoli
 * z týmu mohl celou stránku rozbít překlepem v HTML, protože struktura byla
 * jen dohoda, kterou nic nehlídá.
 *
 * Teď je to devět příspěvků s poli. Vzhled dělá CSS z palety, strukturu drží
 * datový model a v editoru se vyplňují políčka, ne značky.
 *
 * Proč to odporuje docs/wordpress/02-moduly-detail.md:
 * Ten dokument v září doporučoval opak — žádný vlastní typ obsahu, jen block
 * pattern. Důvod byl, že data nejsou uniformní: jedna osvěžovna je pouhá
 * „Sleva 20 % na vše (heslo: Majáles)" bez ceny, jedna položka má dlouhý
 * popis ingrediencí. Rozhodnutí padlo jinak (5. 11. 2026, viz
 * docs/wordpress/17-osvezovny.md) a tenhle doplněk musí tu námitku unést:
 * cena je **text a smí být prázdná**, popis je samostatné nepovinné pole
 * a druh položky se nemusí vyplnit vůbec.
 *
 * Proč se typ obsahu registruje v PHP, a ne jako u Králů importem do SCF:
 * Králové vznikli dřív, přes SCF → Tools → Import. Ten krok se musí udělat
 * rukou v administraci a nikde se nevynutí. Tady se typ i pole registrují
 * kódem, takže je nahraje FTP spolu se zbytkem a nemůžou se rozejít
 * se souborem v repozitáři. Cena: v SCF → Post Types a SCF → Field Groups
 * osvěžovny **nejsou vidět** a nejdou tam upravit. Měnit se musí tady.
 *
 * Proč typ obsahu přes register_post_type(), a pole přes SCF:
 * Typ obsahu umí jádro WordPressu samo, takže ho nemá cenu věšet na doplněk,
 * který může kdokoli vypnout. Pole bez SCF vykreslit nejdou — kdyby se SCF
 * někdy vypnulo, data v databázi zůstanou a vrátí se i s ním.
 */

defined( 'ABSPATH' ) || exit;

/** Typ obsahu s podniky. */
const BM_OSVEZOVNY_TYP = 'osvezovna';

/** Opakovatelné pole s nabídkou. Podpole: nazev, popis, cena, druh. */
const BM_OSVEZOVNY_POLE = 'polozky';

/**
 * Strop na počet řádků opakovače. Záporný nebo nesmyslný počet by znamenal
 * poškozená data; strop drží smyčku konečnou, i kdyby se to stalo.
 */
const BM_OSVEZOVNY_MAX_POLOZEK = 100;

add_action( 'init', 'bm_osvezovny_registruj_typ' );
add_action( 'acf/init', 'bm_osvezovny_registruj_pole' );
add_action( 'wp_enqueue_scripts', 'bm_osvezovny_zarad_predem' );
add_shortcode( 'bm_osvezovny', 'bm_osvezovny_zkratka' );

/**
 * Registruje typ obsahu Osvěžovna.
 *
 * Viditelnost je schválně stejná jako u Králů: `public` i `publicly_queryable`
 * vypnuté. WordPress by jinak ke každému podniku vyrobil vlastní adresu
 * /osvezovna/<nazev>/ — prázdnou stránku s názvem podniku a ničím dalším,
 * protože typ nemá pole na text. Devět takových stránek by skončilo v Googlu.
 * Podniky se ukazují jedině ve výpisu na /osvezovny/.
 *
 * `show_in_rest` zapnuté zůstat musí — bez něj nejde obsah plnit skriptem
 * a nefunguje blokový editor.
 *
 * @return void
 */
function bm_osvezovny_registruj_typ() {
	register_post_type( BM_OSVEZOVNY_TYP, array(
		'labels'              => array(
			'name'                  => 'Osvěžovny',
			'singular_name'         => 'Osvěžovna',
			'menu_name'             => 'Osvěžovny',
			'all_items'             => 'Všechny osvěžovny',
			'add_new'               => 'Přidat osvěžovnu',
			'add_new_item'          => 'Přidat osvěžovnu',
			'edit_item'             => 'Upravit osvěžovnu',
			'new_item'              => 'Nová osvěžovna',
			'view_item'             => 'Zobrazit osvěžovnu',
			'search_items'          => 'Hledat osvěžovny',
			'not_found'             => 'Žádná osvěžovna nenalezena',
			'not_found_in_trash'    => 'V koši není žádná osvěžovna',
			'attributes'            => 'Pořadí osvěžovny',
			'item_published'        => 'Osvěžovna publikována.',
			'item_updated'          => 'Osvěžovna aktualizována.',
			'items_list'            => 'Seznam osvěžoven',
			'uploaded_to_this_item' => 'Nahráno k této osvěžovně',
		),
		'description'         => 'Podniky se zvýhodněnou nabídkou na týden festivalu.',
		'public'              => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => false,
		'show_in_rest'        => true,
		'menu_position'       => 27,
		'menu_icon'           => 'dashicons-coffee',
		'hierarchical'        => false,
		'has_archive'         => false,
		// Pořadí drží „Pořadí" ze záložky Atributy, ne datum vložení —
		// marketing chce mít vepředu podniky, které platí nejvíc.
		'supports'            => array( 'title', 'page-attributes' ),
	) );
}

/**
 * Registruje pole osvěžovny.
 *
 * Věší se na acf/init, ne na načtení souboru: mu-plugins běží dřív než
 * běžné doplňky, takže ve chvíli načtení tohohle souboru Secure Custom
 * Fields ještě neexistuje a acf_add_local_field_group() by byla nedefinovaná.
 *
 * `show_in_rest` je potřeba na dvě věci: aby šla nabídka naplnit skriptem
 * a aby se v odpovědi REST dala zpětně zkontrolovat.
 *
 * @return void
 */
function bm_osvezovny_registruj_pole() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$druhy = bm_osvezovny_druhy();

	acf_add_local_field_group( array(
		'key'                   => 'group_osvezovna',
		'title'                 => 'Osvěžovna – adresa a nabídka',
		'description'           => 'Kde podnik je a co na festival nabízí.',
		'location'              => array(
			array(
				array( 'param' => 'post_type', 'operator' => '==', 'value' => BM_OSVEZOVNY_TYP ),
			),
		),
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'active'                => true,
		'show_in_rest'          => 1,
		'fields'                => array(
			array(
				'key'          => 'field_osvezovna_adresa',
				'label'        => 'Adresa',
				'name'         => 'adresa',
				'type'         => 'text',
				'instructions' => 'Ulice a číslo, třeba „Široká 443/19“. Když zůstane prázdná, napíše se jen „Zobrazit na mapě“.',
				'wrapper'      => array( 'width' => '50' ),
			),
			array(
				'key'          => 'field_osvezovna_mapa',
				'label'        => 'Odkaz na mapu',
				'name'         => 'mapa',
				'type'         => 'url',
				'instructions' => 'Zkrácený odkaz z Map Google. Nepovinné — bez něj se vyhledá podnik podle názvu.',
				'wrapper'      => array( 'width' => '50' ),
			),
			array(
				'key'          => 'field_osvezovna_polozky',
				'label'        => 'Nabídka',
				'name'         => BM_OSVEZOVNY_POLE,
				'type'         => 'repeater',
				'instructions' => 'Jeden řádek = jedna položka menu. Pořadí řádků je pořadí na webu.',
				'layout'       => 'row',
				'button_label' => 'Přidat položku',
				'min'          => 0,
				'max'          => 0,
				'sub_fields'   => array(
					array(
						'key'             => 'field_osvezovna_polozky_nazev',
						'label'           => 'Název',
						'name'            => 'nazev',
						'type'            => 'text',
						'required'        => 1,
						'wrapper'         => array( 'width' => '35' ),
						'parent_repeater' => 'field_osvezovna_polozky',
					),
					array(
						'key'             => 'field_osvezovna_polozky_popis',
						'label'           => 'Popis',
						'name'            => 'popis',
						'type'            => 'text',
						'instructions'    => 'Složení nebo doplněk. Nepovinné.',
						'wrapper'         => array( 'width' => '35' ),
						'parent_repeater' => 'field_osvezovna_polozky',
					),
					array(
						'key'   => 'field_osvezovna_polozky_cena',
						'label' => 'Cena',
						'name'  => 'cena',
						// Text, ne číslo. Na stránce je vedle „105 Kč" taky
						// „Sleva 20 % na vše" a položky úplně bez ceny.
						// Číselné pole by tohle neuneslo.
						'type'            => 'text',
						'instructions'    => 'Včetně „Kč“. Může zůstat prázdná.',
						'wrapper'         => array( 'width' => '15' ),
						'parent_repeater' => 'field_osvezovna_polozky',
					),
					array(
						'key'             => 'field_osvezovna_polozky_druh',
						'label'           => 'Druh',
						'name'            => 'druh',
						'type'            => 'select',
						'instructions'    => 'Nepovinné. Vyplň jen tam, kde se nabídka dělí.',
						'choices'         => $druhy,
						'allow_null'      => 1,
						'default_value'   => '',
						'return_format'   => 'value',
						'wrapper'         => array( 'width' => '15' ),
						'parent_repeater' => 'field_osvezovna_polozky',
					),
				),
			),
		),
	) );
}

/**
 * Druhy položek v pořadí, v jakém se vykreslují.
 *
 * Je to zároveň nabídka pole „Druh" i pořadí skupin v kartičce. Když se sem
 * přidá další druh, objeví se v obojím. Druh je schválně nepovinný: polovinu
 * položek na dnešním webu zařadit nejde (co je „Květnový infarkt" — yerba
 * maté, mangový džus a espresso?) a hádat za tým se nemá.
 *
 * @return array<string, string>
 */
function bm_osvezovny_druhy() {
	return array(
		'nealko' => 'Nealko',
		'alko'   => 'Alko',
		'jidlo'  => 'Jídlo',
	);
}

/**
 * Nabídka jednoho podniku, načtená z databáze.
 *
 * Čte meta přímo přes jádro WordPressu, ne přes funkce Secure Custom Fields —
 * stejně jako bm-kralove a bm-termin. Kdyby se plugin s poli jednou vyměnil,
 * výpis funguje dál, protože tvar uložení (polozky = počet řádků,
 * polozky_0_nazev = první řádek) je daný databází, ne pluginem.
 *
 * @param int $id ID podniku.
 * @return array<int, array{nazev:string, popis:string, cena:string, druh:string}>
 */
function bm_osvezovny_polozky( $id ) {
	$radku = (int) get_post_meta( $id, BM_OSVEZOVNY_POLE, true );
	if ( $radku < 1 ) {
		return array();
	}
	$radku = min( $radku, BM_OSVEZOVNY_MAX_POLOZEK );

	$polozky = array();
	for ( $i = 0; $i < $radku; $i++ ) {
		$zaklad = BM_OSVEZOVNY_POLE . '_' . $i . '_';
		$nazev  = trim( (string) get_post_meta( $id, $zaklad . 'nazev', true ) );

		// Řádek bez názvu je nedopsaný koncept. Vypsat prázdnou odrážku
		// s cenou vypadá jako chyba vykreslení.
		if ( '' === $nazev ) {
			continue;
		}

		$polozky[] = array(
			'nazev' => $nazev,
			'popis' => trim( (string) get_post_meta( $id, $zaklad . 'popis', true ) ),
			'cena'  => trim( (string) get_post_meta( $id, $zaklad . 'cena', true ) ),
			'druh'  => (string) get_post_meta( $id, $zaklad . 'druh', true ),
		);
	}

	return $polozky;
}

/**
 * Rozdělí nabídku do skupin podle druhu.
 *
 * Nadpisy skupin se vypisují jen tehdy, když se nabídka opravdu dělí — tedy
 * když má podnik aspoň dva různé vyplněné druhy. Jinak by osm z devíti
 * podniků dostalo nad svoje tři drinky nadpis „Nealko", který nic nerozlišuje.
 * Takhle vyjde Široko s Alkem a Nealkem přesně jako dnes a zbytek zůstane
 * plochým seznamem.
 *
 * Položky bez druhu jdou vždy na konec a nikdy nedostanou nadpis — nevíme,
 * jak by se jmenoval.
 *
 * @param array $polozky Nabídka z bm_osvezovny_polozky().
 * @return array<int, array{popis:string, polozky:array}>
 */
function bm_osvezovny_skupiny( $polozky ) {
	$podle_druhu = array();
	$bez_druhu   = array();

	foreach ( $polozky as $polozka ) {
		$druh = isset( $polozka['druh'] ) ? (string) $polozka['druh'] : '';
		if ( '' === $druh ) {
			$bez_druhu[] = $polozka;
			continue;
		}
		$podle_druhu[ $druh ][] = $polozka;
	}

	$nadpisy = count( $podle_druhu ) > 1;
	$skupiny = array();

	// Pevné pořadí z nabídky pole, ne pořadí, v jakém druhy padly do řádků.
	// Jinak by Alko a Nealko přeskakovalo podle toho, co kdo zadal dřív.
	foreach ( bm_osvezovny_druhy() as $klic => $popis ) {
		if ( empty( $podle_druhu[ $klic ] ) ) {
			continue;
		}
		$skupiny[] = array(
			'popis'   => $nadpisy ? $popis : '',
			'polozky' => $podle_druhu[ $klic ],
		);
		unset( $podle_druhu[ $klic ] );
	}

	// Druh, který v nabídce přibyl a tady ještě není. Nesmí zmizet.
	foreach ( $podle_druhu as $klic => $radky ) {
		$skupiny[] = array(
			'popis'   => $nadpisy ? ucfirst( str_replace( '_', ' ', (string) $klic ) ) : '',
			'polozky' => $radky,
		);
	}

	if ( $bez_druhu ) {
		$skupiny[] = array( 'popis' => '', 'polozky' => $bez_druhu );
	}

	return $skupiny;
}

/**
 * Odkaz na mapu.
 *
 * Když tým odkaz nevyplní, vyhledá se podnik podle názvu v Českých
 * Budějovicích. Je to lepší než žádný odkaz i než mrtvý odkaz: na starém webu
 * měly čtyři podniky z devíti jen vyhledávání a nikomu to nevadilo.
 *
 * @param string $mapa  Vyplněný odkaz, nebo prázdno.
 * @param string $nazev Název podniku.
 * @return string
 */
function bm_osvezovny_mapa( $mapa, $nazev ) {
	$mapa = trim( (string) $mapa );
	if ( '' !== $mapa ) {
		return $mapa;
	}

	return 'https://www.google.com/maps/search/?api=1&query='
		. rawurlencode( trim( $nazev ) . ' České Budějovice' );
}

/**
 * ID publikovaných osvěžoven.
 *
 * Bez vazby na ročník — schválně. U akcí se ukázalo, že tým taxonomii Ročník
 * běžně nezaškrtne (viz bm-carousel), a osvěžovny by se tím vyprázdnily.
 * Devět podniků se jednou ročně přepíše a to je celé.
 *
 * @return int[]
 */
function bm_osvezovny_ids() {
	return get_posts( array(
		'post_type'     => BM_OSVEZOVNY_TYP,
		'post_status'   => 'publish',
		'numberposts'   => -1,
		'fields'        => 'ids',
		'orderby'       => 'menu_order title',
		'order'         => 'ASC',
		'no_found_rows' => true,
	) );
}

/**
 * Zařadí styl a skript. Volá se ze dvou míst ze stejného důvodu jako
 * u programu a králů: z wp_enqueue_scripts, aby se CSS stihlo do hlavičky
 * a stránka neproblikla, a ze zkratky kvůli umístění mimo obsah stránky.
 *
 * @return void
 */
function bm_osvezovny_zarad() {
	$slozka = plugin_dir_path( __FILE__ );
	$verze  = static function ( $soubor ) use ( $slozka ) {
		// Verze = čas změny souboru. Na Wedosu není nic, čím jinak shodit cache.
		return file_exists( $slozka . $soubor ) ? (string) filemtime( $slozka . $soubor ) : '1.0.0';
	};

	wp_enqueue_style( 'bm-osvezovny', plugins_url( 'osvezovny.css', __FILE__ ), array(), $verze( 'osvezovny.css' ) );

	// Skript jen dokresluje filtr nad výpisem. Bez něj zůstane vidět všechno,
	// takže se odkládá do patičky a nic nezdržuje.
	wp_enqueue_script(
		'bm-osvezovny',
		plugins_url( 'osvezovny.js', __FILE__ ),
		array(),
		$verze( 'osvezovny.js' ),
		array( 'in_footer' => true, 'strategy' => 'defer' )
	);
}

/**
 * @return void
 */
function bm_osvezovny_zarad_predem() {
	$prispevek = get_post();

	if ( $prispevek instanceof WP_Post && has_shortcode( $prispevek->post_content, 'bm_osvezovny' ) ) {
		bm_osvezovny_zarad();
	}
}

/**
 * Vypíše kartičky podniků.
 *
 * @return string
 */
function bm_osvezovny_zkratka() {
	bm_osvezovny_zarad();

	$karticky = '';
	foreach ( bm_osvezovny_ids() as $id ) {
		$karticky .= bm_osvezovny_karta( (int) $id );
	}

	if ( '' === $karticky ) {
		return bm_osvezovny_prazdno();
	}

	return '<section class="bmo">'
		. '<ul class="bmo__seznam">' . $karticky . '</ul>'
		. '</section>';
}

/**
 * Jedna kartička podniku.
 *
 * @param int $id ID podniku.
 * @return string
 */
function bm_osvezovny_karta( $id ) {
	$nazev   = wp_strip_all_tags( get_the_title( $id ) );
	$adresa  = trim( (string) get_post_meta( $id, 'adresa', true ) );
	$mapa    = bm_osvezovny_mapa( (string) get_post_meta( $id, 'mapa', true ), $nazev );
	$polozky = bm_osvezovny_polozky( $id );

	$telo = '';
	foreach ( bm_osvezovny_skupiny( $polozky ) as $skupina ) {
		if ( '' !== $skupina['popis'] ) {
			$telo .= '<p class="bmo-karta__skupina">' . esc_html( $skupina['popis'] ) . '</p>';
		}

		$telo .= '<ul class="bmo-karta__polozky">';
		foreach ( $skupina['polozky'] as $polozka ) {
			$telo .= bm_osvezovny_polozka( $polozka );
		}
		$telo .= '</ul>';
	}

	if ( '' === $telo ) {
		// Podnik bez nabídky je rozdělaná práce. Kartička s prázdným tělem
		// vypadá jako chyba, tak to radši řekneme rovnou.
		$telo = '<p class="bmo-karta__chybi">Nabídku ještě ladíme.</p>';
	}

	// Barvu proužku nese CSS podle pořadí kartičky, ne inline styl — jinak by
	// se barvy nedaly přebarvit paletou při rebrandingu.
	return '<li class="bmo-karta">'
		. '<span class="bmo-karta__pruh" aria-hidden="true"></span>'
		. '<div class="bmo-karta__hlava">'
		. '<h2 class="bmo-karta__nazev">' . esc_html( $nazev ) . '</h2>'
		. '<a class="bmo-karta__adresa" href="' . esc_url( $mapa ) . '" target="_blank" rel="noreferrer noopener">'
		. '<span class="bmo-karta__spendlik" aria-hidden="true"></span>'
		. esc_html( '' !== $adresa ? $adresa : 'Zobrazit na mapě' )
		. '</a></div>'
		. '<div class="bmo-karta__telo">' . $telo . '</div>'
		. '</li>';
}

/**
 * Jedna položka nabídky.
 *
 * Cena je ve vlastním prvku zarovnaném vpravo, aby šly ceny porovnat očima.
 * Na dnešním webu jsou vlepené do věty („…a prosecco 155 Kč“) a přečíst se
 * dají jen po jedné. Když cena chybí, prvek se vůbec nevypíše — prázdné
 * místo vpravo vypadá jako chybějící údaj.
 *
 * `data-druh` potřebuje filtr nad výpisem. Zůstává tam i prázdné, aby se
 * položky bez druhu daly ve filtru rozpoznat.
 *
 * @param array $polozka Položka z bm_osvezovny_polozky().
 * @return string
 */
function bm_osvezovny_polozka( $polozka ) {
	$text = '<span class="bmo-polozka__nazev">' . esc_html( $polozka['nazev'] ) . '</span>';
	if ( '' !== $polozka['popis'] ) {
		$text .= '<span class="bmo-polozka__popis">' . esc_html( $polozka['popis'] ) . '</span>';
	}

	$cena = '' !== $polozka['cena']
		? '<span class="bmo-polozka__cena">' . esc_html( $polozka['cena'] ) . '</span>'
		: '';

	return '<li class="bmo-polozka" data-druh="' . esc_attr( $polozka['druh'] ) . '">'
		. '<span class="bmo-polozka__text">' . $text . '</span>' . $cena
		. '</li>';
}

/**
 * Hlášení, když osvěžovny ještě nejsou. Prázdná stránka by vypadala jako chyba.
 *
 * @return string
 */
function bm_osvezovny_prazdno() {
	return '<section class="bmo bmo--prazdno">'
		. '<p>Osvěžovny s podniky teprve domlouváme. Jakmile budou nabídky hotové, '
		. 'najdeš je tady i s cenami.</p></section>';
}
