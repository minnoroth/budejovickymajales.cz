"""Nastaví celý vzhled v Customizeru Kadence — paleta, písmo, hlavička, patička.

Jeden běh přepíše vzhled do stavu, který je popsaný v docs/wordpress/08-vzhled.md.
Je to záchranná síť, ne denní nástroj: tým mění barvy a velikosti klikáním
v Customizeru, tenhle skript je tu pro případ, že se web staví znovu od nuly.

    export BM_WP=~/bm-wordpress/staging/wp.txt
    python3 nastaveni.py
"""

import json
import os
import time

from pripojeni import customizer, WEB

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
    ("#B6B4DA", "Levandulová – doplňková"),
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
    # Papírová hlavička, ne zelená. Barvy položek menu (žlutá, korálová,
    # levandulová, zelenomodrá) potřebují tichý podklad — na zelené by se
    # zelenomodrý štítek ztratil. Rozhodnuto 6. 11. 2026,
    # viz docs/wordpress/18-hlavicka.md.
    "header_main_background": {"desktop": {"color": "palette7"}},
    "header_main_height": {"size": {"mobile": "", "tablet": "", "desktop": 88},
                           "unit": {"mobile": "px", "tablet": "px", "desktop": "px"}},
    # Šířka loga. Tým dodává logo na šířku, takže se omezuje VÝŠKOU v bm.css
    # (.site-branding .custom-logo) — u loga na šířku je výška to, co musí
    # sedět na hlavičku, a šířka se dopočítá. Tenhle rozměr je jen strop,
    # aby se ani nečekaně velký soubor nerozlezl.
    "logo_width": {"size": {"mobile": 180, "tablet": 200, "desktop": 220},
                   "unit": {"mobile": "px", "tablet": "px", "desktop": "px"}},
    # V hlavičce je jen logo, bez názvu webu. Logo si tým vždycky připraví
    # na šířku i s nápisem, takže by název stál vedle něj podruhé.
    # Jméno webu nemizí úplně: nese ho alternativní text obrázku, takže ho
    # přečte odečítač obrazovky i vyhledávač.
    "logo_layout": {"include": {"mobile": "logo", "tablet": "logo",
                                "desktop": "logo"},
                    "layout": {"mobile": "standard", "tablet": "standard",
                               "desktop": "standard"}},
    # Písmo menu je černé ve všech stavech — barvu nese výplň štítku,
    # ne text. Kdyby se při najetí měnila i barva písma, přestal by být
    # čitelný na žluté.
    "primary_navigation_color": {"color": "palette3", "hover": "palette3", "active": "palette3"},
    "primary_navigation_typography": {"size": {"desktop": 16}, "lineHeight": {"desktop": ""},
                                      "family": "inherit", "google": False,
                                      "weight": "800", "variant": "800"},
    "mobile_trigger_color": {"color": "palette3", "hover": "palette3"},
    # Mobilní panel je papírový ze stejného důvodu jako hlavička.
    "header_popup_background": {"desktop": {"color": "palette7"}},
    "header_popup_close_color": {"color": "palette3", "hover": "palette3"},
    "mobile_navigation_color": {"color": "palette3", "hover": "palette3", "active": "palette3"},

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
    # Pro neziskovku je „Podpoř nás" silnější výzva než e-shop. E-shop
    # z webu nemizí — je odkazem na té stránce, tedy o jeden klik dál.
    # Rozhodnuto 4. 11. 2026, viz docs/wordpress/13-struktura-a-program.md.
    # Stejné tlačítko jako „Podpořit →" v heru: korálová, stejný text.
    # Šipka v popisku být nemůže — Kadence ho prohání přes esc_html(),
    # takže by se vypsala jako text. Dolepuje ji CSS (.header-button::after).
    "header_button_label": "Podpořit",
    "header_button_link": "/podpor-nas/",
    # POZOR: tohle není řetězec, ale pravda/nepravda. Kadence píše
    # target="_blank", když je hodnota pravdivá — a „_self" je pravdivý
    # řetězec, takže se odkaz otevíral v novém okně. Odkaz vede na vlastní
    # web, tam nové okno nepatří.
    "header_button_target": False,
    "header_button_style": "filled",
    "header_button_background": {"color": "palette10", "hover": "palette9"},
    "header_button_color": {"color": "palette3", "hover": "palette3"},
    "header_button_radius": {"size": [999, 999, 999, 999], "unit": "px", "locked": True},
    "header_button_typography": {"size": {"desktop": 15}, "lineHeight": {"desktop": ""},
                                 "family": "inherit", "google": False,
                                 "weight": "800", "variant": "800"},
    "mobile_button_label": "Podpořit",
    "mobile_button_link": "/podpor-nas/",
    "mobile_button_target": False,
    "mobile_button_background": {"color": "palette10", "hover": "palette9"},
    "mobile_button_color": {"color": "palette3", "hover": "palette3"},
    "mobile_button_radius": {"size": [999, 999, 999, 999], "unit": "px", "locked": True},

    # ── Průhledná hlavička nad fotkou ────────────────────────────────────
    # Fotka na úvodu má jít až nahoru, pod menu — jinak nad ní visí zelený
    # pruh a vypadá to jako dvě nesouvisející věci nalepené na sebe.
    #
    # Zapnuto globálně, ale vypnuto na stránkách i článcích; jedinou
    # výjimkou je Úvod, který to má povolené svým vlastním nastavením
    # (meta _kad_post_transparent, nastavuje uvod.py). Jinde by průhledná
    # hlavička splynula s bílým obsahem a menu by zmizelo.
    "transparent_header_enable": True,
    "transparent_header_page": True,
    "transparent_header_post": True,
    "transparent_header_archive": True,
    # transparent_header_product se nenastavuje — je pro e-shop WooCommerce,
    # který tu není, a Customizer ho odmítá.
    # Menu a název webu zůstávají bílé jako na zelené hlavičce. Přes fotku
    # je drží čitelné tmavý závoj, který dosazuje doplněk bm-pozadi.php.
    "transparent_header_site_title_color": {"color": "palette9", "hover": "palette15"},
    # Nad fotkou mají štítky bílou výplň (bm.css), takže i tam je písmo
    # černé. Bílé písmo na bílém štítku by zmizelo.
    "transparent_header_navigation_color": {"color": "palette3", "hover": "palette3"},
    "transparent_header_button_color": {"color": "palette3", "hover": "palette3"},
    "transparent_header_background": {"desktop": {"color": "transparent"}},
    "transparent_header_bottom_border": {"desktop": {"width": 0, "unit": "px", "style": "none"}},

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


# Doplňkové CSS vzniká spojením těchto souborů v tomto pořadí.
# bm-akce.css navazuje na proměnné z bm.css, takže musí jít za ním.
CSS_SOUBORY = ("bm.css", "bm-akce.css")


def nacti_css():
    """Spojí zdrojové CSS soubory do jednoho bloku pro Customizer.

    Cestou zkontroluje, že komentáře jsou párové. Zbylé „*/“ navíc udělá
    z následujícího textu neplatný selektor, který spolkne pravidlo pod
    sebou — a prohlížeč to nikde nenahlásí. Stalo se to 5. 11. 2026
    a projevilo se to tím, že úvodní pás neměl odsazení jen na monitoru
    (pravidla v @media blocích přežila, to pod komentářem ne).
    """
    slozka = os.path.dirname(os.path.abspath(__file__))
    casti = []
    for jmeno in CSS_SOUBORY:
        obsah = open(os.path.join(slozka, jmeno), encoding="utf-8").read()
        zacatku, koncu = obsah.count("/*"), obsah.count("*/")
        if zacatku != koncu:
            raise SystemExit(
                "%s má %d× „/*“ a %d× „*/“ — nepárový komentář zruší pravidlo "
                "pod sebou. Oprav to, nic neukládám." % (jmeno, zacatku, koncu))
        casti.append(obsah)

    return "\n\n".join(casti)


def overit_css(ocekavano):
    """Zkontroluje, že se CSS opravdu propsalo na web.

    Customizer umí vrátit HTTP 200 a „uloženo", a přitom zůstat u starší
    verze CSS. Stalo se to 5. 11. 2026 a poznalo se to až tím, že na webu
    chybělo jedno pravidlo — hlášení skriptu přitom tvrdilo, že je hotovo.
    Od té doby se po uložení kontroluje, co web skutečně servíruje.
    """
    import html as _html
    import urllib.request

    try:
        with urllib.request.urlopen(WEB + "/?nocache=" + str(int(time.time())), timeout=30) as o:
            stranka = o.read().decode("utf-8", "replace")
    except Exception as chyba:                      # noqa: BLE001 — kontrola nesmí shodit skript
        print("CSS se nepodařilo ověřit:", chyba)
        return

    znacka = "Budějovický Majáles — doplňkové CSS"
    i = stranka.find(znacka)
    if i < 0:
        print("POZOR: doplňkové CSS na webu vůbec není.")
        return

    j = stranka.find("</style>", i)
    na_webu = _html.unescape(stranka[stranka.rfind(">", 0, i) + 1:j]).strip()

    if len(na_webu) == len(ocekavano.strip()):
        print("CSS ověřeno:", len(na_webu), "znaků na webu")
        return

    print("POZOR: CSS se neuložilo celé — posláno %d znaků, na webu je %d."
          % (len(ocekavano.strip()), len(na_webu)))
    print("       Spusť skript znovu a zkontroluj to; uložení občas tiše neprojde.")


if __name__ == "__main__":
    kod, odpoved = customizer(NASTAVENI)
    platnost = (odpoved.get("data") or {}).get("setting_validities", {}) if isinstance(odpoved, dict) else {}
    spatne = [k for k, v in platnost.items() if v is not True]
    print("HTTP", kod, "| uloženo", len(platnost), "nastavení")
    print("odmítnuto:", spatne or "nic")

    # CSS se posílá zvlášť, ne spolu se zbytkem nastavení. Když jede
    # v jednom požadavku s ostatními šedesáti, Customizer vrátí „uloženo"
    # a tiše si nechá předchozí verzi — ověřeno 5. 11. 2026 měřením:
    # samotné CSS projde i v šedesáti kilobajtech, dohromady s ostatními
    # neprojde vůbec. Příčina na straně hostingu není známá; tohle ji obchází.
    css = nacti_css()
    kod, _ = customizer({"custom_css[kadence]": css})
    print("CSS: HTTP", kod, "|", len(css), "znaků")
    overit_css(css)
