# Export definic Secure Custom Fields

`bm-scf-all.json` obsahuje 7 definic datového modelu:

- typ obsahu **Králové** (`kral`)
- taxonomie **Ročník** (`rocnik`, hierarchická)
- 5 skupin polí — Králové, Ročník, barva u kategorie programu,
  bezbariérovost u místa, nastavení u akce

## Import

V administraci WordPressu: **SCF → Tools → Import Field Groups**,
nahrát tenhle soubor. Vytvoří se tím definice, **ne obsah** — místa, žánry,
stránky, termín ročníku a role se musí založit zvlášť.

Po importu zkontrolovat, že se objevily:
- **SCF → Post Types → Králové**
- **SCF → Taxonomies → Ročníky**
- **SCF → Field Groups** (5 skupin)

## Export

Vygenerováno z lokální instalace přes `acf_prepare_internal_post_type_for_export()`,
tedy stejnou cestou jako ruční export v administraci. Soubor je bez interních ID,
takže reimport nic neduplikuje.

Popis celé kostry je v [docs/wordpress/05-kostra.md](../../docs/wordpress/05-kostra.md).
