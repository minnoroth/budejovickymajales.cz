"""Ořízne průhledný okraj loga a uloží variantu pro hlavičku a patičku.

Tiskové logo má kolem znaku široký průhledný rám. V hlavičce se kvůli němu
znak zmenší na polovinu výšky řádku, proto se před nahráním do médií ořízne.

    python3 oriznout-logo.py          # zapíše do ../branding/
"""

import os

from PIL import Image

SLOZKA = os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "branding")

for nazev in ("bile", "cerne"):
    im = Image.open(os.path.join(SLOZKA, f"logo-{nazev}.png")).convert("RGBA")
    orez = im.crop(im.split()[3].getbbox())
    # 320 px výšky pokryje i retina displeje, v hlavičce je znak ~56 px vysoký.
    sirka = round(orez.width * 320 / orez.height)
    maly = orez.resize((sirka, 320), Image.LANCZOS)
    maly.save(os.path.join(SLOZKA, f"majales-znak-{nazev}.png"), optimize=True)
    print(nazev, im.size, "→", maly.size)
