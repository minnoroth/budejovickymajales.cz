<?php
/**
 * Plugin Name: Majáles — rozbalovátko bez odkazu
 * Description: Položka menu s adresou „#“ nikam nevede, jen rozbaluje seznam pod sebou.
 * Version: 1.0.0
 *
 * Proč to existuje:
 * „O festivalu“ není stránka, je to jen záhlaví seznamu. Dokud byla položka
 * odkazem, nešlo poznat, jestli se klepnutím rozbaluje, nebo někam odchází —
 * na mobilu obzvlášť, kde odkaz a šipka leží vedle sebe v jednom řádku.
 *
 * Jak se taková položka založí:
 * ve Vzhled → Menu jako **Vlastní odkaz** s adresou `#` (nebo skriptem
 * vzhled/menu.py, kde má v seznamu místo čísla stránky None). WordPress
 * samotný „#“ nechá být a vykreslí z něj odkaz, který skočí na začátek
 * stránky. Tenhle doplněk z něj href sundá úplně.
 *
 * Co se tím získá:
 * `<a>` bez adresy není odkaz. Myš nad ním nemá ruku, klepnutí nic neudělá
 * a tabulátor ho přeskočí — klávesnice jde rovnou na tlačítko se šipkou,
 * které Kadence kreslí vedle a které umí seznam rozbalit. Zbytek (najetí
 * myší na počítači, klepnutí na celý řádek na mobilu) drží bm.css.
 *
 * Proč filtr, a ne úprava menu:
 * adresa `#` je v administraci vidět a dá se tam i zadat ručně. Kdyby se
 * href sundával jinudy, tým by v menu viděl odkaz a na webu ne.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'nav_menu_link_attributes', 'bm_rozbalovatko_bez_odkazu' );

/**
 * Sundá href z položky, která vede na „#“.
 *
 * @param array<string,string> $atts Atributy odkazu položky menu.
 * @return array<string,string> Atributy bez href, jde-li o „#“.
 */
function bm_rozbalovatko_bez_odkazu( $atts ) {
	if ( isset( $atts['href'] ) && '#' === trim( $atts['href'] ) ) {
		unset( $atts['href'] );
	}

	return $atts;
}
