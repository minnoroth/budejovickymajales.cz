# Budějovický Majáles — poznámky pro agenty

## Co je tohle za projekt

Nezisková organizace, studentský festival v Českých Budějovicích. Repozitář drží **analýzu
a stavbu nového webu na WordPressu**, ne aplikaci. Přehled je v [README.md](README.md).

## Jediné kritérium, které rozhoduje spory

Web drží sestava lidí rok, nejvýš dva, a pak se předává dál. Kritérium není „udržovatelné
vývojářem", ale **převzatelné cizím člověkem za jeden večer**.

Když se rozhoduješ mezi elegantnějším řešením a tím, které příští sestava pochopí bez tebe,
ber to druhé. Platí to i pro dokumentaci v tomhle repu.

Prakticky to znamená:

- **Žádný vlastní kód nad rámec nutného.** Současný web je ukázka toho, co se stane,
  když se tohle deset let porušuje.
- **Žádné úpravy šablon pluginů** — rozbijí se při první aktualizaci.
- **Barvy nikdy natvrdo.** Vždy přes `var(--global-paletteN)` z palety Kadence.
- Když něco jde naklikat v Customizeru, patří to tam, ne do CSS.

## Techstack

WordPress + Kadence (téma) + The Events Calendar, Event Tickets, Secure Custom Fields,
Members. Všechno ve verzi zdarma. Hosting Wedos, sdílený, bez SSH a bez WP-CLI.

Dřívější verze na Next.js + Strapi je smazaná. Když na ni narazíš v historii gitu nebo
ve starších dokumentech, je to slepá větev — nestav na ní.

## Jazyk

- **Dokumentace, komentáře a obsah webu: česky.** Čte to studentský tým, ne vývojáři.
- Identifikátory v kódu a názvy commitů běžné anglické konvence snesou, ale popisné texty
  pro tým jsou česky.

## Přístupy

V repozitáři nejsou a nebudou — **je veřejný**. Skripty čtou přihlašovací údaje ze souboru,
na který ukazuje proměnná prostředí `BM_WP`. Stejně tak sem nepatří soubory fontů
s nejasnou licencí (`.gitignore` je blokuje).

## Než něco změníš na webu

Vzhled je reprodukovatelný skripty ve [wordpress/vzhled/](wordpress/vzhled/). Když změníš
CSS přímo v administraci, vrať tu změnu i do `wordpress/vzhled/bm.css` — jinak ji příští běh
`nastaveni.py` přepíše.
