"""Postaví hlavní menu. Je možné ho spustit opakovaně — menu vždy přestaví celé.

Struktura vychází z rozhodnutí ze 4. a 5. 11. 2026, zapsaných
v docs/wordpress/13-struktura-a-program.md:

- Osvěžovny patří do hlavní úrovně. Pod Partnery byly nesmysl — je to
  informace pro návštěvníka, ne pro sponzora.
- Partneři zůstávají jako stránka, ale z hlavní úrovně mizí. Loga partnerů
  bývají součástí smlouvy o partnerství, takže stránka zmizet nesmí; v menu
  ale zabírala místo, které patří návštěvníkovi.
- Praktické info nemá podstránky: čtyři se slučují do jedné stránky s kotvami
  (prakticke-info.py). Rozbalovátko se čtyřmi položkami bylo na mobilu to,
  co na menu vadilo nejvíc.
- Aktuality jdou pod O festivalu.

Změna z 6. 11. 2026 (zadavatel):

- Pořadí hlavní úrovně je Program, Králové, Osvěžovny — králové jsou to,
  čím se festival liší od ostatních, osvěžovny jsou servis.
- Položka „O festivalu" nikam nevede, jen rozbaluje seznam pod sebou.
  V seznamu má místo čísla stránky None a skript z ní udělá vlastní odkaz
  s adresou „#"; href z něj sundá doplněk bm-menu-rozbalovatko.php.
  Dokud to byl odkaz, nešlo poznat, jestli se klepnutím rozbaluje, nebo
  někam odchází.
- Praktické info se stěhuje pod O festivalu, a to jako první položka.
  Dokument 13-struktura-a-program.md to nedoporučoval (je to informace pro
  návštěvníka, kdežto O festivalu je o organizaci); zadavatel na přesunu
  trvá, hlavní úroveň má být krátká. První místo v seznamu je kompromis,
  aby se nezahrabalo mezi osm položek o organizaci.
- Program nemá podstránky: zkratka [bm_program] drží Lineup i Seznam na jedné
  stránce. Stránky /program/lineup/ a /program/prehled/ jsou v koši,
  znovu je sem nepřidávat.

Zbylo jedno rozbalovátko místo šesti a čtyři položky v hlavní úrovni
místo devatenácti.
"""
from pripojeni import rest

NAZEV = "Hlavní menu"

# (název, číslo stránky, podpoložky). None místo čísla stránky znamená
# položku, která nikam nevede a jen rozbaluje seznam pod sebou.
STROM = [
    ("Program", 83, []),
    ("Králové", 85, []),
    ("Osvěžovny", 90, []),
    ("O festivalu", None, [
        # Praktické info je pro návštěvníka, ne o organizaci — proto stojí
        # nad vším ostatním, oddělené od zbytku seznamu.
        ("Praktické info", 86),
        ("O nás", 97),
        ("Historie", 99),
        # Aktuality jsou tu nejnavštěvovanější položka ze zbytku.
        ("Aktuality", 114),
        ("Dobrovolníci", 98),
        ("Fotogalerie", 100),
        ("Pro média", 102),
        ("Partneři", 84),
        ("Kontakt", 101),
    ]),
]


def najdi_menu():
    """Existující menu, nebo None. Hledá podle názvu, ne podle pozice —
    pozice se dá v Customizeru odpojit a skript by pak založil druhé menu."""
    for m in rest("menus?per_page=100", "GET"):
        if (m.get("name") or "").strip() == NAZEV:
            return m
    return None


def vyprazdni(mid):
    """Smaže položky menu. Bez toho by se při druhém spuštění zdvojily."""
    polozky = rest("menu-items?menus=%d&per_page=100" % mid, "GET")
    for p in polozky:
        rest("menu-items/%d?force=true" % p["id"], "DELETE")
    return len(polozky)


menu = najdi_menu()
if menu:
    print("menu:", menu["id"], NAZEV, "— smazáno starých položek:", vyprazdni(menu["id"]))
    # Pozici nastavit i u existujícího menu: kdyby se někdy odpojilo,
    # skript ho vrátí zpátky, aniž by se muselo klikat.
    rest("menus/%d" % menu["id"], "POST", {"locations": ["primary"]})
else:
    menu = rest("menus", "POST", {"name": NAZEV, "locations": ["primary"]})
    print("menu založeno:", menu.get("id"), menu.get("name"))

def cil(page_id):
    """Kam položka vede. None = nikam, jen rozbaluje.

    Prázdnou adresu REST u vlastního odkazu odmítne, proto „#" — stejnou
    adresu by tam zadal i člověk ve Vzhled → Menu. Na webu z ní href sundá
    doplněk bm-menu-rozbalovatko.php, takže z položky není odkaz."""
    if page_id is None:
        return {"type": "custom", "url": "#"}
    return {"type": "post_type", "object": "page", "object_id": page_id}


mid = menu["id"]
poradi = 0

for nazev, page_id, deti in STROM:
    poradi += 1
    telo = {"title": nazev, "menus": mid, "status": "publish", "menu_order": poradi}
    telo.update(cil(page_id))
    rodic = rest("menu-items", "POST", telo)
    print(" ", nazev, "→", rodic.get("id"))
    for d_nazev, d_id in deti:
        poradi += 1
        telo = {"title": d_nazev, "menus": mid, "status": "publish",
                "parent": rodic.get("id"), "menu_order": poradi}
        telo.update(cil(d_id))
        d = rest("menu-items", "POST", telo)
        print("     ", d_nazev, "→", d.get("id"))
