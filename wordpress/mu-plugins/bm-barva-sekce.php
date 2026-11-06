<?php
/**
 * Plugin Name: Majáles — barva sekce
 * Description: Každá položka hlavního menu má svou barvu z palety a stránka pod ní ji zdědí.
 * Version: 1.0.0
 *
 * Proč to existuje:
 * Hlavička od 6. 11. 2026 ukazuje položky menu jako barevné štítky a nadpis
 * stránky má tutéž barvu podtrženou. Někde se tedy musí rozhodnout, která
 * položka má kterou barvu.
 *
 * Proč podle pořadí v menu, a ne podle ID stránek:
 * Seznam ID stránek v CSS („stránka 85 je korálová") je přesně ten druh
 * zápisu, kterému za rok nikdo neporozumí a který se po přejmenování
 * stránky tiše rozejde. Pořadí v menu naopak tým vidí a přetahuje myší
 * v administraci. Přehodí položky a barvy se přehodí s nimi.
 *
 * Proč čísla palety, a ne hexy:
 * Rebranding na nový ročník je přepsání patnácti polí v Customizeru.
 * Kdyby tady stály hexy, hlavička by o rebrandingu nevěděla.
 *
 * Jak se barva dostane do CSS:
 * doplněk vypíše do hlavičky stránky jeden malý <style>. V něm je pravidlo
 * pro každou položku hlavní úrovně (--bm-polozka) a jedno pro <body>
 * (--bm-sekce). CSS v bm.css tedy o pořadí ani o ID nic neví a jen barvy
 * používá.
 *
 * Proč pravidlo pro položku, a ne atribut style na odkazu:
 * proměnná se takhle dědí do celé položky včetně rozbalovátka pod ní, takže
 * si barvu vezme i podbarvení položek v seznamu. Na odkazu by skončila.
 *
 * Omezení: počítá se s jednou úrovní zanoření, což je přesně to, co menu
 * má (jediné rozbalovátko je O festivalu). Hlubší vnoření by barvu nezdědilo.
 * Položka hlavní úrovně nemusí vést na stránku — „O festivalu" je vlastní
 * odkaz, který jen rozbaluje seznam. Barvu dostane stejně jako ostatní,
 * jen do mapy stránek nic nezapíše.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Barvy sekcí v pořadí položek hlavní úrovně menu. Jsou to sloty globální
 * palety Kadence, ne hexy.
 *
 * Dnešní menu: Program (žlutá), Králové (korálová), Osvěžovny (levandulová),
 * O festivalu (zelenomodrá). Když položek přibude, barvy se opakují dokola.
 */
const BM_BARVY_SEKCI = array( 15, 10, 14, 1 );

add_action( 'wp_head', 'bm_barva_do_hlavicky', 20 );

/**
 * Slot palety pro položku na dané pozici (od nuly).
 *
 * @param int $poradi Pořadí položky v hlavní úrovni menu.
 * @return int Číslo slotu globální palety.
 */
function bm_barva_slot( $poradi ) {
	$barvy = BM_BARVY_SEKCI;

	return (int) $barvy[ $poradi % count( $barvy ) ];
}

/**
 * Mapa barev: co má která položka menu a co která stránka.
 *
 * Počítá se jednou za požadavek. Podstránky zdědí barvu své nadřazené
 * položky, takže Kontakt je zelenomodrý stejně jako O festivalu.
 *
 * @return array{polozky: array<int,int>, stranky: array<int,int>}
 */
function bm_barva_mapa() {
	static $mapa = null;

	if ( null !== $mapa ) {
		return $mapa;
	}

	$mapa     = array( 'polozky' => array(), 'stranky' => array() );
	$umisteni = get_nav_menu_locations();

	if ( empty( $umisteni['primary'] ) ) {
		return $mapa;
	}

	$polozky = wp_get_nav_menu_items( $umisteni['primary'] );

	if ( empty( $polozky ) ) {
		return $mapa;
	}

	// Nejdřív hlavní úroveň — ta určuje pořadí i barvy.
	$poradi = 0;
	foreach ( $polozky as $polozka ) {
		if ( 0 !== (int) $polozka->menu_item_parent ) {
			continue;
		}

		$slot = bm_barva_slot( $poradi );
		$poradi++;

		$mapa['polozky'][ (int) $polozka->ID ] = $slot;

		// Jen položka vedoucí na stránku má co zapsat do mapy stránek.
		// U vlastního odkazu (například „O festivalu", které jen rozbaluje
		// seznam) je object_id ID té položky menu, ne ID stránky.
		if ( 'page' === $polozka->object && $polozka->object_id ) {
			$mapa['stranky'][ (int) $polozka->object_id ] = $slot;
		}
	}

	// Potom potomci, kteří si barvu berou od rodiče.
	foreach ( $polozky as $polozka ) {
		$rodic = (int) $polozka->menu_item_parent;

		if ( ! $rodic || ! isset( $mapa['polozky'][ $rodic ] ) || 'page' !== $polozka->object || ! $polozka->object_id ) {
			continue;
		}

		$mapa['stranky'][ (int) $polozka->object_id ] = $mapa['polozky'][ $rodic ];
	}

	return $mapa;
}

/**
 * Vypíše barvy do hlavičky stránky.
 *
 * Položky se cílí třídou `menu-item-<ID>`, ne atributem id: Kadence
 * vykresluje totéž menu dvakrát (počítač a mobilní panel), takže by stejné
 * id bylo v dokumentu dvakrát.
 *
 * Stránka, která v menu není (Zpracování osobních údajů, úvod),
 * barvu nedostane. Nadpis takové stránky zůstane černý bez podtržení — to je
 * schválně: barva znamená „tady v menu jsi“, a tam návštěvník není.
 *
 * @return void
 */
function bm_barva_do_hlavicky() {
	$mapa = bm_barva_mapa();

	if ( empty( $mapa['polozky'] ) ) {
		return;
	}

	$pravidla = array();

	foreach ( $mapa['polozky'] as $id => $slot ) {
		$pravidla[] = sprintf(
			'.menu-item-%d{--bm-polozka:var(--global-palette%d)}',
			(int) $id,
			(int) $slot
		);
	}

	// Aktuality nejsou stránka, ale výpis příspěvků — a jednotlivý článek
	// není v menu vůbec. Obojí v menu zastupuje stránka nastavená jako
	// „stránka s příspěvky", takže barvu dostanou po ní.
	if ( is_home() || is_singular( 'post' ) ) {
		$id_stranky = (int) get_option( 'page_for_posts' );
	} elseif ( is_singular() ) {
		$id_stranky = (int) get_queried_object_id();
	} else {
		$id_stranky = 0;
	}

	if ( ! empty( $mapa['stranky'][ $id_stranky ] ) ) {
		$pravidla[] = sprintf(
			'body{--bm-sekce:var(--global-palette%d)}',
			(int) $mapa['stranky'][ $id_stranky ]
		);
	}

	echo '<style id="bm-barva-sekce">' . implode( '', $pravidla ) . "</style>\n";
}
