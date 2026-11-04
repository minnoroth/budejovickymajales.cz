"""Nastaví celý vzhled v Customizeru Kadence — paleta, písmo, hlavička, patička.

Jeden běh přepíše vzhled do stavu, který je popsaný v docs/wordpress/08-vzhled.md.
Je to záchranná síť, ne denní nástroj: tým mění barvy a velikosti klikáním
v Customizeru, tenhle skript je tu pro případ, že se web staví znovu od nuly.

    export BM_WP=~/bm-wordpress/staging/wp.txt
    python3 nastaveni.py
"""

import json
import os

from pripojeni import customizer

# Paleta ročníku. Rebranding = přepsat hexy tady nebo v Customizeru,
# CSS se na ně odkazuje jen přes var(--global-paletteN).
PALETA = [
    ("#1EA198", "Zelenomodrá – hlavní"),
    ("#0C6F69", "Zelenomodrá tmavá – odkazy při najetí"),
    ("#121212", "Text – nejsilnější"),
    ("#2A2A2A", "Text – silný"),
    ("#4A4A4A", "Text – střední"),
    ("#8E8E8E", "Text – slabý a linky"),
    ("#F2F2F0", "Pozadí – světlé"),
    ("#FAFAF9", "Pozadí – nejsvětlejší"),
    ("#FFFFFF", "Bílá"),
    ("#EE7270", "Korálová – doplňková"),
    ("#13612e", "Úspěch"),
    ("#1159af", "Informace"),
    ("#b82105", "Chyba"),
    ("#f7630c", "Upozornění"),
    ("#F8E862", "Žlutá – akcent"),
]

barvy = [{"color": c, "slug": f"palette{i}", "name": n} for i, (c, n) in enumerate(PALETA, 1)]


def pismo(desktop, tablet=None, mobile=None, vyska=1.2, vaha="800", barva="palette3"):
    """Hodnota pro typografické ovládání Kadence."""
    return {
        "size": {"desktop": desktop, "tablet": tablet or desktop, "mobile": mobile or desktop},
        "sizeType": "px",
        "lineHeight": {"desktop": vyska},
        "lineType": "",
        "family": "inherit",
        "google": False,
        "weight": vaha,
        "variant": vaha,
        "color": barva,
    }


NASTAVENI = {
    # ── Barvy ────────────────────────────────────────────────────────
    "kadence_global_palette": json.dumps(
        {"palette": barvy, "second-palette": barvy, "third-palette": barvy, "active": "palette"},
        ensure_ascii=False),

    # ── Písmo ────────────────────────────────────────────────────────
    # Archivo z Google Fonts, hostované lokálně — bez požadavků na Google.
    "base_font": {"size": {"desktop": 17, "tablet": "", "mobile": ""}, "sizeType": "px",
                  "lineHeight": {"desktop": 1.6, "tablet": "", "mobile": ""}, "lineType": "",
                  "family": "Archivo", "google": True, "weight": "400", "variant": "regular",
                  "color": "palette5"},
    "load_fonts_local": True,
    "preload_fonts_local": True,
    "h1_font": pismo(64, 48, 38, 1.02),
    "h2_font": pismo(42, 34, 30, 1.1),
    "h3_font": pismo(22, 21, 20, 1.2),
    "h4_font": pismo(19, vyska=1.3, vaha="700"),

    # ── Hlavička ─────────────────────────────────────────────────────
    "header_main_background": {"desktop": {"color": "palette1"}},
    "header_main_height": {"size": {"mobile": "", "tablet": "", "desktop": 88},
                           "unit": {"mobile": "px", "tablet": "px", "desktop": "px"}},
    "logo_width": {"size": {"mobile": 36, "tablet": 42, "desktop": 46},
                   "unit": {"mobile": "px", "tablet": "px", "desktop": "px"}},
    # Znak nemá nápis, proto vedle něj stojí název webu — a to i na tabletu,
    # kde Kadence ve výchozím stavu nechává jen znak.
    "logo_layout": {"include": {"mobile": "logo_title", "tablet": "logo_title",
                                "desktop": "logo_title"},
                    "layout": {"mobile": "standard", "tablet": "standard",
                               "desktop": "standard"}},
    "brand_typography": {"size": {"desktop": 24, "tablet": 21, "mobile": 17},
                         "sizeType": "px", "lineHeight": {"desktop": 1.2},
                         "family": "inherit", "google": False, "weight": "700",
                         "variant": "700", "color": "palette9"},
    "primary_navigation_color": {"color": "palette9", "hover": "palette15", "active": "palette15"},
    "primary_navigation_typography": {"size": {"desktop": 16}, "lineHeight": {"desktop": ""},
                                      "family": "inherit", "google": False,
                                      "weight": "700", "variant": "700"},
    "mobile_trigger_color": {"color": "palette9", "hover": "palette15"},
    "header_popup_background": {"desktop": {"color": "palette3"}},
    "header_popup_close_color": {"color": "palette9", "hover": "palette15"},
    "mobile_navigation_color": {"color": "palette9", "hover": "palette15", "active": "palette15"},

    # Žluté tlačítko na e-shop vpravo v hlavičce i v mobilním panelu.
    "header_desktop_items": {
        "top": {"top_left": [], "top_left_center": [], "top_center": [],
                "top_right_center": [], "top_right": []},
        "main": {"main_left": ["logo"], "main_left_center": [], "main_center": [],
                 "main_right_center": [], "main_right": ["navigation", "button"]},
        "bottom": {"bottom_left": [], "bottom_left_center": [], "bottom_center": [],
                   "bottom_right_center": [], "bottom_right": []}},
    "header_mobile_items": {
        "popup": {"popup_content": ["mobile-navigation", "mobile-button"]},
        "top": {"top_left": [], "top_center": [], "top_right": []},
        "main": {"main_left": ["mobile-logo"], "main_center": [], "main_right": ["popup-toggle"]},
        "bottom": {"bottom_left": [], "bottom_center": [], "bottom_right": []}},
    "header_button_label": "E-shop",
    "header_button_link": "https://shop.budejovickymajales.cz/",
    "header_button_target": "_blank",
    "header_button_style": "filled",
    "header_button_background": {"color": "palette15", "hover": "palette9"},
    "header_button_color": {"color": "palette3", "hover": "palette3"},
    "header_button_radius": {"size": [999, 999, 999, 999], "unit": "px", "locked": True},
    "header_button_typography": {"size": {"desktop": 15}, "lineHeight": {"desktop": ""},
                                 "family": "inherit", "google": False,
                                 "weight": "800", "variant": "800"},
    "mobile_button_label": "E-shop",
    "mobile_button_link": "https://shop.budejovickymajales.cz/",
    "mobile_button_target": "_blank",
    "mobile_button_background": {"color": "palette15", "hover": "palette9"},
    "mobile_button_color": {"color": "palette3", "hover": "palette3"},
    "mobile_button_radius": {"size": [999, 999, 999, 999], "unit": "px", "locked": True},

    # ── Titulkový pás stránek a příspěvků ────────────────────────────
    # Úvod a Program ho mají vypnutý v nastavení konkrétní stránky,
    # protože si vlastní pás kreslí samy v obsahu.
    "page_title": True,
    "page_title_layout": "above",
    "page_title_elements": ["title"],
    "page_title_align": {"mobile": "left", "tablet": "left", "desktop": "left"},
    "page_title_height": {"size": {"mobile": "", "tablet": "", "desktop": ""},
                          "unit": {"mobile": "px", "tablet": "px", "desktop": "px"}},
    "page_title_background": {"desktop": {"color": "palette1"}},
    "page_title_font": pismo(48, 40, 34, 1.08, barva="palette9"),
    # Bez "above" Kadence pás nevykreslí a bílý titulek skončí na bílém pozadí.
    "post_title_layout": "above",
    "post_title_align": {"mobile": "left", "tablet": "left", "desktop": "left"},
    "post_title_elements": ["categories", "title", "meta"],
    "post_title_background": {"desktop": {"color": "palette1"}},
    "post_title_font": pismo(44, 36, 32, 1.1, barva="palette9"),
    "post_title_meta_color": {"color": "palette9", "hover": "palette15"},
    "post_title_category_color": {"color": "palette15", "hover": "palette9"},
    "post_title_height": {"size": {"mobile": "", "tablet": "", "desktop": ""},
                          "unit": {"mobile": "px", "tablet": "px", "desktop": "px"}},

    # ── Patička ──────────────────────────────────────────────────────
    "footer_wrap_background": {"desktop": {"color": "palette3"}},
    # Text patičky i logo hlavičky nastavuje logo.py — potřebuje adresu
    # nahrané přílohy, která je na každé instalaci jiná.
    "footer_html_typography": {"size": {"desktop": 15}, "lineHeight": {"desktop": 1.6},
                               "family": "inherit", "google": False, "weight": "",
                               "variant": "", "color": "palette8"},
    "footer_html_link_color": {"color": "palette9", "hover": "palette15"},
}


if __name__ == "__main__":
    css = open(os.path.join(os.path.dirname(os.path.abspath(__file__)), "bm.css"),
               encoding="utf-8").read()
    NASTAVENI["custom_css[kadence]"] = css
    kod, odpoved = customizer(NASTAVENI)
    platnost = (odpoved.get("data") or {}).get("setting_validities", {}) if isinstance(odpoved, dict) else {}
    spatne = [k for k, v in platnost.items() if v is not True]
    print("HTTP", kod, "| uloženo", len(platnost), "nastavení")
    print("odmítnuto:", spatne or "nic")
