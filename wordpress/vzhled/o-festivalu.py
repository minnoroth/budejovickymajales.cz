"""Zruší rozcestník O festivalu a dá na jeho místo obsah stránky O nás.

Proč (rozhodnuto 6. 11. 2026):
Stránka /o-festivalu/ byla prázdný rozcestník — jedna věta „Rozcestník
informací o festivalu“ a pod tím „Úvodní text dodá zadavatel“. Položka menu
na ni vedla, takže kliknutí na „O festivalu“ skončilo na stránce, která nic
neříká a jen odkazuje dál. Skutečný text byl vedle, na podstránce O nás.

Co skript udělá:
- přenese obsah stránky O nás (97) na stránku O festivalu (87),
- stránku O nás schová do konceptů; nemaže ji, dá se vrátit jedním klikem.

Adresa zůstává /o-festivalu/, takže podstránky (Historie, Dobrovolníci…)
mají dál /o-festivalu/<nazev>/ a nic se nepřesouvá. Položka „O nás“ zároveň
mizí z menu, viz menu.py.

Je možné spustit opakovaně: když je stránka 97 už v konceptech a obsah sedí,
skript jen vypíše, že nemá co dělat.

    export BM_WP=~/bm-wordpress/staging/wp.txt
    python3 o-festivalu.py
"""
from pripojeni import rest

ROZCESTNIK = 87   # O festivalu
O_NAS = 97        # O nás


def raw(stranka):
    """Zdrojové HTML stránky. REST ho posílá jen v režimu úprav."""
    return ((stranka.get("content") or {}).get("raw") or "").strip()


zdroj = rest("pages/%d?context=edit" % O_NAS, "GET")
cil = rest("pages/%d?context=edit" % ROZCESTNIK, "GET")

if "id" not in zdroj or "id" not in cil:
    raise SystemExit("Nepodařilo se načíst stránky: %s %s" % (zdroj, cil))

text = raw(zdroj)

if not text:
    raise SystemExit("Stránka O nás nemá žádný obsah, nic nepřenáším.")

if raw(cil) == text:
    print("obsah už na stránce %d je" % ROZCESTNIK)
else:
    r = rest("pages/%d" % ROZCESTNIK, "POST", {"content": text, "title": "O festivalu"})
    print("O festivalu:", r.get("id"), (r.get("title") or {}).get("rendered"), r.get("status"))

if zdroj.get("status") == "draft":
    print("O nás už je v konceptech")
else:
    r = rest("pages/%d" % O_NAS, "POST", {"status": "draft"})
    print("O nás →", r.get("status"))
