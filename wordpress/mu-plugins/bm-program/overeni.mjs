/* ════════════════════════════════════════════════════════════════════════
   OVĚŘENÍ DATOVÉ VRSTVY

   Proč existuje: na ostrém webu je dnes jediná akce, takže se na něm nedá
   ověřit ani stránkování po padesáti, ani hranice festivalového dne, ani
   pořadí scén. Tenhle skript prožene program-data.js plnými daty ročníku
   2026 (102 vystoupení, 28 míst), která leží zmrazená v prototypu.

       node overeni.mjs          nad zmrazenými daty z prototypu
       node overeni.mjs --zivy   nad živým REST API (jen tvary a mechanika)

   Živý běh na web nic nezapisuje — jsou to samé dotazy GET.
   ════════════════════════════════════════════════════════════════════════ */

import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';
import { fileURLToPath } from 'node:url';

const ZDE = path.dirname(fileURLToPath(import.meta.url));
const PROTOTYP = path.resolve(ZDE, '../../../docs/wordpress/11-lineup-navrh/prototyp.html');
const ZIVY_ZAKLAD = 'https://novy.budejovickymajales.cz/wp-json/';

let chyb = 0;

function overit(popis, podminka, detail) {
	if (podminka) {
		console.log('  ok   ' + popis);
	} else {
		chyb++;
		console.log('  CHYBA ' + popis + (detail === undefined ? '' : ' — ' + detail));
	}
}

function rovno(popis, skutecnost, ocekavani) {
	overit(popis, skutecnost === ocekavani, 'dostal jsem ' + JSON.stringify(skutecnost) + ', čekal ' + JSON.stringify(ocekavani));
}

/* ─────────────────────────────────────────────────────────────────────────
   Načtení testovaného souboru a zmrazených dat
   ───────────────────────────────────────────────────────────────────────── */

function nactiModul() {
	const zdroj = fs.readFileSync(path.join(ZDE, 'program-data.js'), 'utf8');
	vm.runInThisContext(zdroj, { filename: 'program-data.js' });
	return globalThis.bmProgram;
}

function zmrazenaData() {
	const html = fs.readFileSync(PROTOTYP, 'utf8');
	const zacatek = html.indexOf('<script type="application/json" id="bm-data">');
	const otevreno = html.indexOf('>', zacatek) + 1;
	const konec = html.indexOf('</script>', otevreno);
	return JSON.parse(html.slice(otevreno, konec));
}

/* ─────────────────────────────────────────────────────────────────────────
   Falešné REST API nad zmrazenými daty

   Napodobuje tři věci, na kterých skutečné API shazuje naivní klienty:
   strop per_page u kalendáře, přepsání konce rozsahu na 23:59:59
   a to, že místo ve wp/v2 je příspěvek s title.rendered, ne tvar kalendáře.
   ───────────────────────────────────────────────────────────────────────── */

function cas(text) {
	return new Date(text.replace(' ', 'T'));
}

function odpoved(telo, stav = 200) {
	return {
		ok: stav >= 200 && stav < 300,
		status: stav,
		json: async () => telo
	};
}

function faleshneApi(data, nastaveni = {}) {
	const zapis = [];
	const rocniky = nastaveni.rocniky || [
		{ id: 1, name: '2026', slug: '2026', acf: { datum_od: '20260531', datum_do: '20260606' } }
	];

	const mista = data.venues.map((m) => ({
		id: m.id,
		slug: m.slug,
		link: m.url,
		// Jedno místo schválně s entitou — přesně v tom tvaru, v jakém ji
		// wp/v2 vrací u „Žižkárna &#8211; garáž".
		title: { rendered: m.slug === 'zizkarna-soud' ? 'Žižkárna &#8211; soud' : m.venue },
		acf: { bezbarierovost: !!(m.acf || {}).bezbarierovost }
	}));

	const fetchStub = async (url) => {
		zapis.push(url);
		const u = new URL(url);
		const cesta = u.pathname.replace(/^\/wp-json\//, '');
		const strana = +(u.searchParams.get('page') || 1);
		const naStranku = +(u.searchParams.get('per_page') || 10);

		const strankovat = (pole) => {
			const strop = Math.min(naStranku, 100);
			const vyrez = pole.slice((strana - 1) * strop, strana * strop);
			if (!vyrez.length && strana > 1) {
				return odpoved({ code: 'rest_post_invalid_page_number' }, 400);
			}
			return odpoved(vyrez);
		};

		if (cesta === 'wp/v2/rocnik') {
			return strankovat(rocniky);
		}
		if (cesta === 'wp/v2/tribe_events_cat') {
			if (nastaveni.rozbitKategorie) {
				return odpoved({ code: 'rest_no_route' }, 404);
			}
			return strankovat(data.categories);
		}
		if (cesta === 'wp/v2/tribe_venue') {
			if (nastaveni.rozbitMista) {
				return odpoved({ code: 'rest_no_route' }, 404);
			}
			return strankovat(mista);
		}
		if (cesta === 'tribe/events/v1/events') {
			if (nastaveni.rozbitAkce) {
				return odpoved({ code: 'rest_no_route' }, 503);
			}
			// Kalendář sníží per_page na 50, i když se požádá o víc.
			const strop = Math.min(naStranku, 50);
			const od = cas(u.searchParams.get('start_date'));
			// A konec rozsahu si přepíše na 23:59:59 toho dne — tohle je ta
			// past, kvůli které musí hranici 6:00 dodržet až klient.
			const doData = cas(u.searchParams.get('end_date').slice(0, 10) + ' 23:59:59');
			const vybrane = (nastaveni.akce || data.events).filter((e) => {
				return cas(e.end_date) >= od && cas(e.start_date) <= doData;
			});
			const vyrez = vybrane.slice((strana - 1) * strop, strana * strop);
			if (!vyrez.length && strana > 1) {
				return odpoved({ code: 'event-archive-page-not-found' }, 404);
			}
			return odpoved({
				events: vyrez,
				total: vybrane.length,
				total_pages: Math.ceil(vybrane.length / strop)
			});
		}
		return odpoved({ code: 'rest_no_route' }, 404);
	};

	return { fetchStub, zapis };
}

/* ─────────────────────────────────────────────────────────────────────────
   Pravidla, která vlastní pohled, ale ověřují se tady — nad plnými daty
   ───────────────────────────────────────────────────────────────────────── */

function normalizuj(bmProgram, akce) {
	return akce.map((e) => {
		const d = e.start_date_details;
		const zac = new Date(+d.year, +d.month - 1, +d.day, +d.hour, +d.minutes, 0);
		return {
			id: e.id,
			nazev: e.title,
			zac,
			den: bmProgram.klicDneAkce(e),
			misto: (e.venue && e.venue.venue) || '',
			mistoSlug: (e.venue && e.venue.slug) || ''
		};
	});
}

/** Pořadí scén podle návrhu: víc akcí napřed, pak dřívější začátek, pak abeceda. */
function seraditMista(seznam) {
	const mapa = new Map();
	seznam.forEach((a) => {
		if (!a.mistoSlug) return;
		if (!mapa.has(a.mistoSlug)) {
			mapa.set(a.mistoSlug, { slug: a.mistoSlug, nazev: a.misto, pocet: 0, prvni: a.zac });
		}
		const m = mapa.get(a.mistoSlug);
		m.pocet++;
		if (a.zac < m.prvni) m.prvni = a.zac;
	});
	return [...mapa.values()].sort((a, b) => b.pocet - a.pocet || a.prvni - b.prvni || a.nazev.localeCompare(b.nazev, 'cs'));
}

/* ─────────────────────────────────────────────────────────────────────────
   Scénáře nad zmrazenými daty
   ───────────────────────────────────────────────────────────────────────── */

async function nadZmrazenymi(bmProgram) {
	const data = zmrazenaData();

	console.log('\n1. Celý ročník 2026 ze zmrazených dat');
	let api = faleshneApi(data);
	globalThis.fetch = api.fetchStub;
	const vysledek = await bmProgram.nactiData('https://test.local/wp-json/');

	rovno('ročník se vybral podle termínu', vysledek.rocnik.name, '2026');
	rovno('datum_od z tvaru Ymd', vysledek.rocnik.datum_od, '2026-05-31');
	rovno('datum_do z tvaru Ymd', vysledek.rocnik.datum_do, '2026-06-06');
	rovno('počet akcí', vysledek.akce.length, 102);
	rovno('počet míst', vysledek.mista.length, 28);
	rovno('počet kategorií', vysledek.kategorie.length, data.categories.length);

	const dotazyNaAkce = api.zapis.filter((u) => u.includes('tribe/events/v1/events'));
	rovno('102 akcí = tři dotazy (per_page 50)', dotazyNaAkce.length, 3);
	overit('dotaz posílá konec rozsahu na 06:00 dne po festivalu',
		dotazyNaAkce[0].includes(encodeURIComponent('2026-06-07 06:00:00')), dotazyNaAkce[0]);

	const soud = vysledek.mista.find((m) => m.slug === 'zizkarna-soud');
	rovno('entita v názvu místa se dekódovala', soud.venue, 'Žižkárna – soud');
	rovno('bezbariérovost místa se přenesla',
		vysledek.mista.find((m) => m.slug === 'kulturni-centrum-rabenstejnska').acf.bezbarierovost, true);
	rovno('barva kategorie se přenesla',
		vysledek.kategorie.find((k) => k.slug === 'hudba').acf.barva, '#1EA198');

	console.log('\n2. Hranice festivalového dne');
	const normalni = normalizuj(bmProgram, vysledek.akce);
	['BROCK', 'HANZALL', 'BASSDEALER'].forEach((nazev) => {
		const set = normalni.find((a) => a.nazev === nazev);
		rovno(nazev + ' (ranní set na K2) patří k pátku', set && set.den, '2026-06-05');
	});

	// Celodenní akce dostává od kalendáře začátek 00:00. Kdyby na ni platila
	// hranice 6:00, spadla by o den zpátky — přesně to dělá na ostrém webu
	// všech pět dílů výstavy ŠIROKO HLUBOKO.
	const celodenni = {
		id: 9200, title: 'Výstava', all_day: true,
		start_date: '2026-06-02 00:00:00',
		start_date_details: { year: '2026', month: '06', day: '02', hour: '00', minutes: '00', seconds: '00' }
	};
	rovno('celodenní akce z 00:00 zůstává ve svém dni', bmProgram.klicDneAkce(celodenni), '2026-06-02');
	rovno('a časovaná akce z 00:15 patří ke včerejšku',
		bmProgram.klicDneAkce({ start_date_details: { year: '2026', month: '06', day: '02', hour: '00', minutes: '15' } }),
		'2026-06-01');

	console.log('\n3. Dny a scény proti tabulce z návrhu');
	// Návrh, kapitola „Osa je správná odpověď": počet míst a akcí po dnech.
	const tabulka = [
		['2026-05-31', 3, 6],
		['2026-06-01', 6, 14],
		['2026-06-02', 9, 20],
		['2026-06-03', 13, 23],
		['2026-06-04', 15, 20],
		['2026-06-05', 5, 19]
	];
	rovno('festivalových dnů', new Set(normalni.map((a) => a.den)).size, tabulka.length);
	tabulka.forEach(([den, mist, akci]) => {
		const vDen = normalni.filter((a) => a.den === den);
		rovno(den + ' — akcí', vDen.length, akci);
		rovno(den + ' — scén', seraditMista(vDen).length, mist);
	});

	const ctvrtek = seraditMista(normalni.filter((a) => a.den === '2026-06-04'));
	overit('pořadí scén je sestupné podle počtu akcí',
		ctvrtek.every((m, i) => i === 0 || ctvrtek[i - 1].pocet >= m.pocet),
		ctvrtek.map((m) => m.nazev + ':' + m.pocet).join(', '));

	console.log('\n4. Ořez konce rozsahu');
	const sPodvrzenymi = data.events.concat([
		{
			id: 9001, title: 'Den po festivalu', url: '', excerpt: '', image: false, all_day: false,
			start_date: '2026-06-07 10:00:00',
			start_date_details: { year: '2026', month: '06', day: '07', hour: '10', minutes: '00', seconds: '00' },
			end_date: '2026-06-07 12:00:00',
			end_date_details: { year: '2026', month: '06', day: '07', hour: '12', minutes: '00', seconds: '00' },
			featured: false, hide_from_listings: false, categories: [], tags: [], venue: null, ticketed: []
		},
		{
			id: 9002, title: 'Noc pred prvnim dnem', url: '', excerpt: '', image: false, all_day: false,
			start_date: '2026-05-31 03:00:00',
			start_date_details: { year: '2026', month: '05', day: '31', hour: '03', minutes: '00', seconds: '00' },
			end_date: '2026-05-31 04:00:00',
			end_date_details: { year: '2026', month: '05', day: '31', hour: '04', minutes: '00', seconds: '00' },
			featured: false, hide_from_listings: false, categories: [], tags: [], venue: null, ticketed: []
		}
	]);
	api = faleshneApi(data, { akce: sPodvrzenymi });
	globalThis.fetch = api.fetchStub;
	const sPodvrhem = await bmProgram.nactiData('https://test.local/wp-json/');
	overit('akce z dne po festivalu vypadla', !sPodvrhem.akce.some((a) => a.id === 9001));
	overit('akce z brzkého rána prvního dne zůstala', sPodvrhem.akce.some((a) => a.id === 9002));

	console.log('\n5. Výběr ročníku');
	api = faleshneApi(data, {
		rocniky: [
			{ id: 1, name: '2026', slug: '2026', acf: { datum_od: '2026-05-31', datum_do: '2026-06-06' } },
			{ id: 2, name: '2027', slug: '2027', acf: { datum_od: '20270509', datum_do: '20270514' } }
		]
	});
	globalThis.fetch = api.fetchStub;
	const sPrazdnym2027 = await bmProgram.nactiData('https://test.local/wp-json/');
	rovno('prázdný novější ročník nesmí přebít archiv', sPrazdnym2027.rocnik.name, '2026');
	rovno('a program archivu zůstal celý', sPrazdnym2027.akce.length, 102);

	// Druhá větev pravidla: jakmile do novějšího termínu přibude první akce,
	// archiv ustoupí. Jedna akce stačí.
	api = faleshneApi(data, {
		akce: data.events.slice(0, 1).map((e) => Object.assign({}, e, {
			id: 9100,
			start_date: '2027-05-10 19:00:00',
			start_date_details: { year: '2027', month: '05', day: '10', hour: '19', minutes: '00', seconds: '00' },
			end_date: '2027-05-10 21:00:00',
			end_date_details: { year: '2027', month: '05', day: '10', hour: '21', minutes: '00', seconds: '00' }
		})).concat(data.events),
		rocniky: [
			{ id: 1, name: '2026', slug: '2026', acf: { datum_od: '2026-05-31', datum_do: '2026-06-06' } },
			{ id: 2, name: '2027', slug: '2027', acf: { datum_od: '20270509', datum_do: '20270514' } }
		]
	});
	globalThis.fetch = api.fetchStub;
	const prvniAkce2027 = await bmProgram.nactiData('https://test.local/wp-json/');
	rovno('první akce v novějším termínu archiv přebije', prvniAkce2027.rocnik.name, '2027');
	rovno('a ukáže se jen ona', prvniAkce2027.akce.length, 1);

	api = faleshneApi(data, {
		akce: [],
		rocniky: [{ id: 2, name: '2027', slug: '2027', acf: { datum_od: '20270509', datum_do: '20270514' } }]
	});
	globalThis.fetch = api.fetchStub;
	const bezProgramu = await bmProgram.nactiData('https://test.local/wp-json/');
	rovno('ročník bez akcí se vrátí jako nejnovější termín', bezProgramu.rocnik.name, '2027');
	rovno('a akce jsou prázdné, ne chyba', bezProgramu.akce.length, 0);

	console.log('\n6. Co se smí a co se nesmí pokazit');
	api = faleshneApi(data, { rozbitKategorie: true, rozbitMista: true });
	globalThis.fetch = api.fetchStub;
	const bezDoplnku = await bmProgram.nactiData('https://test.local/wp-json/');
	rovno('výpadek barev program nepoloží', bezDoplnku.akce.length, 102);
	rovno('kategorie jsou prázdné', bezDoplnku.kategorie.length, 0);
	rovno('místa jsou prázdná', bezDoplnku.mista.length, 0);

	api = faleshneApi(data, { rozbitAkce: true });
	globalThis.fetch = api.fetchStub;
	let vyhozeno = null;
	try {
		await bmProgram.nactiData('https://test.local/wp-json/');
	} catch (e) {
		vyhozeno = e;
	}
	overit('výpadek akcí vyhodí chybu, ať zůstane záchranný blok', vyhozeno !== null);
}

/* ─────────────────────────────────────────────────────────────────────────
   Živý běh — jen čtení
   ───────────────────────────────────────────────────────────────────────── */

async function nadZivym(bmProgram) {
	console.log('\nŽivé API ' + ZIVY_ZAKLAD + ' (jen dotazy GET)');

	const kalendar = await (await fetch(ZIVY_ZAKLAD + 'tribe/events/v1/events?per_page=100&start_date=2000-01-01%2000:00:00&end_date=2030-01-01%2000:00:00')).json();
	overit('kalendář sníží per_page na 50', String(kalendar.rest_url).includes('per_page=50'), kalendar.rest_url);
	overit('odpověď nese total i total_pages',
		typeof kalendar.total !== 'undefined' && typeof kalendar.total_pages !== 'undefined');

	const prvni = await (await fetch(ZIVY_ZAKLAD + 'wp/v2/tribe_venue?per_page=10&page=1&_fields=id,slug,link,title,acf')).json();
	const treti = await (await fetch(ZIVY_ZAKLAD + 'wp/v2/tribe_venue?per_page=10&page=3&_fields=id,slug,link,title,acf')).json();
	rovno('wp/v2 vrátí plnou první stránku', prvni.length, 10);
	overit('a poslední je kratší, čímž se pozná konec', treti.length > 0 && treti.length < 10, treti.length);
	overit('_fields projde i přes pole acf', prvni[0] && prvni[0].acf !== undefined);

	// Kolik dotazů na akce to stálo — stránkování se tím ověří i naživo.
	const puvodniFetch = globalThis.fetch;
	let dotazyNaAkce = 0;
	globalThis.fetch = (url, volby) => {
		if (String(url).includes('tribe/events/v1/events')) dotazyNaAkce++;
		return puvodniFetch(url, volby);
	};
	const vysledek = await bmProgram.nactiData(ZIVY_ZAKLAD);
	globalThis.fetch = puvodniFetch;

	console.log('  ročník:    ' + (vysledek.rocnik ? vysledek.rocnik.name + ' (' + vysledek.rocnik.datum_od + ' – ' + vysledek.rocnik.datum_do + ')' : 'žádný'));
	console.log('  akcí:      ' + vysledek.akce.length);
	console.log('  kategorií: ' + vysledek.kategorie.length);
	console.log('  míst:      ' + vysledek.mista.length);
	console.log('  dotazů na akce: ' + dotazyNaAkce);

	const poDnech = new Map();
	normalizuj(bmProgram, vysledek.akce).forEach((a) => poDnech.set(a.den, (poDnech.get(a.den) || 0) + 1));
	[...poDnech.keys()].sort().forEach((den) => console.log('  ' + den + ': ' + poDnech.get(den)));

	overit('ročník se našel', vysledek.rocnik !== null);
	overit('stránkování po padesáti sedí na počet akcí',
		dotazyNaAkce >= Math.ceil(vysledek.akce.length / 50), dotazyNaAkce + ' dotazů na ' + vysledek.akce.length + ' akcí');
	overit('kategorie mají barvu u rodičů',
		vysledek.kategorie.some((k) => k.parent === 0 && k.acf.barva));
	overit('názvy míst nejsou zakódované',
		vysledek.mista.length > 0 && !vysledek.mista.some((m) => /&#?\w+;/.test(m.venue)));
}

/* ───────────────────────────────────────────────────────────────────────── */

const bmProgram = nactiModul();
const zivy = process.argv.includes('--zivy');

try {
	if (zivy) {
		await nadZivym(bmProgram);
	} else {
		await nadZmrazenymi(bmProgram);
	}
} catch (e) {
	chyb++;
	console.log('\nSpadlo to: ' + e.stack);
}

console.log(chyb === 0 ? '\nVšechno sedí.' : '\nNeprošlo: ' + chyb);
process.exit(chyb === 0 ? 0 : 1);
