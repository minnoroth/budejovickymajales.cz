/**
 * Tečky pod carouselem.
 *
 * Proč vůbec JavaScript, když je zbytek doplňku na serveru:
 * přepínání stránek tečkami je interakce a ta se bez kódu v prohlížeči
 * neudělá. Je to ale jen **vylepšení**: bez JavaScriptu zůstane pruh
 * posuvný prstem i kolečkem, tak jak ho vykreslí PHP. Nic se neztratí,
 * jen zmizí tečky.
 *
 * Proč se nepočítá s pevným počtem kartiček na stránku:
 * kolik se jich vejde, ví jedině prohlížeč — mění to šířka okna i velikost
 * písma, kterou si návštěvník může zvětšit. Počet se proto odvozuje
 * ze skutečné šířky kartičky a pruhu, ne z čísla v kódu.
 */
(function () {
	'use strict';

	var pruh = document.querySelector('.bmc');
	if (!pruh) {
		return;
	}

	var karty = Array.prototype.slice.call(pruh.querySelectorAll('.bmc-karta'));
	if (karty.length < 2) {
		return;
	}

	var tecky = document.createElement('div');
	tecky.className = 'bmc-tecky';
	tecky.setAttribute('role', 'tablist');
	tecky.setAttribute('aria-label', 'Stránky zvýrazněných akcí');
	pruh.parentNode.insertBefore(tecky, pruh.nextSibling);

	var stran = 0;

	/** Kolik kartiček je vedle sebe. Zaokrouhluje se, protože mezera mezi
	 *  nimi dělá z podílu nepěkné číslo. */
	function naStranku() {
		var sirkaKarty = karty[0].getBoundingClientRect().width;
		if (!sirkaKarty) {
			return 1;
		}

		return Math.max(1, Math.round(pruh.clientWidth / sirkaKarty));
	}

	function prepni(index) {
		var cil = karty[index * naStranku()];
		if (cil) {
			pruh.scrollTo({ left: cil.offsetLeft - pruh.offsetLeft, behavior: 'smooth' });
		}
	}

	function oznacAktivni() {
		var sirka = pruh.clientWidth || 1;
		var ktera = Math.round(pruh.scrollLeft / sirka);

		Array.prototype.forEach.call(tecky.children, function (tecka, i) {
			var aktivni = i === ktera;
			tecka.setAttribute('aria-selected', aktivni ? 'true' : 'false');
			tecka.tabIndex = aktivni ? 0 : -1;
		});
	}

	function postavTecky() {
		var novy = Math.ceil(karty.length / naStranku());

		// Jedna stránka znamená, že se všechno vešlo — tečky by nic nedělaly.
		if (novy === stran) {
			oznacAktivni();
			return;
		}

		stran = novy;
		tecky.textContent = '';
		tecky.hidden = stran < 2;

		for (var i = 0; i < stran; i++) {
			var tecka = document.createElement('button');
			tecka.type = 'button';
			tecka.className = 'bmc-tecka';
			tecka.setAttribute('role', 'tab');
			tecka.setAttribute('aria-label', 'Stránka ' + (i + 1) + ' z ' + stran);
			tecka.addEventListener('click', prepni.bind(null, i));
			tecky.appendChild(tecka);
		}

		oznacAktivni();
	}

	var cekam;
	function sPauzou(co) {
		return function () {
			window.clearTimeout(cekam);
			cekam = window.setTimeout(co, 100);
		};
	}

	pruh.addEventListener('scroll', sPauzou(oznacAktivni), { passive: true });
	window.addEventListener('resize', sPauzou(postavTecky));
	postavTecky();
}());
