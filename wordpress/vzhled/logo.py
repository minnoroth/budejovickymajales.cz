"""Nahraje oříznutá loga do médií, nastaví logo hlavičky a znak v patičce.

Spouští se po `nastaveni.py` — patří do kroků stavby vzhledu od nuly, protože
ID přílohy a adresa souboru jsou na každé instalaci jiné.

    export BM_WP=~/bm-wordpress/staging/wp.txt
    python3 logo.py
"""

import json
import os
import subprocess

from pripojeni import AUTH, WEB, customizer

SLOZKA = os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "branding")
PATICKA = ('<img src="{url}" alt="" width="38" style="display:block;margin:0 auto 14px" />'
           "© {{year}} {{site-title}} · Nezisková organizace. "
           "Festival dělají studenti a dobrovolníci.")


def nahraj(soubor):
    vystup = subprocess.run([
        "curl", "-s", "-u", AUTH, "--max-time", "120", "-X", "POST",
        f"{WEB}/wp-json/wp/v2/media",
        "-H", f'Content-Disposition: attachment; filename="{soubor}"',
        "-H", "Content-Type: image/png",
        "--data-binary", f"@{os.path.join(SLOZKA, soubor)}",
    ], capture_output=True, text=True).stdout
    r = json.loads(vystup)
    if "id" not in r:
        raise SystemExit(f"Nahrání {soubor} selhalo: {vystup[:300]}")
    return r["id"], r["source_url"]


if __name__ == "__main__":
    bile_id, bile_url = nahraj("majales-znak-bile.png")
    nahraj("majales-znak-cerne.png")
    # Relativní adresa přežije i změnu domény (staging → ostrý web).
    cesta = bile_url.split(WEB, 1)[-1] if bile_url.startswith(WEB) else bile_url
    kod, odpoved = customizer({
        "custom_logo": str(bile_id),
        "footer_html_content": PATICKA.format(url=cesta),
    })
    print("logo hlavičky: příloha", bile_id, "|", cesta)
    print("HTTP", kod, "|", (odpoved.get("data") or {}).get("setting_validities")
          if isinstance(odpoved, dict) else odpoved)
