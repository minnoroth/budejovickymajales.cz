/* ══════════════════════════════════════════════════════════════════════
   Uložení QR kódu do telefonu.

   Proč to existuje:
   Na telefonu se QR kód naskenovat nedá — přístroj se nevyfotí sám.
   Návrh počítal s tlačítkem „Otevřít v bance", jenže žádná česká banka
   neumí přijmout platbu z odkazu ve webové stránce: SPAYD je norma jen
   pro obsah kódu, ne pro URL, a cesta přes Android Intent funguje jen
   z nativní aplikace. Co zbývá a opravdu funguje: kód uložit a v bankovní
   aplikaci ho načíst z galerie. Částku i zprávu má pak člověk předvyplněné.

   Odkaz vyrábí až skript, ne PHP. Kdyby byl v HTML a skript se nenačetl,
   zůstalo by na stránce tlačítko, které nikam nevede.

   Je to <a download>, ne <button>: i kdyby obsluha kliknutí selhala,
   prohlížeč pořád umí obrázek stáhnout nebo otevřít sám.
   ══════════════════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  var ucet = document.querySelector('.bm-qr__ucet');
  var cislo = ucet && ucet.querySelector('.bm-qr__cislo');
  if (!ucet || !cislo) return;

  /* Starý prohlížeč bez `download` by odkaz jen otevřel v nové kartě.
     To je pořád použitelné (jde podržet prstem a uložit), takže se
     nevzdáváme — vzdáváme se jen tam, kde není ani kód. */
  if (!document.querySelector('.bm-qr__kod')) return;

  var odkaz = document.createElement('a');
  odkaz.className = 'bm-sdilet';
  odkaz.textContent = 'Uložit kód do telefonu';

  var rada = document.createElement('p');
  rada.className = 'bm-sdilet__rada';
  rada.textContent = 'Pak ho v bankovní aplikaci načti z galerie — částku i zprávu máš předvyplněnou.';

  /** Obrázek odpovídající zaškrtnuté částce. */
  function vybranyKod() {
    var volba = document.querySelector('.bm-qr__volba:checked');
    var kod = volba && document.querySelector('.bm-qr__kod--' + volba.id.replace('bm-qr-', ''));
    return kod || document.querySelector('.bm-qr__kod');
  }

  /** Přepne odkaz na kód právě vybrané částky. */
  function nastav() {
    var kod = vybranyKod();
    if (!kod) return;
    var volba = document.querySelector('.bm-qr__volba:checked');
    odkaz.href = kod.currentSrc || kod.src;
    odkaz.download = 'qr-platba-majales-' + (volba ? volba.id.replace('bm-qr-', '') : 'dar') + '.png';
  }

  nastav();
  Array.prototype.forEach.call(document.querySelectorAll('.bm-qr__volba'), function (volba) {
    volba.addEventListener('change', nastav);
  });

  /* Sdílení je lepší než stažení: na telefonu otevře systémový panel,
     kde je „Uložit obrázek" i rovnou bankovní aplikace, pokud obrázky
     přijímá. Když prohlížeč sdílení souborů neumí, klik se nechá projít
     a obrázek se prostě stáhne. */
  odkaz.addEventListener('click', function (udalost) {
    if (!navigator.canShare || !navigator.share) return;

    var kod = vybranyKod();
    if (!kod) return;

    udalost.preventDefault();

    fetch(odkaz.href).then(function (odpoved) {
      return odpoved.blob();
    }).then(function (blob) {
      var soubor = new File([blob], odkaz.download, { type: blob.type || 'image/png' });
      if (!navigator.canShare({ files: [soubor] })) throw new Error('soubory nejdou sdílet');
      return navigator.share({ files: [soubor], title: 'QR platba — Budějovický Majáles' });
    }).catch(function (chyba) {
      /* Zavření panelu uživatelem není chyba a nesmí nic spustit. */
      if (chyba && chyba.name === 'AbortError') return;
      /* Cokoli jiného (offline, prohlížeč couvl) — stáhni to postaru. */
      var nahradni = document.createElement('a');
      nahradni.href = odkaz.href;
      nahradni.download = odkaz.download;
      document.body.appendChild(nahradni);
      nahradni.click();
      nahradni.remove();
    });
  });

  cislo.insertAdjacentElement('afterend', rada);
  cislo.insertAdjacentElement('afterend', odkaz);
})();
