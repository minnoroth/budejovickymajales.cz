# Statický archiv starého webu

`mirror.py` stáhne `budejovickymajales.cz` jako statický adresářový strom,
který se nahraje do kořene `2026.budejovickymajales.cz` a obslouží ho Apache
**bez PHP a bez databáze**.

```bash
python3 mirror.py ./archiv-2026
```

## Proč to existuje

Verze PHP je na Wedosu **globální pro celý webhosting** — nejde nastavit zvlášť
pro subdoménu. Nový WordPress potřebuje PHP 8.x, starý web běžel na 7.3 a je to
Nette aplikace z roku 2014, u které není jisté, že PHP 8 přežije.

Statický archiv ten spor ruší: zmrazený web žádné PHP nepotřebuje, takže se verze
může zvednout bez ohledu na to, co by Nette udělalo. Stejný postup už spolek používá
pro `2019.budejovickymajales.cz`.

## Co archiv obsahuje

492 souborů, 136 HTML stránek včetně všech 102 detailů akcí ročníku 2026,
310 obrázků a 32 fontů GT Walsheim. Ověřeno lokálním serverem — homepage, program,
lineup, králové, partneři, osvěžovny, historie, FAQ, kontakty i GDPR vracejí 200
a načítají CSS i fonty.

Velikost 560 MB, z toho 416 MB je `uploads/files/Presskit.zip`. Bez něj 144 MB.

## Známá omezení

- **`/lineup` bude neúplný.** Je to aplikace v AngularJS 1.5.3, která si data
  dotahuje za běhu. Stránka se zobrazí, program v ní nenaběhne. Kompletní program
  je na `/program`.
- Stránky bez přípony se ukládají jako `program/689/index.html`, takže Apache je
  obslouží přes DirectoryIndex. Původní adresa bez lomítka se přesměruje na variantu
  s lomítkem.
- Mrtvé odkazy ze živého webu se nepřenesly: `/program/489` (404 i na originále)
  a odkaz na Facebook bez `https://`.

## Na co si dát pozor při úpravách skriptu

Soubory s parametrem v URL (`gt-walsheim-bold.woff2?v=72e2a9…`) se musí uložit pod
**holou cestou** bez parametru — statický server parametr ignoruje a hledá
`gt-walsheim-bold.woff2`. První verze skriptu tohle neuměla a uložila fonty jako
adresáře s binárkou přejmenovanou na `index.html`.

## Co se záměrně nestahuje

`uploads/files/Presskit.zip` (416 MB) — tiskové materiály k ročníku, který skončil.
Tvořily tři čtvrtiny archivu a upload přes FTP by se kvůli nim protáhl.
Skript je přeskakuje, archiv má bez nich **144 MB**.

Důsledek: odkaz na presskit na stránce `kontakty` povede do prázdna.
Soubor je k dispozici na původním webu, dokud běží.
