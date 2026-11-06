/**
 * Filtr nad výpisem osvěžoven.
 *
 * Proč vůbec JavaScript, když je zbytek doplňku na serveru:
 * přepínání je interakce a ta se bez kódu v prohlížeči neudělá. Je to ale
 * jen **vylepšení**: bez JavaScriptu zůstane vidět celá nabídka, tak jak ji
 * vykreslí PHP. Nic se neztratí, jen zmizí filtr.
 *
 * Proč filtr staví JavaScript, a ne PHP:
 * kdyby tlačítka vykreslilo PHP a skript se nenačetl, zůstala by na stránce
 * tlačítka, která nic nedělají. Mrtvé ovládání je horší než žádné.
 *
 * Proč se nabídka tlačítek počítá z položek:
 * druh je u položky nepovinný. Když letos nikdo nic neoznačí jako jídlo,
 * tlačítko „Jídlo" by filtrovalo do prázdna. Vyrobí se proto jen ta
 * tlačítka, pro která něco je.
 */
(function () {
	'use strict';

	var koren = document.querySelector('.bmo');
	if (!koren) {
		return;
	}

	var seznam = koren.querySelector('.bmo__seznam');
	if (!seznam) {
		return;
	}

	var polozky = Array.prototype.slice.call(koren.querySelectorAll('.bmo-polozka'));
	var karty = Array.prototype.slice.call(seznam.querySelectorAll('.bmo-karta'));

	// Popisky musí sedět na volby pole „Druh" v bm-osvezovny.php. Když tam
	// druh přibude, přidej ho i sem; do té doby se jeho položky ukážou jen
	// pod „Vše", takže se žádná neztratí.
	var DRUHY = [
		['nealko', 'Nealko'],
		['alko', 'Alko'],
		['jidlo', 'Jídlo']
	];

	var pritomne = DRUHY.filter(function (druh) {
		return polozky.some(function (polozka) {
			return polozka.getAttribute('data-druh') === druh[0];
		});
	});

	// Jeden druh (nebo žádný) znamená, že filtr nemá co rozdělovat.
	if (pritomne.length < 2) {
		return;
	}

	var filtr = document.createElement('ul');
	filtr.className = 'bmo__filtr';

	var tlacitka = [];

	function prepni(vybrany) {
		tlacitka.forEach(function (tlacitko) {
			tlacitko.setAttribute('aria-pressed', tlacitko.dataset.druh === vybrany ? 'true' : 'false');
		});

		polozky.forEach(function (polozka) {
			polozka.hidden = vybrany !== '' && polozka.getAttribute('data-druh') !== vybrany;
		});

		// Kartička bez jediné viditelné položky by zůstala jako prázdný
		// rámeček s adresou. Schová se celá.
		//
		// Prázdný výpis tu nehlídá nic schválně: tlačítko vzniká jen pro
		// druh, který aspoň jedna položka má, takže po kliknutí vždycky
		// něco zbude. Hláška „nic tu není" by byla kód, který nejde vyvolat.
		karty.forEach(function (karta) {
			karta.hidden = !Array.prototype.some.call(
				karta.querySelectorAll('.bmo-polozka'),
				function (polozka) { return !polozka.hidden; }
			);
		});

		// Nadpis skupiny nad schovanými položkami by visel ve vzduchu.
		// Při zapnutém filtru mizí všechny: nadpis „Jídlo" nad jídlem,
		// které si návštěvník sám vyfiltroval, neříká nic.
		Array.prototype.forEach.call(koren.querySelectorAll('.bmo-karta__skupina'), function (nadpis) {
			var dalsi = nadpis.nextElementSibling;
			var zbylo = dalsi && Array.prototype.some.call(
				dalsi.querySelectorAll('.bmo-polozka'),
				function (polozka) { return !polozka.hidden; }
			);
			nadpis.hidden = vybrany !== '' || !zbylo;
		});
	}

	function pridej(klic, popis) {
		var tlacitko = document.createElement('button');
		tlacitko.type = 'button';
		tlacitko.className = 'bmo__chip';
		tlacitko.dataset.druh = klic;
		tlacitko.textContent = popis;
		tlacitko.setAttribute('aria-pressed', klic === '' ? 'true' : 'false');
		tlacitko.addEventListener('click', function () { prepni(klic); });

		var obal = document.createElement('li');
		obal.appendChild(tlacitko);
		filtr.appendChild(obal);
		tlacitka.push(tlacitko);
	}

	pridej('', 'Vše');
	pritomne.forEach(function (druh) { pridej(druh[0], druh[1]); });

	koren.insertBefore(filtr, seznam);
}());
