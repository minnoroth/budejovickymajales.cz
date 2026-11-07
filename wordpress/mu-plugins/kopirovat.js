/* ══════════════════════════════════════════════════════════════════════
   Ikonka „zkopírovat" u čísla účtu.

   Ikonku vyrábí až tenhle skript, ne PHP. Důvod: kdyby byla v HTML
   a skript se nenačetl, zůstalo by na stránce tlačítko, které nic nedělá.
   Takhle se objeví jen tam, kde opravdu funguje — a když ne, číslo je
   pořád vybrané na jedno kliknutí (user-select: all v bm.css).

   Po zkopírování se ikonka na dvě a půl vteřiny změní na zelenou fajfku.
   Zelenou dělá třída bm-kopirovat--hotovo v bm.css, ne tenhle soubor.

   Kopíruje se dvěma cestami, protože moderní Clipboard API je leckde
   zakázané (starší Safari, soukromé okno, vestavěné prohlížeče —
   zrovna v tom v Claude Code vrací NotAllowedError). Když odmítne,
   zkusí se ještě execCommand('copy'). Je zastaralý, ale funkční,
   a bez něj by na takovém prohlížeči tlačítko jen označilo text.
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

  var umiNove = !!(navigator.clipboard && navigator.clipboard.writeText);
  var umiStare = !!(document.queryCommandSupported && document.queryCommandSupported('copy'));
  if (!umiNove && !umiStare) return;

  var cisla = document.querySelectorAll('.bm-kopirovatelne');
  if (!cisla.length) return;

  /**
   * Zkopíruje text zastaralou cestou: přes schované pole a execCommand.
   * Kopíruje se z pole, ne z rámečku na stránce — v rámečku jsou kolem
   * lomítka mezery kvůli čitelnosti a do bankovnictví nepatří.
   *
   * @param {string} hodnota Text do schránky.
   * @returns {boolean} Jestli to prohlížeč přijal.
   */
  function stareKopirovani(hodnota) {
    var pole = document.createElement('textarea');
    pole.value = hodnota;
    pole.setAttribute('readonly', '');
    /* Mimo obraz, ale ne display:none — z neviditelného pole se nekopíruje.
       position: fixed, aby stránka neposkočila. */
    pole.style.cssText = 'position:fixed;top:0;left:-9999px;opacity:0';
    document.body.appendChild(pole);

    pole.select();
    pole.setSelectionRange(0, hodnota.length);

    var prijato = false;
    try {
      prijato = document.execCommand('copy');
    } catch (chyba) {
      prijato = false;
    }
    pole.remove();
    return prijato;
  }

  Array.prototype.forEach.call(cisla, function (cislo) {
    /* Čte se dřív, než se dovnitř přidá tlačítko. */
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

    function hotovo() {
      tlacitko.innerHTML = IKONA_HOTOVO;
      tlacitko.classList.add('bm-kopirovat--hotovo');
      tlacitko.title = 'Zkopírováno';
      tlacitko.setAttribute('aria-label', 'Číslo účtu zkopírováno');
    }

    function oznac() {
      /* Poslední záchrana: zelená fajfka by tu lhala, tak číslo aspoň
         označíme, ať stačí jedno Cmd+C. */
      var vyber = window.getSelection();
      var rozsah = document.createRange();
      rozsah.selectNodeContents(cislo);
      vyber.removeAllRanges();
      vyber.addRange(rozsah);
      tlacitko.title = 'Označeno — zkopíruj klávesnicí';
      tlacitko.setAttribute('aria-label', 'Číslo účtu je označené, zkopíruj ho klávesnicí');
    }

    function zaloha() {
      if (stareKopirovani(hodnota)) hotovo();
      else oznac();
    }

    tlacitko.addEventListener('click', function () {
      if (umiNove) {
        navigator.clipboard.writeText(hodnota).then(hotovo, zaloha);
      } else {
        zaloha();
      }

      clearTimeout(casovac);
      casovac = setTimeout(vrat, 2500);
    });

    cislo.appendChild(tlacitko);
  });
})();
