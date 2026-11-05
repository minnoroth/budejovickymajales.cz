# Vzhled

Skripty, kterými se vzhled stagingu postaví od nuly, a zdrojové doplňkové CSS.
Proč je co takhle a kde se to mění klikáním popisuje
[`docs/wordpress/08-vzhled.md`](../../docs/wordpress/08-vzhled.md).

| Soubor | Co dělá |
|---|---|
| `bm.css` | doplňkové CSS — **zdroj pravdy**, kopie žije v Customizeru |
| `nastaveni.py` | paleta, písmo, hlavička, patička, titulkové pásy, nahraje `bm.css` |
| `logo.py` | nahraje znaky do médií, nastaví logo hlavičky a text patičky |
| `menu.py` | postaví hlavní menu (jde spustit opakovaně, menu vždy přestaví) |
| `stranky.py` | srovná zanoření stránek, a tím jejich adresy |
| `prakticke-info.py` | složí stránku Praktické info ze čtyř původních podstránek |
| `bloky.py` | společné bloky stránek (nadpis podstránky, obal obsahu) |
| `uvod.py` | obsah úvodní stránky |
| `program.py` | obsah stránky Program |
| `kralove.py` | obsah stránky Králové |
| `oriznout-logo.py` | ořízne průhledný rám loga pro hlavičku (potřebuje Pillow) |
| `pripojeni.py` | společné přihlášení, REST a zápis do Customizeru |

```bash
export BM_WP=~/bm-wordpress/staging/wp.txt
cd wordpress/vzhled
python3 nastaveni.py
python3 logo.py
```

Přihlašovací údaje v repozitáři nejsou a nebudou — repozitář je veřejný.
Soubor, na který `BM_WP` ukazuje, má tvar `klíč = hodnota` a potřebuje
`web`, `user`, `pass` (aplikační heslo) a `adminpass`.
