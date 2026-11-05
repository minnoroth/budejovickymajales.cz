<?php
/**
 * Plugin Name: Majáles — zrušení rezervace návštěvníkem
 * Description: Pošle k rezervaci e-mail s podepsaným odhlašovacím odkazem a obslouží ho.
 * Version: 1.0.0
 *
 * ┌────────────────────────────────────────────────────────────────────────┐
 * │ NENASAZOVAT, DOKUD NENÍ HOTOVÁ ETAPA 0 (SMTP a DNS).                   │
 * │ Doména dnes nemá pro Wedos platný SPF ani DKIM, takže e-mail odeslaný  │
 * │ z webu nemusí dojít. Odkaz, který nedorazí, je horší než žádný odkaz:  │
 * │ návštěvník čeká na zprávu, která nepřijde. Postup je v                 │
 * │ docs/wordpress/12-rezervace-analyza.md, kapitola „E-maily              │
 * │ a doručitelnost".                                                      │
 * └────────────────────────────────────────────────────────────────────────┘
 *
 * Proč to existuje:
 * Návštěvník si zarezervuje místo na akci a pak mu to nevyjde. Dnes nemá jak
 * rezervaci zrušit — musí napsat pořadateli, a většinou nenapíše. Místo propadne
 * a akce je „plná" s prázdnými židlemi. Event Tickets tohle neumí ani v placené
 * verzi: jeho samoobslužná úprava rezervace vyžaduje uživatelský účet
 * a přihlášení, což je přesně ta registrace, kterou festival nechce.
 *
 * Co se stane, když se tenhle soubor smaže:
 * Rezervace fungují dál přesně jako dnes, jen z potvrzovacího e-mailu zmizí
 * odhlašovací odkaz a rezervace zase ruší pořadatel v administraci. Potvrzení
 * samo chodí dál, protože ho posílá Event Tickets, ne tenhle doplněk. Odkazy
 * v už odeslaných e-mailech přestanou fungovat (ukáže se běžná úvodní stránka).
 * Nic se nerozbije — doplněk nic nenahrazuje, jen přidává vrstvu navíc.
 *
 * Na čem z Event Tickets to visí (grep na „EVENT TICKETS" najde všechna místa):
 *   1) filtr tec_tickets_emails_rsvp_template_args         — obsah potvrzení
 *   2) data  typy příspěvků a meta klíče Tickets Commerce  — viz blok konstant níž
 *   3) volání TEC\Tickets\Commerce\Order::modify_status()  — zrušení objednávky,
 *      které vrátí místo do kapacity a uklidí účastníky
 *
 * POZOR — proti čemu je psaná analýza:
 * docs/wordpress/12-rezervace-analyza.md popisuje v nálezu 4 starou větev RSVP
 * (`tribe_rsvp_attendees`, akce `event_tickets_rsvp_tickets_generated`). Event
 * Tickets 5.30.0 zavedl RSVP v2 nad Tickets Commerce a tenhle web na něm běží —
 * ověřeno 4. 11. 2026 na /wp-json/tribe/tickets/v1/tickets, kde jediný lístek
 * hlásí "provider":"tc" a "type":"tc-rsvp". Stará větev se na tomhle webu vůbec
 * nenačítá, takže hooky z analýzy by nikdy nic nespustily. Podrobnosti jsou
 * v README vedle tohohle souboru.
 *
 * Jak ověřit, že žije: viz wordpress/mu-plugins/README.md.
 */

defined( 'ABSPATH' ) || exit;


/* ──────────────────────────────────────────────────────────────────────────
   0. Co doplněk ví o Event Tickets

   Všechno, čím se drží cizího pluginu, je tady pohromadě. Když Event Tickets
   jednou něco přejmenuje, opravuje se to na jednom místě — a dokud se to
   neopraví, doplněk mlčky nedělá nic, místo aby web rozbil.
   ────────────────────────────────────────────────────────────────────────── */

// Parametry odhlašovacího odkazu. Změna zneplatní odkazy v už odeslaných e-mailech.
const BM_REZERVACE_PARAM  = 'bm_zrusit';
const BM_REZERVACE_PODPIS = 'podpis';

// EVENT TICKETS 2/3 — typy příspěvků a stavy objednávky.
const BM_REZERVACE_OBJEDNAVKA   = 'tec_tc_order';
const BM_REZERVACE_UCASTNIK     = 'tec_tc_attendee';
const BM_REZERVACE_STAV_PLATI   = 'tec-tc-completed';
const BM_REZERVACE_STAV_ZRUSENO = 'tec-tc-refunded';
const BM_REZERVACE_TYP_RSVP     = 'tc-rsvp';

/*
 * Na jaký stav se objednávka převádí při zrušení.
 *
 * „refunded" u bezplatné rezervace zní divně a v administraci se ukáže jako
 * „Refunded". Je to ale JEDINÝ stav, na který Event Tickets dovolí přejít
 * z „completed" a který zároveň nese příznaky `increase_stock`
 * a `archive_attendees` — tedy vrátí místo do kapacity a uklidí účastníky.
 *
 * NEPŘEPISOVAT na „voided" („Cancelled"), i když to čte líp: ten stav příznak
 * `increase_stock` nemá a místo by se neuvolnilo. Stejně tak nestačí přepsat
 * účastníkovi meta `_tec_tickets_commerce_rsvp_status` na „no" — plugin to
 * dělá u přihlášených uživatelů a kapacitou to taky nehne.
 */
const BM_REZERVACE_ZRUSIT_NA = 'refunded';

// EVENT TICKETS 2/3 — meta klíče, které doplněk čte nebo přepisuje.
const BM_REZERVACE_META_AKCE             = '_tec_tickets_commerce_event';
const BM_REZERVACE_META_LISTEK           = '_tec_tickets_commerce_ticket';
const BM_REZERVACE_META_JMENO            = '_tec_tickets_commerce_full_name';
const BM_REZERVACE_META_EMAIL            = '_tec_tickets_commerce_email';
const BM_REZERVACE_META_RSVP             = '_tec_tickets_commerce_rsvp_status';
const BM_REZERVACE_META_TYP_LISTKU       = '_type';
const BM_REZERVACE_META_KUPUJICI_JMENO   = '_tec_tc_order_purchaser_full_name';
const BM_REZERVACE_META_KUPUJICI_KRESTNI = '_tec_tc_order_purchaser_first_name';
const BM_REZERVACE_META_KUPUJICI_PRIJMENI = '_tec_tc_order_purchaser_last_name';
const BM_REZERVACE_META_KUPUJICI_EMAIL   = '_tec_tc_order_purchaser_email';

// Vlastní značka doplňku na objednávce. Osobní údaj v ní není.
const BM_REZERVACE_META_ZRUSENO = '_bm_zruseno_navstevnikem';

// Čím se nahradí jméno kupujícího po zrušení.
const BM_REZERVACE_ANONYM = 'Rezervace zrušena návštěvníkem';


/* ──────────────────────────────────────────────────────────────────────────
   1. Podpis odkazu

   Odkaz musí fungovat měsíce — rezervace se ruší i den před akcí — a nesmí
   jít padělat. Proto HMAC nad wp_salt(), ne wp_nonce: nonce platí nejvýš
   24 hodin a váže se na session, takže v e-mailu je k ničemu.

   wp_salt() je tajemství z wp-config.php. Kdo ho nezná, podpis nevyrobí.
   Kdyby se soli na hostingu vyměnily, všechny staré odkazy přestanou platit
   a začnou se chovat jako nefunkční — ne jako platné cizí odkazy.
   ────────────────────────────────────────────────────────────────────────── */

/**
 * Podpis jedné objednávky.
 *
 * Podepisuje se objednávka, ne jednotlivý účastník. Formulář rezervace má pole
 * „Počet míst" a Event Tickets z jedné objednávky udělá jeden záznam účastníka
 * na každé místo. Kdo by podepisoval účastníka, vyrobí odkaz, který ze tří
 * rezervovaných míst uvolní jedno.
 *
 * @param int $id_objednavky ID příspěvku objednávky.
 * @return string 32 znaků hexadecimálně.
 */
function bm_rezervace_podpis( $id_objednavky ) {
	// 32 znaků ze sha256 je 128 bitů — neuhodnutelné, a odkaz zůstane krátký,
	// aby ho poštovní klient nezalomil doprostřed.
	return substr( hash_hmac( 'sha256', (string) (int) $id_objednavky, wp_salt( 'bm_rezervace' ) ), 0, 32 );
}

/**
 * Odhlašovací odkaz pro jednu objednávku.
 *
 * V adrese není jméno ani e-mail — jen číslo objednávky a podpis. Je to
 * požadavek GDPR: adresa putuje e-mailem, zůstává v historii prohlížeče
 * a objevuje se v logu serveru.
 *
 * @param int $id_objednavky ID příspěvku objednávky.
 * @return string Úplná adresa.
 */
function bm_rezervace_odkaz( $id_objednavky ) {
	return add_query_arg(
		array(
			BM_REZERVACE_PARAM  => (int) $id_objednavky,
			BM_REZERVACE_PODPIS => bm_rezervace_podpis( $id_objednavky ),
		),
		home_url( '/' )
	);
}


/* ──────────────────────────────────────────────────────────────────────────
   2. Odkaz v potvrzovacím e-mailu

   Odkaz se přidává DO potvrzení, které posílá Event Tickets, ne vedle něj.
   Dva e-maily naráz od stejného odesílatele o stejné akci nejdou rozlišit —
   návštěvník za tři týdny neví, ve kterém z nich odkaz hledat.

   Nejde o zásah do šablony. Event Tickets má na „obsah navíc" vlastní filtr
   a vykresluje ho sám (views/emails/rsvp/body.php volá template-parts/body/
   additional-content). Nekopíruje se sem žádná šablona pluginu.

   Proč zrovna `tec_tickets_emails_rsvp_template_args` a ne
   `tec_tickets_emails_rsvp_additional_content`:

     Nastavení „Use ticket email settings" je u RSVP ve výchozím stavu ZAPNUTÉ
     (Email/RSVP.php: tribe_get_option( …'use-ticket-email', true )). Při něm si
     e-mail vezme obsah navíc od e-mailu Ticket, takže filtr …_rsvp_additional_content
     se vůbec nespustí. Filtr nad template_args běží až potom, co se obsah vybral
     z jedné nebo druhé větve, takže funguje v obou. Ověřeno ve zdrojovém kódu
     Event Tickets 5.30.2 (Emails/Email_Abstract.php: get_template_context()).
   ────────────────────────────────────────────────────────────────────────── */

// EVENT TICKETS 1/3: filtr nad podklady pro potvrzovací e-mail k rezervaci.
add_filter( 'tec_tickets_emails_rsvp_template_args', 'bm_rezervace_doplnit_odkaz', 20, 4 );

/**
 * Přidá odhlašovací odkaz do potvrzovacího e-mailu k rezervaci.
 *
 * Když se objednávka nedá určit — typicky v náhledu e-mailu v administraci,
 * kde žádná není — vrací podklady beze změny. Náhled tím zůstane funkční.
 *
 * @param array  $args     Podklady pro šablonu e-mailu.
 * @param string $id       Identifikátor e-mailu.
 * @param string $sablona  Název šablony.
 * @param mixed  $email    Objekt e-mailu.
 * @return array
 */
function bm_rezervace_doplnit_odkaz( $args, $id = '', $sablona = '', $email = null ) {
	if ( ! is_array( $args ) || ! is_object( $email ) || ! method_exists( $email, 'get' ) ) {
		return $args;
	}

	$id_objednavky = bm_rezervace_objednavka_z_emailu( $email );

	if ( ! $id_objednavky ) {
		return $args;
	}

	// Placené lístky se takhle rušit nesmí a odpověď „Nepřijdu" se nemá
	// z čeho odhlašovat.
	$ucastnici = bm_rezervace_ucastnici( $id_objednavky );

	if ( ! $ucastnici || ! bm_rezervace_je_rsvp( $ucastnici[0] ) ) {
		return $args;
	}

	if ( ! bm_rezervace_pocet_mist( $ucastnici ) ) {
		return $args;
	}

	$odkaz = bm_rezervace_odkaz( $id_objednavky );

	$text = '<p>' . esc_html__( 'Kdyby ti to nevyšlo, dej nám prosím vědět tímhle odkazem a místo se uvolní někomu dalšímu:', 'bm' ) . '</p>'
		. '<p><a href="' . esc_url( $odkaz ) . '">' . esc_html( $odkaz ) . '</a></p>'
		. '<p>' . esc_html__( 'Odkaz ruší celou rezervaci, i když sis zamluvil víc míst. Je jen pro tebe, neposílej ho dál. Nic se nezruší hned — na stránce, která se otevře, to ještě jednou potvrdíš tlačítkem.', 'bm' ) . '</p>';

	$puvodni = isset( $args['additional_content'] ) ? (string) $args['additional_content'] : '';

	$args['additional_content'] = $puvodni . $text;

	return $args;
}

/**
 * Vytáhne z objektu e-mailu ID objednávky.
 *
 * Odesílač rezervací plní e-mailu klíč „tickets" polem účastníků a u účastníka
 * je objednávka nadřazeným příspěvkem (Commerce/Emails/RSVP_Email_Sender.php).
 *
 * @param object $email Objekt e-mailu Event Tickets.
 * @return int ID objednávky, nebo 0.
 */
function bm_rezervace_objednavka_z_emailu( $email ) {
	$listky = $email->get( 'tickets' );

	if ( ! is_array( $listky ) ) {
		return 0;
	}

	foreach ( $listky as $listek ) {
		$id_ucastnika = 0;

		if ( is_array( $listek ) && isset( $listek['attendee_id'] ) ) {
			$id_ucastnika = (int) $listek['attendee_id'];
		}

		if ( ! $id_ucastnika ) {
			continue;
		}

		$ucastnik = get_post( $id_ucastnika );

		if ( ! $ucastnik instanceof WP_Post || BM_REZERVACE_UCASTNIK !== $ucastnik->post_type ) {
			continue;
		}

		$id_objednavky = (int) $ucastnik->post_parent;

		if ( $id_objednavky && BM_REZERVACE_OBJEDNAVKA === get_post_type( $id_objednavky ) ) {
			return $id_objednavky;
		}
	}

	return 0;
}


/* ──────────────────────────────────────────────────────────────────────────
   3. Obsluha odkazu

   Odkaz nevede na stránku ve WordPressu, ale na úvodní adresu s parametrem.
   Je to schválně: nevyžaduje to zakládat stránku, přepisovat trvalé odkazy
   ani nic nastavovat v administraci. Smazáním souboru odkaz prostě přestane
   fungovat a nezůstane po něm osiřelá stránka.
   ────────────────────────────────────────────────────────────────────────── */

add_action( 'template_redirect', 'bm_rezervace_obsluha_odkazu' );

/**
 * Zachytí odhlašovací odkaz, ověří podpis a podle metody požadavku
 * buď ukáže potvrzení (GET), nebo rezervaci zruší (POST).
 */
function bm_rezervace_obsluha_odkazu() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( empty( $_GET[ BM_REZERVACE_PARAM ] ) ) {
		return;
	}

	$id_objednavky = absint( wp_unslash( $_GET[ BM_REZERVACE_PARAM ] ) );
	$podpis        = isset( $_GET[ BM_REZERVACE_PODPIS ] )
		? sanitize_text_field( wp_unslash( $_GET[ BM_REZERVACE_PODPIS ] ) )
		: '';
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	nocache_headers();

	// hash_equals porovnává v konstantním čase — podpis nejde uhádnout po znacích.
	if ( ! $id_objednavky || ! hash_equals( bm_rezervace_podpis( $id_objednavky ), $podpis ) ) {
		bm_rezervace_stranka(
			'Tenhle odkaz nefunguje',
			array(
				'Adresa je nejspíš někde cestou zalomená nebo zkrácená. Zkus ji otevřít'
					. ' znovu přímo z e-mailu, nebo nám napiš a rezervaci zrušíme za tebe.',
			)
		);
	}

	$objednavka = get_post( $id_objednavky );
	$ucastnici  = bm_rezervace_ucastnici( $id_objednavky );

	// Rezervaci mezitím smazal pořadatel, nebo už proběhl úklid po festivalu.
	if ( ! $objednavka instanceof WP_Post
		|| BM_REZERVACE_OBJEDNAVKA !== $objednavka->post_type
		|| empty( $ucastnici )
	) {
		bm_rezervace_stranka(
			'Rezervaci jsme nenašli',
			array(
				'Nejspíš ji už někdo zrušil, nebo je akce dávno za námi. Nic dalšího'
					. ' dělat nemusíš.',
			)
		);
	}

	// Pojistka: tenhle odkaz patří jen bezplatným rezervacím. Kdyby web někdy
	// začal prodávat lístky, nesmí jít objednávka takhle stornovat.
	if ( ! bm_rezervace_je_rsvp( $ucastnici[0] ) ) {
		bm_rezervace_stranka(
			'Tenhle odkaz tu nic nezruší',
			array( 'Napiš nám prosím a vyřídíme to s tebou osobně.' ),
			''
		);
	}

	$id_akce = (int) get_post_meta( $ucastnici[0], BM_REZERVACE_META_AKCE, true );
	$nazev   = $id_akce ? get_the_title( $id_akce ) : '';
	$mista   = bm_rezervace_pocet_mist( $ucastnici );

	if ( BM_REZERVACE_STAV_PLATI !== $objednavka->post_status || ! $mista ) {
		bm_rezervace_stranka(
			'Tahle rezervace je už zrušená',
			array( 'Místo jsme uvolnili dalším zájemcům. Nic dalšího dělat nemusíš.' ),
			'',
			$nazev
		);
	}

	$metoda = isset( $_SERVER['REQUEST_METHOD'] )
		? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) )
		: 'GET';

	// Na GET se nikdy nic neruší. Antivirové brány, náhledy odkazů v poště
	// a firemní skenery odkazy v e-mailech předem načítají — rezervace by se
	// rušily samy od sebe, aniž by na ně kdo klikl.
	if ( 'POST' !== $metoda ) {
		bm_rezervace_potvrzeni( $id_objednavky, $id_akce, $nazev, $mista );
	}

	// Formulář se mohl otevřít před mnoha hodinami a klíč formuláře mezitím
	// vypršel. Není to chyba návštěvníka — ukážeme potvrzení znovu.
	$klic = isset( $_POST['bm_potvrzeni'] ) ? sanitize_text_field( wp_unslash( $_POST['bm_potvrzeni'] ) ) : '';
	if ( ! wp_verify_nonce( $klic, 'bm_zrusit_' . $id_objednavky ) ) {
		bm_rezervace_potvrzeni( $id_objednavky, $id_akce, $nazev, $mista, true );
	}

	if ( ! bm_rezervace_zrusit( $id_objednavky, $ucastnici, $id_akce ) ) {
		// Buď to mezi načtením stránky a kliknutím stihl někdo jiný,
		// nebo je na webu zádrhel. Rozliší to stav objednávky — načtený znovu,
		// protože ten z začátku požadavku může být z mezipaměti.
		clean_post_cache( $id_objednavky );

		if ( BM_REZERVACE_STAV_PLATI !== get_post_status( $id_objednavky ) ) {
			bm_rezervace_stranka(
				'Tahle rezervace je už zrušená',
				array( 'Místo jsme uvolnili dalším zájemcům. Nic dalšího dělat nemusíš.' ),
				'',
				$nazev
			);
		}

		bm_rezervace_stranka(
			'Rezervaci se teď nepodařilo zrušit',
			array(
				'Někde na webu je zádrhel a nechceme tvrdit něco, co neplatí.'
					. ' Napiš nám prosím a zrušíme ji ručně.',
			),
			'',
			$nazev
		);
	}

	bm_rezervace_stranka(
		'Rezervace je zrušená',
		array(
			sprintf( 'Uvolnili jsme %s dalším zájemcům. Díky, že jsi dal vědět.', bm_rezervace_mista( $mista ) ),
			'Tvoje jméno a e-mail jsme z rezervace rovnou smazali, nikde je dál nedržíme.',
		),
		'',
		$nazev
	);
}

/**
 * Vykreslí potvrzovací stránku s tlačítkem.
 *
 * @param int    $id_objednavky ID objednávky.
 * @param int    $id_akce       ID akce.
 * @param string $nazev         Název akce.
 * @param int    $mista         Kolik míst se zruší.
 * @param bool   $vyprselo      Klíč formuláře vypršel a zkouší se znovu.
 */
function bm_rezervace_potvrzeni( $id_objednavky, $id_akce, $nazev, $mista, $vyprselo = false ) {
	$formular = sprintf(
		'<form method="post" action="%1$s">'
			. '<input type="hidden" name="bm_potvrzeni" value="%2$s">'
			. '<button type="submit" class="bm-rez-tlacitko">%3$s</button>'
			. '</form>',
		esc_url( bm_rezervace_odkaz( $id_objednavky ) ),
		esc_attr( wp_create_nonce( 'bm_zrusit_' . $id_objednavky ) ),
		esc_html( 1 === $mista ? 'Ano, zrušit rezervaci' : 'Ano, zrušit všechna místa' )
	);

	if ( $id_akce && get_permalink( $id_akce ) ) {
		$formular .= sprintf(
			'<p class="bm-rez-zpet"><a href="%s">Ne, rezervaci si nechám</a></p>',
			esc_url( get_permalink( $id_akce ) )
		);
	}

	$odstavce = array(
		sprintf( 'Máš u nás rezervované %s. Zrušením se místo uvolní dalším zájemcům.', bm_rezervace_mista( $mista ) ),
	);

	if ( $vyprselo ) {
		$odstavce[] = 'Stránka byla otevřená moc dlouho, tak jsme se pro jistotu zeptali znovu.';
	}

	bm_rezervace_stranka( 'Opravdu chceš zrušit rezervaci?', $odstavce, $formular, $nazev );
}


/* ──────────────────────────────────────────────────────────────────────────
   4. Vlastní zrušení

   Účastníka nejde smazat přímo — Event Tickets v mazací metodě kontroluje
   oprávnění a nepřihlášenému návštěvníkovi vrátí false. Správná cesta vede
   přes objednávku: ta se převede na stav, který plugin umí, a plugin sám
   vrátí místo do kapacity a účastníky uklidí.

   Osobní údaje pak doplněk smaže (viz bm_rezervace_smazat_udaje). Zůstane
   objednávka bez jména a e-mailu — pořadatel vidí, že se někdo odhlásil,
   ale nevidí kdo. To je zároveň jediný zdroj čísla, které rozhodne o tom,
   jestli se má v dalším ročníku stavět čekací fronta.
   ────────────────────────────────────────────────────────────────────────── */

/**
 * Všichni účastníci jedné objednávky, nejstarší první.
 *
 * EVENT TICKETS 2/3: typ příspěvku tec_tc_attendee, objednávka je jeho rodič.
 * Čte se jádrovou funkcí get_posts, ne přes vnitřní repozitáře pluginu.
 * Bere i účastníky v koši — po zrušení je tam plugin odloží a doplněk je
 * potřebuje najít, aby z nich smazal osobní údaje.
 *
 * @param int $id_objednavky ID objednávky.
 * @return int[] ID příspěvků účastníků.
 */
function bm_rezervace_ucastnici( $id_objednavky ) {
	if ( ! $id_objednavky ) {
		return array();
	}

	return get_posts(
		array(
			'post_type'   => BM_REZERVACE_UCASTNIK,
			'post_status' => array( 'publish', 'private', 'draft', 'pending', 'trash' ),
			'post_parent' => (int) $id_objednavky,
			'numberposts' => -1,
			'fields'      => 'ids',
			'orderby'     => 'ID',
			'order'       => 'ASC',
		)
	);
}

/**
 * Je tenhle účastník z bezplatné rezervace, ne z placeného lístku?
 *
 * @param int $id_ucastnika ID účastníka.
 * @return bool
 */
function bm_rezervace_je_rsvp( $id_ucastnika ) {
	$id_listku = (int) get_post_meta( $id_ucastnika, BM_REZERVACE_META_LISTEK, true );

	if ( ! $id_listku ) {
		return false;
	}

	return BM_REZERVACE_TYP_RSVP === get_post_meta( $id_listku, BM_REZERVACE_META_TYP_LISTKU, true );
}

/**
 * Kolik míst z objednávky je ještě obsazených.
 *
 * Účastník, který rovnou odpověděl „Nepřijdu", místo nikdy nezabral.
 * Tohle není kosmetika, ale pojistka: Event Tickets při odpočtu kapacity
 * odpověď „Nepřijdu" přeskakuje, ale při jejím vracení už ne. Kdyby se takováhle
 * objednávka zrušila, přibylo by na akci volné místo, které nikdo neměl.
 * Proto se u nulového počtu nic neruší.
 *
 * @param int[] $ucastnici ID účastníků.
 * @return int
 */
function bm_rezervace_pocet_mist( array $ucastnici ) {
	$mista = 0;

	foreach ( $ucastnici as $id ) {
		if ( 'no' !== get_post_meta( $id, BM_REZERVACE_META_RSVP, true ) ) {
			++$mista;
		}
	}

	return $mista;
}

/**
 * Zruší celou objednávku a smaže z ní osobní údaje.
 *
 * @param int   $id_objednavky ID objednávky.
 * @param int[] $ucastnici     ID účastníků, zjištěná před zrušením.
 * @param int   $id_akce       ID akce (kvůli vyčištění cache seznamu).
 * @return bool Povedlo se.
 */
function bm_rezervace_zrusit( $id_objednavky, array $ucastnici, $id_akce ) {
	// EVENT TICKETS 3/3. Bez pluginu nejde vrátit místo do kapacity, a zrušení,
	// po kterém místo zůstane zabrané, je horší než žádné. Radši nic neuděláme.
	if ( ! function_exists( 'tribe' ) || ! class_exists( '\TEC\Tickets\Commerce\Order' ) ) {
		return false;
	}

	$objednavky = tribe( \TEC\Tickets\Commerce\Order::class );

	if ( ! is_object( $objednavky ) || ! method_exists( $objednavky, 'modify_status' ) ) {
		return false;
	}

	// Tohle je celé zrušení: plugin sám vrátí místo do kapacity a účastníky
	// odloží do koše. Metoda nekontroluje oprávnění, takže ji smí zavolat
	// i nepřihlášený návštěvník.
	//
	// Porovnává se přísně proti true: starší i novější verze pluginu umí vrátit
	// i WP_Error, a ten by v běžné podmínce prošel jako úspěch.
	if ( true !== $objednavky->modify_status( $id_objednavky, BM_REZERVACE_ZRUSIT_NA ) ) {
		return false;
	}

	// Účastníci v koši by si jméno a e-mail nechali, proto je mažeme nadobro.
	// Jádrovou funkcí, ne mazáním z pluginu — to by se ještě pokoušelo
	// převést objednávku na stav, který kapacitu zase zabere.
	foreach ( $ucastnici as $id ) {
		wp_delete_post( $id, true );
	}

	bm_rezervace_smazat_udaje( $id_objednavky );

	// Nepovinné, ale užitečné: datum zrušení bez osobních údajů. Z těchhle
	// značek se po festivalu spočítá, kolik lidí se opravdu odhlásilo.
	update_post_meta( $id_objednavky, BM_REZERVACE_META_ZRUSENO, current_time( 'mysql' ) );

	// Seznam účastníků se cachuje na hodinu. Bez tohohle řádku by pořadatel
	// v administraci viděl starý počet a myslel si, že zrušení neproběhlo.
	if ( $id_akce && class_exists( 'Tribe__Tickets__Attendance' ) ) {
		Tribe__Tickets__Attendance::delete_attendees_caches( $id_akce );
	}

	return true;
}

/**
 * Smaže z objednávky jméno a e-mail.
 *
 * Zadání GDPR je jasné: po zrušení se osobní údaje smažou, ne jen označí.
 * Celou objednávku smazat nechceme — tím by zmizelo i to, že se někdo
 * odhlásil, a právě tohle číslo rozhoduje o čekací frontě v dalším ročníku.
 * Zůstane tedy záznam, ve kterém už není nic osobního.
 *
 * @param int $id_objednavky ID objednávky.
 */
function bm_rezervace_smazat_udaje( $id_objednavky ) {
	update_post_meta( $id_objednavky, BM_REZERVACE_META_KUPUJICI_JMENO, BM_REZERVACE_ANONYM );
	update_post_meta( $id_objednavky, BM_REZERVACE_META_KUPUJICI_KRESTNI, BM_REZERVACE_ANONYM );
	update_post_meta( $id_objednavky, BM_REZERVACE_META_KUPUJICI_PRIJMENI, '' );
	update_post_meta( $id_objednavky, BM_REZERVACE_META_KUPUJICI_EMAIL, '' );
}


/* ──────────────────────────────────────────────────────────────────────────
   5. Pomocné funkce a vykreslení stránky
   ────────────────────────────────────────────────────────────────────────── */

/**
 * Přečte jméno nebo e-mail z meta a vrátí ho v čitelné podobě.
 *
 * Event Tickets prohání jméno i adresu funkcí htmlentities(), takže „Novák"
 * může ležet v databázi jako „Nov&aacute;k". Do e-mailu to musí jít zpátky.
 *
 * @param int    $id   ID příspěvku.
 * @param string $klic Meta klíč.
 * @return string
 */
function bm_rezervace_cist( $id, $klic ) {
	$hodnota = get_post_meta( $id, $klic, true );

	if ( ! is_string( $hodnota ) ) {
		return '';
	}

	return trim( html_entity_decode( $hodnota, ENT_QUOTES, 'UTF-8' ) );
}

/**
 * „jedno místo" / „3 místa" / „7 míst".
 *
 * @param int $pocet Počet míst.
 * @return string
 */
function bm_rezervace_mista( $pocet ) {
	$pocet = (int) $pocet;

	if ( 1 === $pocet ) {
		return 'jedno místo';
	}

	if ( $pocet >= 2 && $pocet <= 4 ) {
		return $pocet . ' místa';
	}

	return $pocet . ' míst';
}

/**
 * Vykreslí samostatnou stránku a ukončí požadavek.
 *
 * Stránka se nestaví přes šablonu tématu, protože by k tomu musela existovat
 * stránka ve WordPressu — a tu by někdo jednou smazal nebo přejmenoval.
 * Volá se ale wp_head() a wp_footer(), takže Kadence vypíše svoji paletu
 * a písmo a stránka vypadá jako zbytek webu. Barvy proto nejsou nikde natvrdo.
 *
 * @param string   $nadpis   Nadpis stránky.
 * @param string[] $odstavce Odstavce textu (vkládají se jako prostý text).
 * @param string   $akce     Hotové HTML s formulářem nebo odkazem, může být prázdné.
 * @param string   $nazev    Název akce, vypíše se nad nadpis.
 */
function bm_rezervace_stranka( $nadpis, array $odstavce, $akce = '', $nazev = '' ) {
	status_header( 200 );

	?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title><?php echo esc_html( $nadpis . ' — ' . get_bloginfo( 'name' ) ); ?></title>
	<?php wp_head(); ?>
	<style>
		.bm-rez-stranka {
			max-width: 620px;
			margin: 0 auto;
			padding: 48px 20px 64px;
		}
		.bm-rez-akce {
			margin: 0 0 8px;
			font-size: 15px;
			font-weight: 700;
			letter-spacing: .04em;
			text-transform: uppercase;
			color: var(--global-palette1);
		}
		.bm-rez-stranka h1 {
			margin: 0 0 20px;
			color: var(--global-palette3);
		}
		.bm-rez-stranka p {
			margin: 0 0 16px;
			color: var(--global-palette4);
		}
		.bm-rez-tlacitko {
			margin-top: 12px;
			padding: 14px 28px;
			border: 2px solid var(--global-palette3);
			border-radius: 18px;
			background: var(--global-palette15);
			color: var(--global-palette3);
			font: inherit;
			font-weight: 800;
			cursor: pointer;
		}
		.bm-rez-tlacitko:hover {
			background: var(--global-palette9);
		}
		.bm-rez-zpet {
			margin-top: 20px;
			font-size: 15px;
		}
	</style>
</head>
<body <?php body_class( 'bm-rez-telo' ); ?>>
<main class="bm-rez-stranka">
	<?php if ( '' !== $nazev ) : ?>
		<p class="bm-rez-akce"><?php echo esc_html( $nazev ); ?></p>
	<?php endif; ?>

	<h1><?php echo esc_html( $nadpis ); ?></h1>

	<?php foreach ( $odstavce as $odstavec ) : ?>
		<p><?php echo esc_html( $odstavec ); ?></p>
	<?php endforeach; ?>

	<?php
	// $akce skládá tenhle soubor sám a jednotlivé hodnoty v něm jsou už
	// ošetřené — proto se tady neescapuje znovu.
	echo $akce; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
</main>
<?php wp_footer(); ?>
</body>
</html>
	<?php

	exit;
}
