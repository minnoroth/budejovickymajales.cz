# Nasazení na staging — co se dělalo a kde jsou pasti

Provedeno 22. 10. 2026. Nový WordPress běží na `novy.budejovickymajales.cz`,
statický archiv starého webu na `2026.budejovickymajales.cz`, původní Nette web
jede dál na hlavní doméně.

Tenhle dokument je **záznam skutečného postupu**, ne plán. Většina času padla
na pasti popsané níže — kdo to bude opakovat, ušetří díky nim den.

## Výsledný stav

| Adresa | Co to je | Technologie |
|---|---|---|
| `budejovickymajales.cz` | původní web | Nette, PHP 8.3 |
| `2026.budejovickymajales.cz` | archiv ročníku 2026 | statické HTML, žádné PHP |
| `novy.budejovickymajales.cz` | nový web | WordPress 7.1.3 |

Na novém webu: 22 stránek, 27 míst, 51 žánrů a kategorií, datový model
(typ Králové, taxonomie Ročník, pole u míst a akcí), role Dramaturgie,
šest pluginů. Indexování vyhledávači vypnuté.

## Postup

1. **Subdomény k certifikátu** — Hostingové služby → Webhosting → HTTPS →
   *nastavení domén certifikátu*. Přidat `novy` a `2026`, pak **aplikovat změny**
   (samotné přidání nic neudělá). Trvá řádově desítky minut.
2. **PHP na 8.3** — Konfigurace PHP. Projeví se do 30 minut.
3. **FTP účet a databáze** — na přehledu webhostingu, panely vpravo.
4. **Složka subdomény** — `www/subdom/<nazev>/` přes FTP.
5. **Jádro WordPressu** — stáhnout z `cs.wordpress.org`, nahrát přes FTP
   (`upload.py` ve `wordpress/nasazeni/`).
6. **`wp-config.php`** — vygenerovat s čerstvými klíči z
   `api.wordpress.org/secret-key/1.1/salt/`.
7. **`.htaccess`** — viz past č. 4.
8. **Import databáze** — phpMyAdmin na `pma.wedos.net`, soubor `.sql.gz`
   se nahrává rovnou zabalený.
9. **Pluginy** — doinstalovat z administrace, server si je stáhne sám (past č. 6).

## Pasti

### 1. Verze PHP je globální pro celý webhosting

Nejde nastavit zvlášť pro subdoménu ani složku. Zvednutí PHP kvůli WordPressu
se dotkne i starého webu. Proto vznikl statický archiv — ten PHP nepotřebuje
a spor tím zaniká. (Nette aplikace z roku 2014 nakonec PHP 8.3 bez problémů
ustála, ale vědět se to předem nedalo.)

### 2. Instalátor aplikací od Wedosu je slepá ulička

Nabízí **WordPress 6.4.2** z prosince 2023. Novější databázi do něj
naimportovat nejde. A chytá se do vlastní pasti: vyžaduje, aby byla subdoména
dostupná přes HTTP(S) — k čemuž potřebuje existující složku s obsahem — ale
zároveň odmítne instalovat do neprázdného adresáře.

**Řešení:** nahrát jádro ručně přes FTP. Je to pomalejší, ale dostaneš rovnou
aktuální verzi a odpadne následná aktualizace.

### 3. FTP vyžaduje znovupoužití TLS session

vsftpd na Wedosu běží s `require_ssl_reuse`, takže datové spojení musí navázat
na TLS session řídicího spojení. Python `ftplib` to sám neumí a vrací:

```
522 SSL connection failed; session reuse required
```

Řeší to podtřída `FTPTLS` v `upload.py`. Port 22 je zavřený, SFTP ani SSH
k dispozici nejsou — tedy ani WP-CLI na serveru.

### 4. Bez `.htaccess` fungují jen homepage a přihlášení

Všechno ostatní vrací 404, včetně REST API. Potřebná pravidla jsou tři:

- standardní přepisovací blok WordPressu
- `SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1` — bez toho Apache
  nepustí hlavičku a REST API odmítá přihlášení
- přesměrování z `/subdom/<nazev>/` na kořen — viz past č. 5

### 5. Cesta `/subdom/<nazev>/` vede na stejné soubory a rozbije přihlášení

Vnitřní cesta na disku je zvenčí dostupná. Kdo na ni omylem zabloudí, projde
přihlášením, ale cookie se uloží pro `/wp-admin` a na `/subdom/novy/wp-admin/`
se neposílá — WordPress ho vrátí na přihlašovací stránku s `reauth=1`
a vznikne nekonečná smyčka. Vypadá to jako špatné heslo, ale heslo je správné.

Řeší to trvalé přesměrování 301 v `.htaccess`.

### 6. Pluginy neinstaluj přes FTP

161 MB souborů je zbytečné přenášet. Administrace WordPressu si je stáhne
server-side z katalogu během vteřin. Automatizovat to jde voláním
`wp-admin/update.php?action=install-plugin&plugin=<slug>&_wpnonce=<nonce>`;
nonce se vytáhne ze stránky `plugin-install.php?s=<slug>&tab=search&type=term`.
Pozor, odkazy používají entitu `&#038;`, ne `&amp;`.

Po importu databáze jsou pluginy vedené jako aktivní, ale WordPress je při
chybějících souborech deaktivuje a zapamatuje si to. Po doinstalování je tedy
nutné **znovu je aktivovat**.

### 7. MySQL není dostupná zvenčí

Port 3306 je zavřený, import na dálku nejde. Zbývá phpMyAdmin na
`pma.wedos.net` — běží na staré verzi 3.5.8.2, ale SQL jen předává serveru,
takže moderní dumpu nevadí.

## Čemu se vyhnout

Nenahrávat na veřejný server pomocné PHP skripty pro import databáze ani
podobné jednorázové nástroje. Pár ušetřených minut nestojí za to mít na
veřejné adrese něco, co sahá do databáze. Import přes phpMyAdmin je pět kliknutí.
