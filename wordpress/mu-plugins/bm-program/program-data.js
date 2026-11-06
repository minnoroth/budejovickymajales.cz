/* ════════════════════════════════════════════════════════════════════════
   PROGRAM — načtení dat

   Vystaví window.bmProgram.nactiData(), která vrátí program jednoho ročníku
   ve stejném tvaru, v jakém ho má zmrazený prototyp v
   docs/wordpress/11-lineup-navrh/prototyp.html:

       { rocnik, akce, kategorie, mista }

   Návrh počítá s tím, že se mezi prototypem a ostrým webem „vymění jediná
   funkce nactiData()" a zbytek skriptu zůstane. Tenhle soubor je ta funkce.
   Vykreslování je v program-view.js a tenhle soubor o něm neví.

   Vedle toho vystavuje nactiRezervaci(idAkce) — stav rezervace jedné akce.
   Načítá se zvlášť a až na vyžádání, protože je to další dotaz na Wedos
   a týká se menšiny akcí.

   Proč je to vlastní soubor a ne součást pohledu:
   Je to jediné místo, kde se potkáváme s The Events Calendar. Až TEC jednou
   přejmenuje endpoint nebo Secure Custom Fields přestane vracet klíč „acf",
   opraví se to tady a nikde jinde.

   Proč tu není žádné „document":
   Aby šel soubor spustit i v Node a dal se ověřit skriptem overeni.mjs nad
   plnými daty ročníku 2026. Entity se proto dekódují regulárním výrazem,
   ne přes <textarea>.
   ════════════════════════════════════════════════════════════════════════ */

(function (globalni) {
	'use strict';

	var bmProgram = globalni.bmProgram = globalni.bmProgram || {};

	/* Hodina, kterou končí festivalový den. Co začíná dřív, patří ještě
	   ke včerejšku — jinak by tři ranní sety na K2 (00:15, 01:15, 02:30)
	   vypadaly jako samostatná sobota. Stejná konstanta drží konec časového
	   rozsahu dotazu i klíč dne u jednotlivé akce; proto je jedna. */
	var HRANICE_DNE = 6;

	/* Stropy stránkování. per_page u The Events Calendar je 50 i tehdy,
	   když se požádá o víc — odpověď to tiše sníží. 102 akcí = tři dotazy. */
	var NA_STRANKU_TEC = 50;
	var NA_STRANKU_WP = 100;
	var MAX_STRANEK = 40;
	var SOUBEZNYCH_SOND = 3;

	/* ─────────────────────────────────────────────────────────────────────
	   Drobnosti
	   ───────────────────────────────────────────────────────────────────── */

	var POJMENOVANE_ENTITY = {
		amp: '&', lt: '<', gt: '>', quot: '"', apos: '\'', nbsp: ' '
	};

	/**
	 * Názvy chodí z REST API jako HTML — „Žižkárna &#8211; garáž". Bez
	 * dekódování je entita vidět v programu. WordPress posílá skoro výhradně
	 * číselné entity, pojmenovaných používá hrstku.
	 */
	function odstranEntity(text) {
		if (text === null || text === undefined) {
			return '';
		}
		return String(text).replace(/&(#\d+|#x[0-9a-f]+|[a-z]+);/gi, function (cela, kod) {
			if (kod.charAt(0) === '#') {
				var cislo = kod.charAt(1).toLowerCase() === 'x'
					? parseInt(kod.slice(2), 16)
					: parseInt(kod.slice(1), 10);
				return isFinite(cislo) && cislo > 0 ? String.fromCodePoint(cislo) : cela;
			}
			var nahrada = POJMENOVANE_ENTITY[kod.toLowerCase()];
			return nahrada === undefined ? cela : nahrada;
		});
	}

	function dve(cislo) {
		return String(cislo).padStart(2, '0');
	}

	function isoZData(datum) {
		return datum.getFullYear() + '-' + dve(datum.getMonth() + 1) + '-' + dve(datum.getDate());
	}

	/**
	 * Klíč festivalového dne. Akce z 00:15 patří k předchozímu dni.
	 * Stejnou funkci používá i pohled, proto je vystavená na window.
	 */
	function klicDne(datum) {
		return isoZData(new Date(datum.getTime() - HRANICE_DNE * 3600e3));
	}

	function oDenPozdeji(iso) {
		var casti = iso.split('-');
		var datum = new Date(+casti[0], +casti[1] - 1, +casti[2]);
		datum.setDate(datum.getDate() + 1);
		return isoZData(datum);
	}

	/**
	 * Secure Custom Fields vrací datum jako „20270509", prototyp má
	 * „2027-05-09". Obojí sem smí přijít, ven jde vždycky ISO.
	 */
	function naIso(hodnota) {
		var text = String(hodnota || '').trim();
		if (/^\d{8}$/.test(text)) {
			return text.slice(0, 4) + '-' + text.slice(4, 6) + '-' + text.slice(6, 8);
		}
		if (/^\d{4}-\d{2}-\d{2}/.test(text)) {
			return text.slice(0, 10);
		}
		return '';
	}

	function zacatekAkce(akce) {
		var d = akce && akce.start_date_details;
		if (d && d.year) {
			return new Date(+d.year, +d.month - 1, +d.day, +d.hour, +d.minutes, 0);
		}
		// Záloha pro případ, že by TEC rozpad data jednou přestal posílat.
		return new Date(String(akce && akce.start_date || '').replace(' ', 'T'));
	}

	/**
	 * Klíč festivalového dne pro jednu akci.
	 *
	 * Celodenní akce se hranicí 6:00 posuzovat nesmí. The Events Calendar
	 * jim dává začátek 00:00, takže by pětidenní výstava skončila celá
	 * o den dřív — a před prvním dnem festivalu by navíc vyskočila sedmá
	 * záložka, ve které není nic než ta výstava. Ověřeno na importu
	 * ročníku 2026: všech pět dílů výstavy ŠIROKO HLUBOKO začíná v 00:00.
	 *
	 * Pohled tuhle funkci používá místo klicDne(a.zac).
	 */
	function klicDneAkce(akce) {
		var zacatek = zacatekAkce(akce);
		return akce && akce.all_day ? isoZData(zacatek) : klicDne(zacatek);
	}

	/* ─────────────────────────────────────────────────────────────────────
	   REST
	   ───────────────────────────────────────────────────────────────────── */

	/**
	 * Skládá adresu tak, aby fungovala i na webu bez hezkých odkazů, kde
	 * rest_url() vrací „…/?rest_route=/" a otazník je v základu už obsazený.
	 */
	function adresa(zaklad, cesta, parametry) {
		var url = zaklad + cesta;
		var dotaz = Object.keys(parametry).map(function (klic) {
			return encodeURIComponent(klic) + '=' + encodeURIComponent(parametry[klic]);
		});
		if (!dotaz.length) {
			return url;
		}
		return url + (url.indexOf('?') < 0 ? '?' : '&') + dotaz.join('&');
	}

	async function ziskej(url) {
		var odpoved = await fetch(url, {
			credentials: 'same-origin',
			headers: { Accept: 'application/json' }
		});
		if (!odpoved.ok) {
			throw new Error('REST ' + odpoved.status + ' — ' + url);
		}
		return odpoved.json();
	}

	/**
	 * Stránkování wp/v2: strop je 100 položek a poslední stránka se pozná
	 * tím, že je kratší. Požádat o stránku za koncem vrátí chybu 400, proto
	 * se druhá a další stránka při selhání jen ukončí.
	 */
	async function strankyWp(zaklad, cesta, pole) {
		var vse = [];
		for (var stranka = 1; stranka <= MAX_STRANEK; stranka++) {
			var davka;
			try {
				davka = await ziskej(adresa(zaklad, cesta, {
					per_page: NA_STRANKU_WP,
					page: stranka,
					_fields: pole
				}));
			} catch (chyba) {
				if (stranka === 1) {
					throw chyba;
				}
				break;
			}
			if (!Array.isArray(davka) || !davka.length) {
				break;
			}
			vse = vse.concat(davka);
			if (davka.length < NA_STRANKU_WP) {
				break;
			}
		}
		return vse;
	}

	/**
	 * Jedna stránka akcí v termínu ročníku.
	 *
	 * Výchozí rozsah The Events Calendar je „dnes až +2 roky", takže se musí
	 * říct výslovně. Konec je datum_do + 1 den 06:00 — bez toho by vypadly
	 * ranní sety po půlnoci. TEC si sice hodinu stejně přepíše na 23:59:59
	 * (ověřeno 4. 11. 2026), takže dotaz vrátí o kus víc; hranici 6:00 proto
	 * dodržuje až filtr níž. Posílat rovnou datum_do by ale ty sety ztratilo.
	 */
	function strankaAkci(zaklad, od, doData, stranka) {
		return ziskej(adresa(zaklad, 'tribe/events/v1/events', {
			per_page: NA_STRANKU_TEC,
			page: stranka,
			start_date: od + ' 00:00:00',
			end_date: oDenPozdeji(doData) + ' ' + dve(HRANICE_DNE) + ':00:00',
			status: 'publish'
		}));
	}

	/* ─────────────────────────────────────────────────────────────────────
	   Výběr ročníku

	   Nejnovější termín, který má aspoň jednu akci; když takový není,
	   nejnovější termín vůbec. Jakmile někdo v prosinci založí termín 2028,
	   aby mohl zadávat program, nesmí tím ze stránky zmizet archiv 2027.
	   ───────────────────────────────────────────────────────────────────── */

	function terminRocniku(termin) {
		var acf = (termin && termin.acf) || {};
		var od = naIso(acf.datum_od) || naIso((acf.datum_od_source || {}).formatted_value);
		var doData = naIso(acf.datum_do) || naIso((acf.datum_do_source || {}).formatted_value);
		return {
			id: termin.id,
			name: String(termin.name || ''),
			datum_od: od,
			datum_do: doData || od
		};
	}

	async function vyberRocnik(zaklad) {
		var terminy = (await strankyWp(zaklad, 'wp/v2/rocnik', 'id,name,slug,acf')).map(terminRocniku);

		// Nejnovější napřed. Názvy jsou roky a návod zakazuje zakládat dva
		// se stejným — stejné pravidlo má i bm-termin.php.
		terminy.sort(function (a, b) {
			return b.name.localeCompare(a.name, 'cs', { numeric: true });
		});

		var kandidati = terminy.filter(function (termin) {
			return termin.datum_od && termin.datum_do;
		});

		/* Sondy jdou po trojicích naráz. Každý dotaz stojí na sdíleném
		   hostingu přes vteřinu, takže ptát se ročník po ročníku je na
		   studené cache znát — a sonda na nejnovější ročník, do kterého
		   teprve někdo začal zadávat program, se zeptá vždycky zbytečně.
		   Stránka 1 vítěze se rovnou použije, nestahuje se dvakrát. */
		for (var i = 0; i < kandidati.length; i += SOUBEZNYCH_SOND) {
			var davka = kandidati.slice(i, i + SOUBEZNYCH_SOND);
			var odpovedi = await Promise.all(davka.map(function (termin) {
				return strankaAkci(zaklad, termin.datum_od, termin.datum_do, 1);
			}));
			for (var j = 0; j < davka.length; j++) {
				if ((odpovedi[j] && +odpovedi[j].total) > 0) {
					return { rocnik: davka[j], prvni: odpovedi[j] };
				}
			}
		}

		return { rocnik: terminy[0] || null, prvni: null };
	}

	/* ─────────────────────────────────────────────────────────────────────
	   Doplňky, které v The Events Calendar nejsou
	   ───────────────────────────────────────────────────────────────────── */

	/**
	 * Barvy kategorií a bezbariérovost míst jsou pole Secure Custom Fields,
	 * ne data kalendáře. Když tyhle dva dotazy selžou, program běží dál —
	 * jen bez barev a bez informace o bezbariérovosti. Návrh s tím v tabulce
	 * rizik počítá, takže se tu nepadá.
	 */
	async function nepovinne(nacti, popis) {
		try {
			return await nacti();
		} catch (chyba) {
			if (globalni.console) {
				globalni.console.warn('[bm-program] ' + popis + ' se nenačetlo: ' + chyba.message);
			}
			return [];
		}
	}

	function upravKategorii(termin) {
		return {
			id: termin.id,
			name: odstranEntity(termin.name),
			slug: termin.slug,
			parent: +termin.parent || 0,
			acf: { barva: ((termin.acf || {}).barva) || '' }
		};
	}

	/**
	 * Místo ve wp/v2 je obyčejný příspěvek (title.rendered, link), kdežto
	 * kalendář posílá tvar { venue, slug, url }. Sjednocuje se to sem, aby
	 * pohled viděl jen jeden tvar místa.
	 */
	function upravMisto(prispevek) {
		return {
			id: prispevek.id,
			venue: odstranEntity((prispevek.title || {}).rendered),
			slug: prispevek.slug,
			url: prispevek.link || '',
			acf: { bezbarierovost: !!((prispevek.acf || {}).bezbarierovost) }
		};
	}

	/* ─────────────────────────────────────────────────────────────────────
	   Úprava akcí
	   ───────────────────────────────────────────────────────────────────── */

	function upravAkci(akce) {
		var misto = akce.venue;
		return Object.assign({}, akce, {
			title: odstranEntity(akce.title),
			venue: misto && misto.venue
				? Object.assign({}, misto, { venue: odstranEntity(misto.venue) })
				: misto,
			categories: (akce.categories || []).map(function (kategorie) {
				return Object.assign({}, kategorie, { name: odstranEntity(kategorie.name) });
			}),
			tags: (akce.tags || []).map(function (stitek) {
				return Object.assign({}, stitek, { name: odstranEntity(stitek.name) });
			})
		});
	}

	/* ─────────────────────────────────────────────────────────────────────
	   Rezervace jedné akce

	   Kapacita a obsazenost se nenačítají s programem, ale až když se
	   otevře detail — a jen u akce, která rezervaci má. Je to další dotaz
	   na Wedos (přes vteřinu) a loni se týkal sedmnácti akcí ze sta dvou;
	   na načtení stránky se kvůli němu nečeká.
	   ───────────────────────────────────────────────────────────────────── */

	function cisloNeboNull(hodnota) {
		if (hodnota === null || hodnota === undefined || hodnota === '') {
			return null;
		}
		var cislo = Number(hodnota);
		/* Kalendář posílá u neomezené kapacity zápornou hodnotu. */
		return isFinite(cislo) && cislo >= 0 ? cislo : null;
	}

	/**
	 * Čas konce rezervací na zobrazení.
	 *
	 * Chodí bez časové zóny („2027-05-11 19:00:00") — je to pražský čas,
	 * ve kterém ho pořadatel zadal. Projít ho přes Date() by znamenalo
	 * přepočet podle zóny prohlížeče, takže se jen poskládá.
	 */
	function casKonce(det) {
		if (!det || !det.year) {
			return '';
		}
		return Number(det.day) + '. ' + Number(det.month) + '. ' +
			dve(det.hour) + ':' + dve(det.minutes);
	}

	/**
	 * Stav rezervace jedné akce.
	 *
	 * @param {number} idAkce ID akce v kalendáři.
	 * @param {string} [zaklad] Základ REST API.
	 * @returns {Promise<object|null>} null znamená „nemá rezervaci zdarma";
	 *          chybu dotazu si volající chytá sám a ukáže detail bez ní.
	 */
	async function nactiRezervaci(idAkce, zaklad) {
		var koren = String(zaklad || bmProgram.zaklad || '/wp-json/');
		if (koren.slice(-1) !== '/') {
			koren += '/';
		}

		var odpoved = await ziskej(adresa(koren, 'tribe/tickets/v1/tickets', {
			post_id: idAkce, per_page: 10
		}));

		/* Festival rozdává jen rezervace zdarma (RSVP). Kdyby na akci někdy
		   visel placený lístek, do programu nepatří — ten o ceně nic neříká
		   a tvářil by se, že je zdarma. */
		var listek = ((odpoved && odpoved.tickets) || []).filter(function (polozka) {
			return String(polozka.type || '').indexOf('rsvp') >= 0;
		})[0];
		if (!listek) {
			return null;
		}

		var podrobnosti = listek.capacity_details || {};
		return {
			nazev: odstranEntity(listek.title || 'Rezervace'),
			kapacita: cisloNeboNull(listek.capacity),
			zbyva: cisloNeboNull(podrobnosti.available),
			volnychProcent: cisloNeboNull(podrobnosti.available_percentage),
			lzeRezervovat: listek.is_available !== false,
			doKdy: casKonce(listek.available_until_details)
		};
	}

	/* ─────────────────────────────────────────────────────────────────────
	   Veřejné rozhraní
	   ───────────────────────────────────────────────────────────────────── */

	/**
	 * Načte program nejnovějšího ročníku, který nějaký má.
	 *
	 * @param {string} [zaklad] Základ REST API, například „…/wp-json/".
	 *                          Když chybí, bere se z window.bmProgram.zaklad,
	 *                          který doplní bm-program.php.
	 * @returns {Promise<{rocnik: object|null, akce: object[], kategorie: object[], mista: object[]}>}
	 * @throws {Error} Když selže dotaz na ročníky nebo na akce. Prázdné pole
	 *                 akce znamená „ročník bez programu", ne chybu.
	 */
	async function nactiData(zaklad) {
		var koren = String(zaklad || bmProgram.zaklad || '/wp-json/');
		if (koren.slice(-1) !== '/') {
			koren += '/';
		}

		var vybrany = await vyberRocnik(koren);
		var rocnik = vybrany.rocnik;

		/* Jakmile je ročník známý, zbytek jde naráz. Při studené cache je na
		   Wedosu každý dotaz drahý a čekat na ně za sebou je na první
		   návštěvě znát — než se program vykreslí, kouká návštěvník na
		   záchranný blok. Počet stránek s akcemi říká total_pages v těle
		   odpovědi; stránka za koncem vrací 404, takže se na ni nedá narazit
		   naslepo. */
		var dalsiStranky = [];
		if (vybrany.prvni) {
			var stranek = Math.min(+vybrany.prvni.total_pages || 1, MAX_STRANEK);
			for (var stranka = 2; stranka <= stranek; stranka++) {
				dalsiStranky.push(strankaAkci(koren, rocnik.datum_od, rocnik.datum_do, stranka));
			}
		}

		var vse = await Promise.all([
			nepovinne(function () {
				return strankyWp(koren, 'wp/v2/tribe_events_cat', 'id,name,slug,parent,acf');
			}, 'Barvy kategorií'),
			nepovinne(function () {
				return strankyWp(koren, 'wp/v2/tribe_venue', 'id,slug,link,title,acf');
			}, 'Bezbariérovost míst'),
			Promise.all(dalsiStranky)
		]);

		var syrove = vybrany.prvni ? (vybrany.prvni.events || []).slice() : [];
		vse[2].forEach(function (odpoved) {
			syrove = syrove.concat((odpoved && odpoved.events) || []);
		});

		/* Hranici 6:00 musí dodržet klient — TEC si konec rozsahu přepsal na
		   23:59:59, takže v odpovědi je i to, co se děje den po festivalu.
		   Ořezává se jen konec: začátek rozsahu hlídá už dotaz, takže akce
		   z brzkého rána prvního dne se nesmí ztratit. */
		var akce = syrove
			.filter(function (polozka) {
				return !rocnik || !rocnik.datum_do || klicDneAkce(polozka) <= rocnik.datum_do;
			})
			.map(upravAkci);

		return {
			rocnik: rocnik,
			akce: akce,
			kategorie: vse[0].map(upravKategorii),
			mista: vse[1].map(upravMisto)
		};
	}

	bmProgram.HRANICE_DNE = HRANICE_DNE;
	bmProgram.klicDne = klicDne;
	bmProgram.klicDneAkce = klicDneAkce;
	bmProgram.odstranEntity = odstranEntity;
	bmProgram.nactiData = nactiData;
	bmProgram.nactiRezervaci = nactiRezervaci;
})(typeof window !== 'undefined' ? window : globalThis);
