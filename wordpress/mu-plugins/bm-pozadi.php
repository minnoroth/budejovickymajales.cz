<?php
/**
 * Plugin Name: Majáles — fotka na pozadí úvodu
 * Description: Náhledový obrázek úvodní stránky se použije jako pozadí barevného pásu.
 * Version: 1.0.0
 *
 * Proč to existuje:
 * Úvodní pás byl jednobarevně zelený. Zadavatel chtěl mít možnost dát za něj
 * fotku přes celé pozadí (vzor: korzonarodni.cz), ale tak, aby ji šlo vyměnit
 * bez sahání do kódu.
 *
 * Proč zrovna náhledový obrázek:
 * Je to ovládání, které tým už zná — stejné pole, jakým se dává fotka ke králi
 * nebo k akci. Nastavit fotku znamená nahrát obrázek, odebrat ji znamená
 * kliknout na „Odebrat náhledový obrázek". Žádné nové políčko, žádné nové
 * vysvětlování v návodu, a hlavně žádná adresa obrázku zadrátovaná v kódu,
 * kterou by po čase nikdo nenašel.
 *
 * Proč přes tmavý přechod:
 * Text v pásu je bílý. Na světlé fotce by zmizel, a která fotka tam bude, se
 * dopředu neví. Přechod drží čitelnost u jakékoli fotky a zároveň nechá
 * horní část obrázku skoro nedotčenou.
 *
 * Proč zelená zůstává pod fotkou:
 * Než se obrázek stáhne — a když se nestáhne vůbec — musí být pás pořád
 * čitelný. Barva z palety je proto dál pozadím a fotka leží na ní.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'bm_pozadi_uvodu', 20 );

/**
 * Přidá pozadí k pásu na úvodní stránce.
 *
 * Styl se přidává k doplňkovému CSS z Customizeru, ne vlastním souborem:
 * je to pár řádků a adresa obrázku se mění, takže by se stejně nedal
 * ukládat do mezipaměti.
 *
 * @return void
 */
function bm_pozadi_uvodu() {
	if ( ! is_front_page() ) {
		return;
	}

	$id = (int) get_post_thumbnail_id( get_queried_object_id() );
	if ( ! $id ) {
		return;
	}

	$obrazek = wp_get_attachment_image_src( $id, 'full' );
	if ( ! $obrazek || empty( $obrazek[0] ) ) {
		return;
	}

	$styl = sprintf(
		'body.home .bm-pas-hlavni{' .
			'background-image:linear-gradient(180deg,rgba(0,0,0,.25) 0%%,rgba(0,0,0,.65) 100%%),url(%s);' .
			'background-size:cover;background-position:center;' .
		'}',
		esc_url( $obrazek[0] )
	);

	// Styl patří za doplňkové CSS, aby přebil barvu pásu z bm.css.
	// Když se Kadence jednou přejmenuje, přidá se vlastní styl a nic nespadne.
	if ( wp_style_is( 'kadence-global', 'enqueued' ) ) {
		wp_add_inline_style( 'kadence-global', $styl );

		return;
	}

	wp_register_style( 'bm-pozadi', false, array(), '1.0.0' );
	wp_enqueue_style( 'bm-pozadi' );
	wp_add_inline_style( 'bm-pozadi', $styl );
}
