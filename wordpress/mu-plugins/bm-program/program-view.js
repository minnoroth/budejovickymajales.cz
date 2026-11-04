/* ══════════════════════════════════════════════════════════════════════
   Budějovický Majáles — Program a lineup: vykreslování a ovládání

   Návrh i s obrázky a zdůvodněním každého rozhodnutí je
   v docs/wordpress/11-lineup-navrh/README.md; klikatelný prototyp vedle
   něj v prototyp.html. Tenhle soubor je převod toho prototypu, jen bez
   dat — ta dodává program-data.js.

   ROZHRANÍ, NA KTERÉM TO STOJÍ

     window.bmProgram.nactiData(zaklad)
       → Promise<{ rocnik, akce, kategorie, mista }>

   Tvary jsou syrové odpovědi The Events Calendar (tribe/events/v1/events)
   doplněné o dvě věci z wp/v2 — barvu kategorie (acf.barva) a
   bezbariérovost místa (acf.bezbarierovost). Odsud se na REST API
   nesahá a žádná data tu nejsou; když se data nenačtou, zůstane
   na stránce záchranný blok s odkazy do kalendáře.

   Celý soubor je jedna uzavřená funkce — do window nepřidává nic,
   takže nemůže kolidovat s Kadence ani s pluginy.
   ══════════════════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* Hodina, kterou končí festivalový den. Co začíná dřív, patří ještě
     ke včerejšku — jinak by tři ranní sety na K2 (00:15, 01:15, 02:30)
     vypadaly jako samostatná sobota a vznikla by sedmá záložka dne.
     Stará časová osa v AngularJS je neukázala vůbec. */
  var HRANICE_DNE = 6;

  var DNY_CZ = ['neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota'];
  var DNY_ZKR = ['NE', 'PO', 'ÚT', 'ST', 'ČT', 'PÁ', 'SO'];
  var KLIC_ULOZISTE = 'bm-program-moje';

  /* ─────────────────────────────────────────────────────────────────────
     0. Kořen a napojení na datovou vrstvu
     ───────────────────────────────────────────────────────────────────── */

  var koren = document.getElementById('bm-program')
    || document.querySelector('[data-bm-program], .bm-program, .bmp');
  if (!koren) return;
  /* Styly visí na .bmp. Třídu dává zkratka, ale přidáme si ji i sami —
     ať se dá program vložit i ručně napsaným divem. */
  koren.classList.add('bmp');

  var nouze = koren.querySelector('#bm-nouze, .bmp-nouze, .bm-nouze, [data-bm-nouze]');

  var most = window.bmProgram;
  if (!most || typeof most.nactiData !== 'function') {
    chyba(new Error('chybí window.bmProgram.nactiData'));
    return;
  }

  var param = new URLSearchParams(location.search);
  /* ?ted=2026-06-03T20:10 přepíše „teď". Zůstává i na ostrém webu
     schválně: je to jediný způsob, jak si projít čáru „teď", štítky
     TEĎ HRAJE a tři stavy ročníku mimo těch šest dní v roce, kdy
     festival opravdu běží. Pětiminutová kontrola po aktualizaci
     WordPressu se bez toho neobejde. */
  var TED = param.get('ted') ? new Date(param.get('ted')) : new Date();
  if (isNaN(+TED)) TED = new Date();

  /* Stav aplikace. Naplní se až po normalizaci dat. */
  var DATA = null, AKCE = [], DNY = [], DNY_INFO = [];
  var BARVA_KAT = {}, BEZBARIEROVE = {};
  var stav = null, el = null, prisloOdkazem = 0, denPredMoje = null;

  most.nactiData(koren.getAttribute('data-bm-zaklad') || most.zaklad || '/wp-json/')
    .then(function (data) { spust(data); })
    .catch(chyba);

  /* Kdyby cokoli spadlo, zůstane na stránce záchranný blok s odkazy
     do kalendáře — návštěvník se k programu dostane i tak. */
  function chyba(e) {
    if (window.console) console.error('[bm-program]', e);
  }

  /* ─────────────────────────────────────────────────────────────────────
     1. Start
     ───────────────────────────────────────────────────────────────────── */

  function spust(data) {
    try {
      DATA = data || {};
      normalizuj();
      postavKostru();
      pripojUdalosti();
      pripravStav();
      vykresli();
      /* Záchranný blok mizí teprve teď, kdy je program opravdu
         vykreslený — ne dřív. */
      if (nouze) nouze.remove();

      var odkazNaAkci = +param.get('akce');
      if (odkazNaAkci && najdi(odkazNaAkci)) otevriDetail(odkazNaAkci);
    } catch (e) {
      /* Rozdělaný program je horší než žádný: uklidíme po sobě
         a necháme na stránce záchranný blok. */
      if (el && el.korenObsah) el.korenObsah.remove();
      chyba(e);
    }
  }

  /* ─────────────────────────────────────────────────────────────────────
     2. Normalizace dat
     ───────────────────────────────────────────────────────────────────── */

  function naDatum(det) {
    return new Date(+det.year, +det.month - 1, +det.day, +det.hour, +det.minutes, 0);
  }
  function klicData(d) {
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') +
      '-' + String(d.getDate()).padStart(2, '0');
  }
  /* Klíč festivalového dne pro okamžik — tedy pro „teď" a pro výchozí den. */
  function klicDne(d) {
    return klicData(new Date(d.getTime() - HRANICE_DNE * 3600e3));
  }
  /* Klíč festivalového dne pro akci. Liší se od klicDne() v jediné věci:
     na celodenní akci se hranice 6:00 nesmí použít. The Events Calendar
     jí dává začátek 00:00, takže by každý díl výstavy spadl o den zpátky
     a před prvním dnem festivalu by vyskočila záložka jen s ní.
     Počítá to datová vrstva, ať je pravidlo na jednom místě; tohle je
     záloha pro případ, že by ho nenabídla. */
  function klicDneAkce(e, zac) {
    if (typeof most.klicDneAkce === 'function') return most.klicDneAkce(e);
    return e.all_day ? klicData(zac) : klicDne(zac);
  }
  function hhmm(d) {
    return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
  }
  /* Názvy z wp/v2 chodí s HTML entitami — „Žižkárna &#8211; garáž". */
  function odstranEntity(s) {
    var t = document.createElement('textarea');
    t.innerHTML = s == null ? '' : String(s);
    return t.value;
  }
  /* Výřez popisu chodí z The Events Calendar podle nastavení buď jako
     holý text, nebo jako HTML. Značky se zahodí tady, ať se na stránce
     neobjeví „<p>" — a protože se výsledek stejně ještě escapuje, nemůže
     se tudy nic propašovat. */
  function naText(s) {
    return odstranEntity(String(s == null ? '' : s).replace(/<[^>]*>/g, ' '))
      .replace(/\s+/g, ' ').trim();
  }
  function bezDiakritiky(s) {
    return (s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
  }
  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }
  function sklonuj(n, j, d, m) { return n === 1 ? j : (n >= 2 && n <= 4 ? d : m); }

  function normalizuj() {
    (DATA.kategorie || []).forEach(function (c) {
      if (c.parent === 0 && c.acf && c.acf.barva) BARVA_KAT[c.slug] = c.acf.barva;
    });
    (DATA.mista || []).forEach(function (v) {
      if (v.acf && v.acf.bezbarierovost) BEZBARIEROVE[v.slug] = true;
    });

    AKCE = (DATA.akce || [])
      .filter(function (e) { return !e.hide_from_listings; })
      .map(function (e) {
        var zac = naDatum(e.start_date_details);
        var kon = naDatum(e.end_date_details);
        /* Kategorie = termín bez rodiče, žánr = termín s rodičem.
           Když chybí obojí, akce zůstane v programu, jen bez barvy. */
        var kat = (e.categories || []).filter(function (c) { return c.parent === 0; })[0] || null;
        var zan = (e.categories || []).filter(function (c) { return c.parent !== 0; })[0] || null;
        var slug = (e.venue && e.venue.slug) || '';
        return {
          id: e.id,
          nazev: odstranEntity(e.title),
          zac: zac, kon: kon,
          den: klicDneAkce(e, zac),
          celyDen: !!e.all_day,
          misto: e.venue && e.venue.venue ? odstranEntity(e.venue.venue) : '',
          mistoSlug: slug,
          bezbarierove: !!BEZBARIEROVE[slug],
          kat: kat ? odstranEntity(kat.name) : '',
          katSlug: kat ? kat.slug : '',
          barva: kat ? (BARVA_KAT[kat.slug] || '') : '',
          zanr: zan ? odstranEntity(zan.name) : '',
          zeme: (e.tags || []).map(function (t) { return odstranEntity(t.name); }).join(', '),
          rezervace: (e.ticketed || []).length > 0,
          top: !!e.featured,
          obraz: e.image && e.image.url ? e.image.url : '',
          popis: naText(e.excerpt),
          url: e.url || ''
        };
      })
      .sort(function (a, b) { return a.zac - b.zac || a.nazev.localeCompare(b.nazev, 'cs'); });

    AKCE.forEach(function (a) {
      a.hledatelne = bezDiakritiky([a.nazev, a.misto, a.kat, a.zanr, a.zeme].join(' '));
    });

    /* Dny festivalu — odvozené z dat, ne natvrdo zapsané. */
    DNY = [];
    AKCE.forEach(function (a) { if (DNY.indexOf(a.den) < 0) DNY.push(a.den); });
    DNY.sort();
    DNY_INFO = DNY.map(popisDne);
  }

  function popisDne(klic) {
    var c = klic.split('-');
    var d = new Date(+c[0], +c[1] - 1, +c[2]);
    return {
      klic: klic,
      zkratka: DNY_ZKR[d.getDay()],
      nazev: DNY_CZ[d.getDay()],
      datum: d.getDate() + '. ' + (d.getMonth() + 1) + '.'
    };
  }
  function najdi(id) {
    return AKCE.filter(function (a) { return a.id === id; })[0] || null;
  }

  /* ─────────────────────────────────────────────────────────────────────
     3. Kostra stránky

     Zkratka v PHP vypíše jen prázdný kořen a záchranný blok. Všechno
     ostatní staví skript, aby se v PHP nemuselo nic ladit, až se změní
     rozvržení.
     ───────────────────────────────────────────────────────────────────── */

  var KOSTRA =
    '<div class="bmp-obsah">' +
    '<div class="bmp-ovladani">' +
    '<div class="bmp-dny" role="group" aria-label="Den festivalu"></div>' +
    '<div class="bmp-radek">' +
    '<div class="bmp-prepinac" role="group" aria-label="Zobrazení programu">' +
    '<button type="button" data-zobrazeni="lineup" aria-pressed="false">Lineup</button>' +
    '<button type="button" data-zobrazeni="seznam" aria-pressed="true">Seznam</button>' +
    '</div>' +
    '<button type="button" class="bmp-btn bmp-filtry-prepinac" aria-expanded="false" aria-controls="bmp-filtry">' +
    '<span>Filtry<span class="bmp-sirsi"> a hledání</span></span>' +
    '<span class="bmp-pocet bmp-filtry-pocet" hidden>0</span></button>' +
    '<button type="button" class="bmp-btn bmp-btn--moje" aria-pressed="false">' +
    '<span>★<span class="bmp-sirsi"> Moje</span></span>' +
    '<span class="bmp-pocet bmp-moje-pocet">0</span></button>' +
    '</div>' +
    '<div class="bmp-filtry" id="bmp-filtry" hidden>' +
    '<div class="bmp-filtry__skupina">' +
    '<input type="search" class="bmp-hledani" placeholder="Hledat kapelu, scénu, žánr…" aria-label="Hledat v programu">' +
    '</div>' +
    '<div class="bmp-filtry__skupina">' +
    '<div class="bmp-filtry__nazev" id="bmp-nazev-kat">Kategorie</div>' +
    '<div class="bmp-chipy bmp-chipy-kat" role="group" aria-labelledby="bmp-nazev-kat"></div>' +
    '</div>' +
    '<div class="bmp-filtry__skupina">' +
    '<div class="bmp-filtry__nazev" id="bmp-nazev-misto">Místo <span class="bmp-mist-popis"></span></div>' +
    '<div class="bmp-chipy bmp-chipy-misto" role="group" aria-labelledby="bmp-nazev-misto"></div>' +
    '</div>' +
    '<div class="bmp-radek bmp-radek--filtry">' +
    '<button type="button" class="bmp-btn bmp-zrusit-filtry">Zrušit filtry</button>' +
    '<button type="button" class="bmp-btn bmp-sdilet">Sdílet můj výběr</button>' +
    '<button type="button" class="bmp-btn bmp-skoc-ted" hidden>Skoč na teď</button>' +
    '</div>' +
    '</div>' +
    '</div>' +
    '<div class="bmp-shrnuti" role="status" aria-live="polite"></div>' +
    '<div class="bmp-stav" hidden></div>' +
    '<div class="bmp-celoden" hidden>' +
    '<div class="bmp-celoden__nazev">Celý den</div>' +
    '<div class="bmp-celoden__pasy"></div>' +
    '</div>' +
    '<div class="bmp-vysledek"></div>' +
    '<dialog class="bmp-detail" aria-label="Detail akce">' +
    '<button type="button" class="bmp-zavrit" aria-label="Zavřít detail">✕</button>' +
    '<div class="bmp-detail__obsah"></div>' +
    '</dialog>' +
    '</div>';

  function postavKostru() {
    koren.insertAdjacentHTML('beforeend', KOSTRA);
    var q = function (t) { return koren.querySelector(t); };
    el = {
      korenObsah: q('.bmp-obsah'),
      ovladani: q('.bmp-ovladani'),
      dny: q('.bmp-dny'),
      vysledek: q('.bmp-vysledek'),
      shrnuti: q('.bmp-shrnuti'),
      stav: q('.bmp-stav'),
      celoden: q('.bmp-celoden'),
      celodenPasy: q('.bmp-celoden__pasy'),
      filtry: q('.bmp-filtry'),
      filtryPrepinac: q('.bmp-filtry-prepinac'),
      filtryPocet: q('.bmp-filtry-pocet'),
      chipyKat: q('.bmp-chipy-kat'),
      chipyMisto: q('.bmp-chipy-misto'),
      mistPopis: q('.bmp-mist-popis'),
      moje: q('.bmp-btn--moje'),
      mojePocet: q('.bmp-moje-pocet'),
      hledani: q('.bmp-hledani'),
      sdilet: q('.bmp-sdilet'),
      zrusitFiltry: q('.bmp-zrusit-filtry'),
      skocTed: q('.bmp-skoc-ted'),
      detail: q('.bmp-detail'),
      detailObsah: q('.bmp-detail__obsah'),
      detailZavrit: q('.bmp-zavrit')
    };
  }

  /* ─────────────────────────────────────────────────────────────────────
     4. Výchozí stav

     Pravidlo pro výchozí den je jedno a drží i jedenáct měsíců v roce,
     kdy žádný festival neběží: když dnešek padne do některého
     festivalového dne, ukáže se on; jinak první den ročníku. Hranice dne
     v 6:00 hlídá i to, že ve dvě v noci pořád platí včerejšek — kdo
     otevře program během afterparty, uvidí, co ještě hraje.
     ───────────────────────────────────────────────────────────────────── */

  function vychoziDen() {
    if (!DNY.length) return null;
    var dnes = klicDne(TED);
    if (DNY.indexOf(dnes) >= 0) return dnes;
    return DNY[0];
  }
  function stavRocniku() {
    if (!DNY.length) return 'prazdno';
    var dnes = klicDne(TED);
    if (DNY.indexOf(dnes) >= 0) return 'behem';
    if (dnes < DNY[0]) return 'pred';
    return 'po';
  }

  function pripravStav() {
    stav = {
      den: vychoziDen(),
      zobrazeni: window.matchMedia('(min-width: 700px)').matches ? 'lineup' : 'seznam',
      mista: new Set(),
      kategorie: new Set(),
      hledani: '',
      jenMoje: false,
      moje: new Set()
    };

    try {
      JSON.parse(localStorage.getItem(KLIC_ULOZISTE) || '[]')
        .forEach(function (id) { if (+id) stav.moje.add(+id); });
    } catch (e) { /* soukromé okno nebo zakázané úložiště — výběr prostě nepřežije */ }

    /* Výběr se dá poslat kamarádovi odkazem, žádný účet k tomu není.
       Sdílený výběr se k mému přidá, nepřepíše ho — přijít o vlastní
       program kliknutím na cizí odkaz by bylo horší než mít ho navíc. */
    if (param.get('moje')) {
      param.get('moje').split(',').forEach(function (id) {
        if (+id && !stav.moje.has(+id)) { stav.moje.add(+id); prisloOdkazem++; }
      });
      ulozMoje();
    }
    if (prisloOdkazem) {
      /* Kdo přijde po odkazu „tohle je můj program", má ho vidět hned,
         ne ho hledat pod hvězdičkou. */
      stav.jenMoje = true;
      stav.den = 'vse';
      stav.zobrazeni = 'seznam';
    }

    var den = param.get('den');
    if (den && (den === 'vse' || DNY.indexOf(den) >= 0)) stav.den = den;
    var zobr = param.get('zobrazeni');
    if (zobr === 'lineup' || zobr === 'seznam') stav.zobrazeni = zobr;

    /* ?akce=<id> otevře rovnou detail — aby se dala poslat jedna kapela.
       Den se přitom přepne na ten její, ať je za detailem vidět
       správný program. */
    var cil = najdi(+param.get('akce'));
    if (cil) stav.den = cil.den;

    /* Osa umí jeden den — celý týden na jedné svislici by byl nesmysl.
       Když se obojí sejde, vyhraje den a zobrazení se přepne na seznam. */
    if (stav.den === 'vse' && stav.zobrazeni === 'lineup') stav.zobrazeni = 'seznam';
  }

  function ulozMoje() {
    try { localStorage.setItem(KLIC_ULOZISTE, JSON.stringify(Array.from(stav.moje))); }
    catch (e) { /* viz výše */ }
  }

  function zapisAdresu() {
    /* Adresa nese den a zobrazení, aby se dal poslat odkaz na konkrétní
       pohled. Jednorázové parametry z příchozího odkazu se zahazují —
       po přepnutí dne už neplatí. Cizí parametry (utm a podobně)
       zůstávají, nejsou naše. */
    var p = new URLSearchParams(location.search);
    p.delete('moje');
    p.delete('akce');
    if (stav.den) p.set('den', stav.den); else p.delete('den');
    p.set('zobrazeni', stav.zobrazeni);
    var dotaz = p.toString();
    try {
      history.replaceState(null, '', location.pathname + (dotaz ? '?' + dotaz : '') + location.hash);
    } catch (e) { /* některé prohlížeče to u souboru z disku zakazují */ }
  }

  /* ─────────────────────────────────────────────────────────────────────
     5. Výběr akcí podle stavu
     ───────────────────────────────────────────────────────────────────── */

  function proDen(klic) {
    return AKCE.filter(function (a) { return a.den === klic; });
  }
  function projdiFiltry(a) {
    if (stav.jenMoje && !stav.moje.has(a.id)) return false;
    if (stav.mista.size && !stav.mista.has(a.mistoSlug)) return false;
    if (stav.kategorie.size && !stav.kategorie.has(a.katSlug || '_bez')) return false;
    if (stav.hledani && a.hledatelne.indexOf(stav.hledani) < 0) return false;
    return true;
  }
  function viditelne() {
    var zaklad = stav.den === 'vse' ? AKCE : proDen(stav.den);
    return zaklad.filter(projdiFiltry);
  }

  /* Kolize: dvě vybrané akce, které se časově překrývají. To je jediný
     důvod, proč si člověk program vůbec skládá předem. */
  function kolize(seznam) {
    var moje = seznam.filter(function (a) { return stav.moje.has(a.id) && !a.celyDen; });
    var kol = new Set();
    for (var i = 0; i < moje.length; i++) {
      for (var j = i + 1; j < moje.length; j++) {
        if (moje[i].zac < moje[j].kon && moje[j].zac < moje[i].kon) {
          kol.add(moje[i].id); kol.add(moje[j].id);
        }
      }
    }
    return kol;
  }

  function bezi(a) { return TED >= a.zac && TED < a.kon; }

  /* Pořadí scén. The Events Calendar u místa žádné pořadí nedrží, takže
     si ho musíme odvodit. Pravidlo: čím víc akcí daný den, tím dřív —
     a při shodě rozhoduje první začátek, pak abeceda. Je to
     deterministické a nikdo to nemusí udržovat ručně. Kdyby organizátoři
     chtěli hlavní scénu vždycky vlevo, přibude u místa číselné pole
     a předřadí se téhle úvaze. */
  function seraditMista(seznam) {
    var mapa = {};
    seznam.forEach(function (a) {
      if (!a.mistoSlug) return;
      if (!mapa[a.mistoSlug]) {
        mapa[a.mistoSlug] = {
          slug: a.mistoSlug, nazev: a.misto, pocet: 0,
          prvni: a.zac, bezbarierove: a.bezbarierove
        };
      }
      mapa[a.mistoSlug].pocet++;
      if (a.zac < mapa[a.mistoSlug].prvni) mapa[a.mistoSlug].prvni = a.zac;
    });
    return Object.keys(mapa).map(function (k) { return mapa[k]; })
      .sort(function (a, b) {
        return b.pocet - a.pocet || a.prvni - b.prvni || a.nazev.localeCompare(b.nazev, 'cs');
      });
  }

  /* ─────────────────────────────────────────────────────────────────────
     6. Vykreslení
     ───────────────────────────────────────────────────────────────────── */

  function vykresli() {
    /* Překreslení zahodí tlačítka i s fokusem. Kdo ovládá program
       klávesnicí, by po každém ťuknutí spadl na začátek stránky, proto
       se stejné tlačítko po překreslení najde znovu. */
    var stopa = stopaFokusu();

    vykresliDny();
    vykresliFiltry();

    koren.querySelectorAll('[data-zobrazeni]').forEach(function (b) {
      b.setAttribute('aria-pressed', String(b.getAttribute('data-zobrazeni') === stav.zobrazeni));
    });
    el.moje.setAttribute('aria-pressed', String(stav.jenMoje));
    el.mojePocet.textContent = stav.moje.size;

    var akce = viditelne();
    var kol = kolize(akce);
    var casove = akce.filter(function (a) { return !a.celyDen; });
    var celodenni = akce.filter(function (a) { return a.celyDen; });

    vykresliShrnuti(akce, casove, kol);
    vykresliCelyDen(celodenni);

    if (!akce.length) {
      el.vysledek.innerHTML = prazdno();
    } else if (stav.zobrazeni === 'lineup') {
      el.vysledek.innerHTML = vykresliOsu(casove);
      umistiTedVOse();
    } else {
      el.vysledek.innerHTML = vykresliSeznam(casove, kol);
    }

    el.skocTed.hidden = stavRocniku() !== 'behem';
    /* Hláška o sdíleném odkazu je jednorázová. Kdyby zůstala viset,
       přebila by po zbytek návštěvy upozornění na kolize — a to je ta
       informace, kvůli které si člověk program skládá. */
    prisloOdkazem = 0;
    zapisAdresu();
    dopocitejRozmery();
    vratFokus(stopa);
  }

  function stopaFokusu() {
    var a = document.activeElement;
    if (!a || !koren.contains(a)) return null;
    var atr = ['data-hvezda', 'data-den', 'data-filtr-kat', 'data-filtr-misto', 'data-akce'];
    for (var i = 0; i < atr.length; i++) {
      if (a.hasAttribute(atr[i])) {
        return 'button[' + atr[i] + '="' + a.getAttribute(atr[i]) + '"]';
      }
    }
    return null;
  }
  function vratFokus(stopa) {
    if (!stopa) return;
    var p = koren.querySelector(stopa);
    if (p) p.focus({ preventScroll: true });
  }

  /* ── Dny ───────────────────────────────────────────────────────────── */

  function vykresliDny() {
    if (!DNY.length) { el.dny.innerHTML = ''; return; }
    /* „Vše" dává smysl jen v seznamu — osa potřebuje jeden den. Kdo na něj
       klikne a je v lineupu, přepne se do seznamu; je to menší překvapení
       než prázdná osa. */
    var html = tlacitkoDne('vse', 'VŠE', stav.jenMoje ? pocetMoje(null) + '×' : 'celý týden', false);
    DNY_INFO.forEach(function (d) {
      var popisek = stav.jenMoje ? d.datum + ' · ' + pocetMoje(d.klic) + '×' : d.datum;
      html += tlacitkoDne(d.klic, d.zkratka, popisek, klicDne(TED) === d.klic);
    });
    el.dny.innerHTML = html;
  }
  function pocetMoje(klicDneNeboNull) {
    return AKCE.filter(function (a) {
      return stav.moje.has(a.id) && (!klicDneNeboNull || a.den === klicDneNeboNull);
    }).length;
  }
  function tlacitkoDne(klic, zkratka, datum, dnes) {
    return '<button type="button" class="bmp-den' + (dnes ? ' bmp-den--dnes' : '') + '" ' +
      'data-den="' + esc(klic) + '" aria-pressed="' + (stav.den === klic) + '">' +
      '<span class="bmp-den__zkratka">' + esc(zkratka) + '</span>' +
      '<span class="bmp-den__datum">' + esc(datum) + '</span></button>';
  }

  /* ── Filtry ────────────────────────────────────────────────────────── */

  function vykresliFiltry() {
    var zaklad = stav.den === 'vse' ? AKCE : proDen(stav.den);

    var kat = [];
    zaklad.forEach(function (a) {
      var s = a.katSlug || '_bez';
      if (!kat.some(function (k) { return k.slug === s; })) {
        kat.push({ slug: s, nazev: a.kat || 'Bez kategorie', barva: a.barva });
      }
    });
    kat.sort(function (a, b) { return a.nazev.localeCompare(b.nazev, 'cs'); });
    el.chipyKat.innerHTML = kat.map(function (k) {
      return '<button type="button" class="bmp-chip bmp-chip--kat" data-kat="' + esc(k.slug) + '" ' +
        'data-filtr-kat="' + esc(k.slug) + '" aria-pressed="' + stav.kategorie.has(k.slug) + '"' +
        (k.barva ? ' style="--kat:' + esc(k.barva) + '"' : '') + '>' +
        '<span class="bmp-chip__tecka"></span>' + esc(k.nazev) + '</button>';
    }).join('');

    var mista = seraditMista(zaklad);
    el.mistPopis.textContent = '(' + mista.length + ')';
    el.chipyMisto.innerHTML = mista.map(function (m) {
      return '<button type="button" class="bmp-chip" data-filtr-misto="' + esc(m.slug) + '" ' +
        'aria-pressed="' + stav.mista.has(m.slug) + '">' + esc(m.nazev) +
        ' <span class="bmp-chip__pocet">' + m.pocet + '</span></button>';
    }).join('');

    var pocet = stav.mista.size + stav.kategorie.size + (stav.hledani ? 1 : 0);
    el.filtryPocet.hidden = pocet === 0;
    el.filtryPocet.textContent = pocet;
  }

  /* ── Shrnutí a hlášky ──────────────────────────────────────────────── */

  function vykresliShrnuti(akce, casove, kol) {
    var mista = seraditMista(akce).length;
    var popis = (!stav.den || stav.den === 'vse')
      ? 'celý týden'
      : (function () { var d = popisDne(stav.den); return d.nazev + ' ' + d.datum; })();

    var h = '<div><strong>' + akce.length + '</strong> vystoupení</div>';
    h += '<div><strong>' + mista + '</strong> ' + sklonuj(mista, 'místo', 'místa', 'míst') + '</div>';
    if (casove.length && stav.den !== 'vse') {
      h += '<div>' + hhmm(casove[0].zac) + '–' + hhmm(casove[casove.length - 1].kon) + '</div>';
    }
    h += '<div class="bmp-shrnuti__popis">' + esc(popis) + '</div>';
    el.shrnuti.innerHTML = h;

    var zprava = '';
    if (prisloOdkazem) {
      zprava = '★ Ze sdíleného odkazu ' +
        sklonuj(prisloOdkazem, 'přibylo', 'přibyla', 'přibylo') + ' ' +
        prisloOdkazem + ' vystoupení k tvému výběru.';
    } else if (kol.size) {
      zprava = '⚠ Ve výběru máš ' + kol.size + ' ' + sklonuj(kol.size,
        'akci, která se časově překrývá',
        'akce, které se časově překrývají',
        'akcí, které se časově překrývají') +
        ' s jinou — jsou označené štítkem KOLIZE.';
    } else if (stavRocniku() === 'pred') {
      /* Datum už tečku nese („31. 5."), druhá by za ním byla navíc. */
      zprava = 'Festival ještě nezačal. Ukazujeme první den ročníku — ' +
        DNY_INFO[0].nazev + ' ' + DNY_INFO[0].datum;
    } else if (stavRocniku() === 'po') {
      zprava = 'Ročník už skončil. Tohle je jeho program, ne nadcházející. ' +
        'Nový se objeví, až ho dramaturgie zveřejní.';
    }
    el.stav.hidden = !zprava;
    el.stav.textContent = zprava;
  }

  function vykresliCelyDen(seznam) {
    el.celoden.hidden = !seznam.length;
    if (!seznam.length) return;
    /* Výstava otevřená od jedenácti do půlnoci by v ose udělala sloupec
       vysoký přes celý den a ostatní bloky by se do něj schovaly. Proto
       má vlastní pás nad osou — a v seznamu je nahoře, ne mezi sety. */
    el.celodenPasy.innerHTML = seznam.map(function (a) {
      return '<button type="button" class="bmp-celoden__pas" data-kat="' + esc(a.katSlug) + '" ' +
        'data-akce="' + a.id + '"' + (a.barva ? ' style="background:' + esc(a.barva) + '"' : '') + '>' +
        esc(a.nazev) + '<small>' + hhmm(a.zac) + '–' + hhmm(a.kon) + ' · ' + esc(a.misto) + '</small></button>';
    }).join('');
  }

  function prazdno() {
    if (stavRocniku() === 'prazdno') {
      return '<div class="bmp-prazdno"><h3>Program se připravuje</h3>' +
        '<p>Dramaturgie ho ladí. Jakmile bude hotový, objeví se tady.</p>' +
        '<p><a class="bmp-btn bmp-btn--zvyrazneny" href="/aktuality/">Aktuality</a></p></div>';
    }
    return '<div class="bmp-prazdno"><h3>Tady zatím nic není</h3>' +
      '<p>' + (stav.jenMoje
        ? 'V tenhle den nemáš vybranou žádnou akci. Hvězdičkou si přidej, co tě zajímá.'
        : 'Zkus zrušit filtry nebo přepnout den.') + '</p></div>';
  }

  /* ── Lineup (časová osa) ───────────────────────────────────────────── */

  function pxZaMinutu() {
    return parseFloat(getComputedStyle(koren).getPropertyValue('--bmp-osa-px-za-minutu')) || 1.4;
  }

  function vykresliOsu(akce) {
    if (!akce.length) return prazdno();

    var mista = seraditMista(akce);
    var zac = new Date(Math.min.apply(null, akce.map(function (a) { return +a.zac; })));
    var kon = new Date(Math.max.apply(null, akce.map(function (a) { return +a.kon; })));
    zac.setMinutes(0, 0, 0);
    kon.setMinutes(0, 0, 0); kon.setHours(kon.getHours() + 1);

    var pxm = pxZaMinutu();
    var vyska = Math.round((kon - zac) / 60000 * pxm);

    var casy = '';
    for (var t = new Date(zac); t < kon; t.setHours(t.getHours() + 1)) {
      casy += '<div class="bmp-osa__cas" style="top:' + Math.round((t - zac) / 60000 * pxm) + 'px">' +
        hhmm(t) + '</div>';
    }

    var hlavicky = '', sloupce = '';
    mista.forEach(function (m) {
      hlavicky += '<div class="bmp-osa__misto" title="' + esc(m.nazev) + '"><b>' + esc(m.nazev) + '</b>' +
        (m.bezbarierove ? '<span>bezbariérové</span>' : '') + '</div>';

      var vMiste = akce.filter(function (a) { return a.mistoSlug === m.slug; })
        .sort(function (a, b) { return a.zac - b.zac; });
      sloupce += '<div class="bmp-osa__sloupec" data-sloupec="' + esc(m.slug) + '">' +
        vMiste.map(function (a) { return blokOsy(a, zac, pxm, vMiste); }).join('') +
        '</div>';
    });

    return '<div class="bmp-osa" style="--sloupcu:' + mista.length +
      ';--vyska:' + vyska + 'px;--hodina:' + Math.round(60 * pxm) + 'px">' +
      '<div class="bmp-osa__vnitrek">' +
      '<div class="bmp-osa__roh"></div>' + hlavicky +
      '<div class="bmp-osa__casy">' + casy + '</div>' + sloupce +
      '</div></div>';
  }

  function blokOsy(a, zacDne, pxm, sousede) {
    var top = Math.round((a.zac - zacDne) / 60000 * pxm);
    var v = Math.max(26, Math.round((a.kon - a.zac) / 60000 * pxm) - 3);
    /* Na jednom místě se občas dvě akce překrývají (workshop běžící přes
       koncert). Místo schovávání se rozdělí šířka sloupce. */
    var prekryv = sousede.filter(function (b) { return b !== a && b.zac < a.kon && a.zac < b.kon; });
    var sirka = '';
    if (prekryv.length) {
      var skupina = [a].concat(prekryv).sort(function (x, y) { return x.zac - y.zac || x.id - y.id; });
      var i = skupina.indexOf(a), n = skupina.length;
      sirka = 'left:calc(3px + ' + (i / n * 100) + '%);width:calc(' + (100 / n) + '% - 6px);right:auto;';
    }
    return '<button type="button" class="bmp-osa__blok' +
      (v < 42 ? ' bmp-osa__blok--kratky' : '') +
      (a.top ? ' bmp-osa__blok--top' : '') +
      (stav.moje.has(a.id) ? ' bmp-osa__blok--moje' : '') +
      '" data-kat="' + esc(a.katSlug) + '" data-akce="' + a.id + '"' +
      ' style="top:' + top + 'px;height:' + v + 'px;' + sirka +
      (a.barva ? 'background:' + esc(a.barva) + ';' : '') + '">' +
      '<span>' + esc(a.nazev) + '</span>' +
      '<small>' + hhmm(a.zac) + '–' + hhmm(a.kon) + (a.rezervace ? ' · rezervace' : '') + '</small>' +
      '</button>';
  }

  function umistiTedVOse() {
    if (stavRocniku() !== 'behem' || klicDne(TED) !== stav.den) return;
    var osa = koren.querySelector('.bmp-osa');
    if (!osa) return;
    var akce = viditelne().filter(function (a) { return !a.celyDen; });
    if (!akce.length) return;
    var zac = new Date(Math.min.apply(null, akce.map(function (a) { return +a.zac; })));
    zac.setMinutes(0, 0, 0);
    var top = Math.round((TED - zac) / 60000 * pxZaMinutu());
    if (top < 0) return;
    osa.querySelectorAll('.bmp-osa__sloupec').forEach(function (s, i) {
      var c = document.createElement('div');
      c.className = 'bmp-osa__ted' + (i === 0 ? ' bmp-osa__ted--popisek' : '');
      c.style.top = top + 'px';
      s.appendChild(c);
    });
    osa.scrollTop = Math.max(0, top - 140);
  }

  /* ── Seznam ────────────────────────────────────────────────────────── */

  function vykresliSeznam(akce, kol) {
    if (!akce.length) return prazdno();
    var html = '<div class="bmp-seznam">', poslDen = '', poslCas = '';
    akce.forEach(function (a) {
      if (stav.den === 'vse' && a.den !== poslDen) {
        /* Zavřít se musí jen slot, který je opravdu otevřený — jinak
           se u prvního dne vypíše ukončovací značka navíc a celý
           týdenní výpis vypadne z odsazení. */
        if (poslCas) html += '</div></section>';
        poslDen = a.den;
        poslCas = '';
        var d = popisDne(a.den);
        html += '<div class="bmp-slot__den">' + d.nazev + ' ' + d.datum + '</div>';
      }
      var cas = hhmm(a.zac);
      if (cas !== poslCas) {
        if (poslCas) html += '</div></section>';
        poslCas = cas;
        /* Nadpis s časem drží nahoře, takže při posouvání je pořád vidět,
           u kolikáté hodiny člověk je. To je celá odpověď na otázku
           „co hraje ve 20:00" — všechno, co je pod tímhle nadpisem. */
        html += '<section class="bmp-slot"><h3 class="bmp-slot__cas"><b>' + cas + '</b>' +
          '<span>' + pocetVSlotu(akce, cas) + '</span></h3><div class="bmp-karty">';
      }
      html += karta(a, kol.has(a.id));
    });
    return html + (poslCas ? '</div></section>' : '') + '</div>';
  }

  function pocetVSlotu(akce, cas) {
    var n = akce.filter(function (a) { return hhmm(a.zac) === cas; }).length;
    return n > 1 ? n + ' akce současně' : '';
  }

  function karta(a, jeKolize) {
    var moje = stav.moje.has(a.id);
    return '<article class="bmp-karta' + (moje ? ' bmp-karta--moje' : '') +
      (bezi(a) ? ' bmp-karta--ted' : '') + '" data-kat="' + esc(a.katSlug) + '" data-akce="' + a.id + '">' +
      '<div class="bmp-karta__pruh"' + (a.barva ? ' style="background:' + esc(a.barva) + '"' : '') + '></div>' +
      '<div class="bmp-karta__telo">' +
      '<div class="bmp-karta__meta">' +
      '<span>' + hhmm(a.zac) + '–' + hhmm(a.kon) + '</span>' +
      (a.mistoSlug
        ? '<button type="button" class="bmp-stitek bmp-stitek--misto" data-filtr-misto="' +
          esc(a.mistoSlug) + '">' + esc(a.misto) + '</button>'
        : '') +
      (a.bezbarierove ? '<span class="bmp-bez-barier">bez bariér</span>' : '') +
      '</div>' +
      '<h3 class="bmp-karta__nazev"><button type="button" data-akce="' + a.id + '">' +
      esc(a.nazev) + '</button></h3>' +
      '<div class="bmp-karta__stitky">' +
      (a.kat ? '<span class="bmp-stitek bmp-stitek--kat"' +
        (a.barva ? ' style="background:' + esc(a.barva) + '"' : '') + '>' + esc(a.kat) + '</span>' : '') +
      (a.zanr ? '<span class="bmp-stitek">' + esc(a.zanr) + '</span>' : '') +
      (a.rezervace ? '<span class="bmp-stitek bmp-stitek--rez">Rezervace</span>' : '') +
      (a.top ? '<span class="bmp-stitek bmp-stitek--top">Top</span>' : '') +
      (bezi(a) ? '<span class="bmp-stitek bmp-stitek--ted">Teď hraje</span>' : '') +
      (jeKolize ? '<span class="bmp-stitek bmp-stitek--kolize">Kolize</span>' : '') +
      '</div></div>' +
      '<button type="button" class="bmp-hvezda" data-hvezda="' + a.id + '" aria-pressed="' + moje + '" ' +
      'aria-label="' + (moje ? 'Odebrat z mého programu' : 'Přidat do mého programu') + '">★</button>' +
      '</article>';
  }

  /* ─────────────────────────────────────────────────────────────────────
     7. Detail akce
     ───────────────────────────────────────────────────────────────────── */

  function otevriDetail(id) {
    var a = najdi(id);
    if (!a) return;
    /* „Ve stejnou dobu jinde" je ta samá informace, pro kterou lidi
       chodí na osu, jen podaná jednou akcí. */
    var zaroven = AKCE.filter(function (x) {
      return x.id !== a.id && !x.celyDen && x.zac < a.kon && a.zac < x.kon;
    }).slice(0, 6);
    var moje = stav.moje.has(a.id);
    var d = popisDne(a.den);

    el.detailObsah.innerHTML =
      (a.obraz ? '<img class="bmp-detail__obraz" src="' + esc(a.obraz) + '" alt="">' : '') +
      '<div class="bmp-detail__telo">' +
      '<p class="bmp-detail__meta">' + d.nazev + ' ' + d.datum + ' · ' +
      hhmm(a.zac) + '–' + hhmm(a.kon) + (a.misto ? ' · ' + esc(a.misto) : '') +
      (a.bezbarierove ? ' · bezbariérové' : '') + '</p>' +
      '<h2>' + esc(a.nazev) + '</h2>' +
      '<div class="bmp-karta__stitky">' +
      (a.kat ? '<span class="bmp-stitek bmp-stitek--kat" data-kat="' + esc(a.katSlug) + '"' +
        (a.barva ? ' style="background:' + esc(a.barva) + '"' : '') + '>' + esc(a.kat) + '</span>' : '') +
      (a.zanr ? '<span class="bmp-stitek">' + esc(a.zanr) + '</span>' : '') +
      (a.zeme ? '<span class="bmp-stitek">' + esc(a.zeme) + '</span>' : '') +
      (a.rezervace ? '<span class="bmp-stitek bmp-stitek--rez">Rezervace</span>' : '') +
      (a.top ? '<span class="bmp-stitek bmp-stitek--top">Top představení</span>' : '') +
      '</div>' +
      /* Výřez je useknutý v polovině věty, proto výpustka — ale jen
         když si ji plugin nepřidal sám. */
      (a.popis ? '<div class="bmp-detail__popis">' + esc(a.popis) +
        (/[.!?…]$/.test(a.popis) ? '' : '…') + '</div>' : '') +
      (zaroven.length ? '<div class="bmp-detail__zaroven"><h3>Ve stejnou dobu jinde</h3><ul>' +
        zaroven.map(function (z) {
          return '<li><b>' + hhmm(z.zac) + '</b>' + esc(z.nazev) +
            (z.misto ? ' <span>· ' + esc(z.misto) + '</span>' : '') + '</li>';
        }).join('') + '</ul></div>' : '') +
      '<div class="bmp-detail__akce">' +
      '<button type="button" class="bmp-btn' + (moje ? ' bmp-btn--zvyrazneny' : '') +
      '" data-hvezda="' + a.id + '" aria-pressed="' + moje + '">' +
      '★ ' + (moje ? 'V mém programu' : 'Přidat do mého programu') + '</button>' +
      (a.url ? '<a class="bmp-btn bmp-btn--hlavni" href="' + esc(a.url) + '">Detail akce</a>' : '') +
      '</div></div>';

    /* Když se obrázek nenačte, ať po něm nezbyde prázdná díra. Obsluha
       se věší tady, ne atributem onerror — ten by padl na přísnějším
       Content-Security-Policy. */
    var obraz = el.detailObsah.querySelector('.bmp-detail__obraz');
    if (obraz) obraz.addEventListener('error', function () { this.remove(); });

    /* showModal() na už otevřeném dialogu vyhodí výjimku, a to se stane
       pokaždé, když se hvězdička zmáčkne přímo v detailu. */
    if (!el.detail.open) el.detail.showModal();
  }

  /* ─────────────────────────────────────────────────────────────────────
     8. Události
     ───────────────────────────────────────────────────────────────────── */

  function pripojUdalosti() {
    /* Delegace visí na kořeni programu, ne na dokumentu — atributy jako
       data-akce může mít i cizí plugin. */
    koren.addEventListener('click', function (ev) {
      var t = ev.target.closest(
        '[data-den],[data-hvezda],[data-akce],[data-filtr-misto],[data-filtr-kat],[data-zobrazeni]');
      if (!t || !koren.contains(t)) return;

      if (t.hasAttribute('data-hvezda')) {
        var hid = +t.getAttribute('data-hvezda');
        if (stav.moje.has(hid)) stav.moje.delete(hid); else stav.moje.add(hid);
        ulozMoje();
        if (el.detail.open) otevriDetail(hid);
        vykresli();
        return;
      }
      if (t.hasAttribute('data-filtr-misto')) {
        prepni(stav.mista, t.getAttribute('data-filtr-misto'));
        vykresli();
        return;
      }
      if (t.hasAttribute('data-filtr-kat')) {
        prepni(stav.kategorie, t.getAttribute('data-filtr-kat'));
        vykresli();
        return;
      }
      if (t.hasAttribute('data-den')) {
        stav.den = t.getAttribute('data-den');
        if (stav.den === 'vse' && stav.zobrazeni === 'lineup') stav.zobrazeni = 'seznam';
        /* Scény se den ode dne mění, kategorie ne. Filtr místa by tedy po
           přepnutí dne většinou ukázal prázdno — proto se maže, kategorie ne. */
        stav.mista.clear();
        vykresli();
        return;
      }
      if (t.hasAttribute('data-zobrazeni')) {
        stav.zobrazeni = t.getAttribute('data-zobrazeni');
        if (stav.zobrazeni === 'lineup' && stav.den === 'vse') stav.den = vychoziDen();
        vykresli();
        return;
      }
      if (t.hasAttribute('data-akce')) otevriDetail(+t.getAttribute('data-akce'));
    });

    /* Vybrané akce jsou skoro vždycky rozházené po celém týdnu. Kdyby
       „Jen moje" nechalo zapnutý jeden den, většina lidí by poprvé
       uviděla prázdnou stránku. Proto se zapnutím přepne na celý týden
       a při vypnutí se vrátí den, na kterém člověk byl. */
    el.moje.addEventListener('click', function () {
      stav.jenMoje = !stav.jenMoje;
      if (stav.jenMoje) {
        denPredMoje = stav.den;
        stav.den = 'vse';
        stav.zobrazeni = 'seznam';
      } else if (denPredMoje) {
        stav.den = denPredMoje;
        denPredMoje = null;
      }
      vykresli();
    });

    el.filtryPrepinac.addEventListener('click', function () {
      el.filtry.hidden = !el.filtry.hidden;
      this.setAttribute('aria-expanded', String(!el.filtry.hidden));
      dopocitejRozmery();
    });

    el.zrusitFiltry.addEventListener('click', function () {
      stav.mista.clear();
      stav.kategorie.clear();
      stav.hledani = '';
      el.hledani.value = '';
      vykresli();
    });

    el.sdilet.addEventListener('click', function () {
      var u = location.origin + location.pathname + '?den=' + encodeURIComponent(stav.den) +
        '&zobrazeni=' + stav.zobrazeni + '&moje=' + Array.from(stav.moje).join(',');
      var btn = this;
      if (navigator.clipboard) {
        navigator.clipboard.writeText(u).then(function () {
          btn.textContent = 'Odkaz zkopírován ✓';
          setTimeout(function () { btn.textContent = 'Sdílet můj výběr'; }, 2200);
        }, function () { prompt('Odkaz na tvůj výběr:', u); });
      } else {
        prompt('Odkaz na tvůj výběr:', u);
      }
    });

    el.skocTed.addEventListener('click', function () {
      stav.den = vychoziDen();
      vykresli();
      var ted = koren.querySelector('.bmp-osa__ted, .bmp-karta--ted');
      if (ted) ted.scrollIntoView({ block: 'center' });
    });

    var casovacHledani;
    el.hledani.addEventListener('input', function (e) {
      var v = bezDiakritiky(e.target.value.trim());
      clearTimeout(casovacHledani);
      casovacHledani = setTimeout(function () { stav.hledani = v; vykresli(); }, 180);
    });

    el.detailZavrit.addEventListener('click', function () { el.detail.close(); });
    el.detail.addEventListener('click', function (e) {
      /* Kliknutí mimo obsah dialogu (do ztmaveného okolí) ho zavře. */
      if (e.target === el.detail) el.detail.close();
    });

    window.addEventListener('resize', dopocitejRozmery);
  }

  function prepni(mnozina, hodnota) {
    if (mnozina.has(hodnota)) mnozina.delete(hodnota); else mnozina.add(hodnota);
  }

  /* Dvě míry, které CSS samo nespočítá:
     — o kolik posunout lepivé nadpisy hodin v seznamu, aby nezalezly
       pod lepivý pruh s ovládáním,
     — jak široký je výřez. Osa se podle toho na mobilu vylomí z okrajů
       stránky. Nejde použít 100vw: to počítá i svislý posuvník a na
       úzkém okně v počítači by z toho vznikl vodorovný posuv stránky. */
  function dopocitejRozmery() {
    if (!el) return;
    koren.style.setProperty('--bmp-lep', el.ovladani.offsetHeight + 'px');
    koren.style.setProperty('--bmp-vyrez', document.documentElement.clientWidth + 'px');
  }
})();
