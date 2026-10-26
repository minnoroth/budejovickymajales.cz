"""Společné napojení na WordPress — přihlašovací údaje, REST a Customizer.

Údaje se nikdy nedávají do repozitáře. Skripty čekají cestu k souboru
v proměnné prostředí ``BM_WP``:

    export BM_WP=~/bm-wordpress/staging/wp.txt
    python3 uvod.py

Soubor má tvar ``klic = hodnota`` na řádek a potřebuje:

    web      = https://novy.budejovickymajales.cz
    user     = přihlašovací jméno správce
    pass     = aplikacni heslo z Uzivatele -> Profil -> Hesla aplikaci
    adminpass = heslo do administrace (jen pro Customizer, REST ho nepotrebuje)
"""

import json
import os
import re
import subprocess

COOKIES = "/tmp/bm-wp-cookies.txt"


def nacti():
    cesta = os.environ.get("BM_WP")
    if not cesta:
        raise SystemExit("Chybí proměnná BM_WP s cestou k souboru s přihlašovacími údaji.")
    cfg = {}
    with open(os.path.expanduser(cesta), encoding="utf-8") as fh:
        for radek in fh:
            radek = radek.strip()
            if "=" in radek and not radek.startswith("#"):
                k, v = radek.split("=", 1)
                cfg[k.strip()] = v.strip()
    cfg.setdefault("web", "https://novy.budejovickymajales.cz")
    return cfg


CFG = nacti()
WEB = CFG["web"].rstrip("/")
AUTH = f"{CFG['user']}:{CFG['pass']}"


def rest(cesta, metoda="GET", telo=None):
    """Zavolá REST API WordPressu pod aplikačním heslem."""
    args = ["curl", "-s", "-u", AUTH, "--max-time", "90", "-X", metoda,
            f"{WEB}/wp-json/wp/v2/{cesta}"]
    if telo is not None:
        args += ["-H", "Content-Type: application/json",
                 "-d", json.dumps(telo, ensure_ascii=False)]
    vystup = subprocess.run(args, capture_output=True, text=True).stdout
    try:
        return json.loads(vystup)
    except ValueError:
        return {"chyba": vystup[:300]}


def _curl(args):
    r = subprocess.run(["curl", "-s", "-b", COOKIES, "-c", COOKIES, "--max-time", "90"] + args,
                       capture_output=True, text=True)
    return r.stdout


def prihlas():
    """Customizer jede na cookies, ne na aplikačním hesle — musíme se přihlásit."""
    if "adminpass" not in CFG:
        raise SystemExit("Pro zápis do Customizeru chybí v souboru klíč 'adminpass'.")
    _curl(["-o", "/dev/null", "-X", "POST", f"{WEB}/wp-login.php",
           "--data-urlencode", f"log={CFG['user']}",
           "--data-urlencode", f"pwd={CFG['adminpass']}",
           "--data-urlencode", "rememberme=forever",
           "--data-urlencode", "wp-submit=Přihlásit se"])


def _kontext():
    _curl([f"{WEB}/wp-admin/customize.php", "-o", "/tmp/bm-customize.html"])
    s = open("/tmp/bm-customize.html", encoding="utf-8", errors="replace").read()
    m = re.search(r"_wpCustomizeSettings\s*=\s*(\{.*?\});\s*\n", s, re.S)
    if not m:
        raise SystemExit("Nepodařilo se otevřít Customizer — nejspíš chybí přihlášení.")
    cfg = json.loads(m.group(1))
    return cfg["nonce"]["save"], cfg["changeset"]["uuid"]


def customizer(nastaveni):
    """Uloží mapu {id_nastaveni: hodnota} do Customizeru a rovnou publikuje."""
    prihlas()
    nonce, uuid = _kontext()
    args = ["-o", "/tmp/bm-customize-save.json", "-w", "%{http_code}", "-X", "POST",
            f"{WEB}/wp-admin/admin-ajax.php",
            "--data-urlencode", "action=customize_save",
            "--data-urlencode", "wp_customize=on",
            "--data-urlencode", "customize_theme=kadence",
            "--data-urlencode", f"nonce={nonce}",
            "--data-urlencode", f"customize_changeset_uuid={uuid}",
            "--data-urlencode", "customize_changeset_status=publish",
            # Plochá mapa, ne {"value": …} — jinak Kadence spadne na wp_kses(Array).
            "--data-urlencode", "customized=" + json.dumps(nastaveni, ensure_ascii=False)]
    kod = _curl(args)
    try:
        return kod, json.load(open("/tmp/bm-customize-save.json", encoding="utf-8"))
    except ValueError:
        return kod, open("/tmp/bm-customize-save.json", encoding="utf-8", errors="replace").read()[:200]
