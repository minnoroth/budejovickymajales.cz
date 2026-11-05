"""Srovná zanoření stránek. Je možné spustit opakovaně.

Zanoření určuje adresu: stránka pod Partnery má adresu /partneri/osvezovny/.
Osvěžovny i Podpoř nás pod Partnery historicky visely, ačkoli ani jedno
s partnery nesouvisí — Osvěžovny jsou informace pro návštěvníka a Podpoř nás
je výzva pro kohokoli. Od 5. 11. 2026 jsou obě v nejvyšší úrovni.

Adresa se tím mění. Dokud web není veřejný, nikomu to nevadí; až bude,
tenhle soubor je to místo, kde se pozná, že se měnila.
"""
from pripojeni import rest

# stránka → nadřazená stránka (0 = nejvyšší úroveň)
ZANORENI = {
    90: 0,   # Osvěžovny — bývalo pod Partnery
    91: 0,   # Podpoř nás! — bývalo pod Partnery
}

for pid, rodic in ZANORENI.items():
    p = rest("pages/%d" % pid, "GET")
    if int(p.get("parent") or 0) == rodic:
        print("  %-16s už sedí (%s)" % (p.get("slug"), p.get("link")))
        continue

    r = rest("pages/%d" % pid, "POST", {"parent": rodic})
    print("  %-16s → %s" % (r.get("slug"), r.get("link")))
