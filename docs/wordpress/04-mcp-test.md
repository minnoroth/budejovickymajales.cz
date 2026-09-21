# Test MCP napojení — výsledky

Provedeno 21. 9. 2026 na čisté lokální instalaci. Cílem bylo ověřit tvrzení z
[00-analyza.md](00-analyza.md), na kterých stojí odhad 8 hodin na plnění obsahu.

**Závěr: funguje to, ale číslo 179 schopností neplatí. Pro náš stack je jich 108.**

## Testovací prostředí

WordPress **7.1.1** (stejná verze jako na dikyzemuzem.cz) v Dockeru, MariaDB 11.
Nainstalované pluginy ve verzích, které odpovídají těm ověřeným v [03-overena-fakta.md](03-overena-fakta.md):

| Plugin | Verze |
|---|---|
| agent-abilities-for-mcp | 1.7.5 |
| the-events-calendar | 6.17.5 |
| event-tickets | 5.29.5 |
| secure-custom-fields | 6.9.5 |

Prostředí je v `~/Projects/personal/bm-wordpress-local`, spouští se `docker compose up -d`.

## Co se potvrdilo

**Abilities API je opravdu v jádře.** Na čisté instalaci WordPressu 7.1.1 bez jediného pluginu
existuje namespace `wp-abilities/v1` se šesti routami. Jádro registruje přesně tři schopnosti
a všechny jsou jen pro čtení:

```
core/get-site-info
core/get-user-info
core/get-environment-info
```

**Namespace `mcp` v jádře NENÍ.** Přidává ho až plugin. Na dikyzemuzem.cz je proto, že tam mají
oficiální MCP Adapter; plugin Agent Abilities používá vlastní namespace
`agent-abilities-for-mcp/mcp` plus OAuth routy (`register`, `token`, `revoke`).

**MCP handshake funguje.** Server odpovídá protokolem `2025-06-18`, vyžaduje `Mcp-Session-Id`
z hlavičky odpovědi na `initialize`.

**Zápis funguje end-to-end.** Přes MCP jsem vytvořil místo a akci:

```
aafm-tec-create-venue  → tribe_venue  id 5, "Piaristické náměstí"
aafm-tec-create-event  → tribe_events id 6, "Zahájení a představení králů"
```

Ověřeno nezávisle v databázi: `_EventStartDate = 2027-05-30 18:00:00`, `_EventVenueID = 5`.
Akce se korektně vykresluje na webu včetně vazby na místo. **České diakritice nic nechybí.**

**Bezpečnostní prvky fungují.** Po zapnutí `aafm_read_only_mode` zapisovací nástroje
z `tools/list` **úplně zmizí** — server vrací „Tool not found", ne jen chybu při volání.
To je silnější záruka, než jsem čekal. Audit log v tabulce `wp_aafm_activity_log` zaznamenal
každé volání včetně toho zablokovaného, s uživatelem, IP adresou, názvem schopnosti,
seznamem argumentů a časem.

## Co se nepotvrdilo

**179 schopností je marketingové číslo pro plnou instalaci.** Pro náš stack jich registr
obsahuje **108**. Zbytek do 179 tvoří integrace na pluginy, které nemáme a mít nebudeme —
hlavně WooCommerce (52 nástrojů) a tři SEO pluginy.

Rozpad těch 108 podle typu operace:

| Skupina | Počet |
|---|---|
| `get-*` (čtení jednotlivostí) | 28 |
| `tec-*` (The Events Calendar) | 16 |
| `update-*` | 15 |
| `delete-*` | 12 |
| `create-*` | 10 |
| `list-*` | 7 |
| `acf-*` (funguje i na Secure Custom Fields) | 7 |
| ostatní (count, trash, upload, search, restore…) | 13 |

Šestnáct nástrojů pro The Events Calendar pokrývá přesně to, co migrace potřebuje:

```
aafm-tec-create-event    aafm-tec-create-venue      aafm-tec-get-attendees
aafm-tec-update-event    aafm-tec-update-venue      aafm-tec-get-tickets
aafm-tec-delete-event    aafm-tec-get-venues        aafm-tec-create-organizer
```

**Bonus:** Secure Custom Fields registruje 48 vlastních schopností sám od sebe, bez MCP pluginu.
Potvrzuje to, že SCF je živý projekt, ne zamrzlý fork.

## Tři překážky, na které se narazí i na ostrém webu

**1. Aplikační hesla vyžadují HTTPS.** WordPress je přes plain HTTP vypíná
(`wp_is_application_passwords_available()` vrací `false`). Na Wedosu s SSL to problém není,
ale lokálně se to musí obejít mu-pluginem — a ten se **nikdy nesmí dostat na produkci**.

**2. Apache nepouští hlavičku `Authorization`.** Bez ní přijde `rest_not_logged_in`, i když
je heslo správně. Řeší se jedním řádkem v konfiguraci nebo v `.htaccess`:

```apache
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```

Tohle na sdíleném Wedosu pravděpodobně nastane taky — počítat s tím při nasazení.

**3. Ve výchozím stavu je vypnuté úplně všechno.** Čerstvě aktivovaný plugin vystaví přes MCP
**nula** nástrojů. Schopnosti se zapínají po jedné v administraci, případně hromadně zápisem do
option `aafm_enabled_abilities`. Je to správné chování, ale kdo to nečeká, stráví hodinu hledáním,
proč MCP „nefunguje".

## Co z toho plyne pro projekt

Odhad **8 hodin na plnění obsahu přes MCP platí** — nástroje na místa, akce, taxonomie i média
existují a ověřeně zapisují. Předpoklad v [00-analyza.md](00-analyza.md) se potvrdil.

Doporučený provozní režim po spuštění:

1. Zapnout **jen ty schopnosti, které jsou zrovna potřeba**, ne všech 108.
2. Po dokončení migrace přepnout na `aafm_read_only_mode = 1`, nebo plugin deaktivovat.
3. Agenta pouštět pod **vyhrazeným uživatelem s omezenou rolí**, ne pod administrátorem.
4. Audit log nechat zapnutý — je to jediný způsob, jak zpětně zjistit, co agent udělal.

A pořád platí to hlavní: **MCP není součást předání.** Příští sestava musí web spravovat
klikáním. Tohle je nástroj pro stavbu a pro hromadné operace, nic víc.
