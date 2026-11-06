# Export definic Secure Custom Fields

`bm-scf-all.json` obsahuje 7 definic datového modelu:

- typ obsahu **Králové** (`kral`)
- taxonomie **Ročník** (`rocnik`, hierarchická)
- 5 skupin polí — Králové, Ročník, barva u kategorie programu,
  bezbariérovost u místa, nastavení u akce

## Co tady NENÍ: Osvěžovny

Typ obsahu **Osvěžovna** a jeho pole v tomhle souboru nejsou a nebudou. Registrují
se kódem v [`wordpress/mu-plugins/bm-osvezovny/bm-osvezovny.php`](../mu-plugins/bm-osvezovny/bm-osvezovny.php),
takže se nahrají přes FTP spolu se zbytkem a nemůžou se rozejít s repozitářem.

Prakticky: **v SCF → Post Types ani SCF → Field Groups je nehledej, nejsou tam.**
Měnit se musí v tom PHP souboru.

Je to nejednotnost — Králové importem, Osvěžovny kódem. Proč to tak dopadlo a proč
by se to mělo jednou srovnat, je v [17-osvezovny.md](../../docs/wordpress/17-osvezovny.md).

## Proč Králové nejsou veřejný typ obsahu

Typ `kral` má `public: false` a `publicly_queryable: false` **schválně**. WordPress
jinak ke každému kandidátovi vyrobí vlastní adresu `/kral/<jméno>/` — a protože ten
typ nemá pole na text, je to prázdná stránka se jménem a fotkou. Na starém webu nic
takového nebylo (dvacet kotev na jedné stránce, nula odkazů) a nikdo o to nestojí;
na produkci by to znamenalo dvacet prázdných stránek se jmény studentů v Googlu.

Kandidáti se ukazují jen v žebříčku na `/kralove/`, který vykresluje doplněk
`bm-kralove`. Zapnout zpátky jde v **SCF → Post Types → Králové → Viditelnost**.

## Import

V administraci WordPressu: **SCF → Tools → Import Field Groups**,
nahrát tenhle soubor. Vytvoří se tím definice, **ne obsah** — místa, žánry,
stránky, termín ročníku a role se musí založit zvlášť.

Po importu zkontrolovat, že se objevily:
- **SCF → Post Types → Králové**
- **SCF → Taxonomies → Ročníky**
- **SCF → Field Groups** (5 skupin)

## Export

Z administrace: **SCF → Nástroje → Export**. Na té obrazovce jsou **tři** skupiny
zaškrtávátek — *Vybrat skupiny polí*, *Vybrat typy obsahu*, *Vybrat taxonomie*.
Zaškrtni všechny tři a stáhni jako JSON.

> **Past, na kterou soubor jednou doplatil.** Export, který pošle jen skupiny polí
> (`keys[]`) a vynechá zbylé dvě skupiny, projde bez chyby — jen vrátí skupiny
> **s prázdným `fields`**. Soubor pak vypadá správně, má sedm položek a správné
> názvy, ale datový model se z něj obnovit nedá: po importu vzniklo sedm prázdných
> skupin bez jediného pole. Objeveno 5. 11. 2026.
>
> **Jak poznat, že je export v pořádku:** každá skupina polí musí mít neprázdné
> `fields` a `Král – detail kandidáta` v něm repeater `body` s podpoli
> `segment` a `pocet`. Na tom stojí celý modul Králové.

```bash
python3 -c "
import json
for g in json.load(open('bm-scf-all.json')):
    f = g.get('fields')
    print(g['title'], '->', len(f) if isinstance(f, list) else 'typ/taxonomie')
"
```

Soubor je bez interních ID, takže reimport nic neduplikuje.

Popis celé kostry je v [docs/wordpress/05-kostra.md](../../docs/wordpress/05-kostra.md).
