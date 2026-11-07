/* ══════════════════════════════════════════════════════════════════════
   Ikonka „zkopírovat" u čísla účtu.

   Ikonku vyrábí až tenhle skript, ne PHP. Důvod: kdyby byla v HTML
   a skript se nenačetl, zůstalo by na stránce tlačítko, které nic nedělá.
   Takhle se objeví jen tam, kde opravdu funguje — a když ne, číslo je
   pořád vybrané na jedno kliknutí (user-select: all v bm.css).

   Po zkopírování se ikonka na dvě a půl vteřiny změní na zelenou fajfku.
   Zelenou dělá třída bm-kopirovat--hotovo v bm.css, ne tenhle soubor.
   ══════════════════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  /* Obě ikonky jsou tady natvrdo, ať si stránka kvůli nim nic nestahuje.
     aria-hidden proto, že co tlačítko dělá, říká jeho aria-label. */
  var IKONA_KOPIE =
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"' +
    ' stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' +
    '<rect x="9" y="9" width="12" height="12" rx="2"/>' +
    '<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>';

  var IKONA_HOTOVO =
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"' +
    ' stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' +
    '<path d="M20 6 9 17l-5-5"/></svg>';

  if (!navigator.clipboard || !navigator.clipboard.writeText) return;

  var cisla = document.querySelectorAll('.bm-kopirovatelne');
  if (!cisla.length) return;

  Array.prototype.forEach.call(cisla, function (cislo) {
    /* Mezery kolem lomítka jsou kvůli čitelnosti na stránce; do schránky
       patří číslo bez nich, ať se dá rovnou vložit do bankovnictví.
       Čte se dřív, než se dovnitř přidá tlačítko. */
    var hodnota = cislo.textContent.replace(/\s+/g, '');

    var tlacitko = document.createElement('button');
    tlacitko.type = 'button';
    tlacitko.className = 'bm-kopirovat';
    tlacitko.innerHTML = IKONA_KOPIE;
    tlacitko.title = 'Zkopírovat číslo účtu';
    tlacitko.setAttribute('aria-label', 'Zkopírovat číslo účtu');

    var casovac;

    function vrat() {
      tlacitko.innerHTML = IKONA_KOPIE;
      tlacitko.classList.remove('bm-kopirovat--hotovo');
      tlacitko.title = 'Zkopírovat číslo účtu';
      tlacitko.setAttribute('aria-label', 'Zkopírovat číslo účtu');
    }

    tlacitko.addEventListener('click', function () {
      navigator.clipboard.writeText(hodnota).then(function () {
        tlacitko.innerHTML = IKONA_HOTOVO;
        tlacitko.classList.add('bm-kopirovat--hotovo');
        tlacitko.title = 'Zkopírováno';
        tlacitko.setAttribute('aria-label', 'Číslo účtu zkopírováno');
      }, function () {
        /* Schránku prohlížeč odmítl (starší Safari, soukromé okno).
           Zelená fajfka by tu lhala, tak číslo aspoň označíme. */
        var vyber = window.getSelection();
        var rozsah = document.createRange();
        rozsah.selectNodeContents(cislo);
        vyber.removeAllRanges();
        vyber.addRange(rozsah);
        tlacitko.title = 'Označeno — zkopíruj klávesnicí';
        tlacitko.setAttribute('aria-label', 'Číslo účtu je označené, zkopíruj ho klávesnicí');
      });

      clearTimeout(casovac);
      casovac = setTimeout(vrat, 2500);
    });

    cislo.appendChild(tlacitko);
  });
})();
