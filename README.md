# budejovickymajales.cz

Web Budějovického Majálesu — **analýza, dokumentace a stavba nového webu na WordPressu**.

Tenhle repozitář není aplikace. Je to podklad pro přechod ze starého webu (Nette + ručně
doprogramovaný lineup) na WordPress, a zároveň sada skriptů, kterými se nový web postaví.
Běžící web žije na hostingu, ne tady.

| | |
|---|---|
| Rozestavěný web | <https://novy.budejovickymajales.cz> |
| Starý web | <https://budejovickymajales.cz> |
| Pracovní větev | `docs/wordpress-migration` |

## Proč WordPress

Web drží sestava lidí rok, nejvýš dva, a pak se předává dál. Kritérium proto není
„udržovatelné vývojářem", ale **převzatelné cizím člověkem za jeden večer**.

Z toho plyne všechno ostatní: žádný vlastní framework, čtyři pluginy zdarma, vzhled se mění
klikáním v Customizeru a barvy se nikde nepíšou natvrdo. Podrobné zdůvodnění je
v [docs/wordpress/00-analyza.md](docs/wordpress/00-analyza.md).

## Kudy do toho

| Soubor | O čem je |
|---|---|
| [docs/wordpress/00-analyza.md](docs/wordpress/00-analyza.md) | **Začni tady.** Rozhodnutí, rozsah, náklady, role a účty, podmínky předání |
| [docs/wordpress/05-kostra.md](docs/wordpress/05-kostra.md) | Datový model: akce, místa, žánry, králové, ročníky |
| [docs/wordpress/06-navod-pro-tym.md](docs/wordpress/06-navod-pro-tym.md) | Návod pro toho, kdo web plní. Česky, krok za krokem |
| [docs/wordpress/07-nasazeni.md](docs/wordpress/07-nasazeni.md) | Jak se web nasadil a na co jsem narazil |
| [docs/wordpress/08-vzhled.md](docs/wordpress/08-vzhled.md) | Jak je postavený vzhled a kde se co mění |
| [docs/wordpress/10-vypis-akci-navrh.md](docs/wordpress/10-vypis-akci-navrh.md) | Vzhled výpisu akcí a kde je strop čistého CSS |
| [docs/wordpress/09-schuzka-marketing.md](docs/wordpress/09-schuzka-marketing.md) | Příprava na schůzku se zadavatelem |

Dokumenty `01`–`04` jsou podkladová analýza z první fáze: otázky na zadavatele, detailní návrh
modulů, ověřená fakta o pluginech a test ovládání WordPressu přes MCP. Jsou referenční —
část čísel v nich překonaly pozdější dokumenty, které to u sebe říkají.

## Co je v repozitáři kromě dokumentace

```
wordpress/
├── vzhled/       skripty, které postaví vzhled od nuly, a zdrojové bm.css
├── branding/     loga a paleta ročníku 2024, otevřená otázka licence fontu
├── scf-export/   definice typů obsahu a polí pro Secure Custom Fields
├── nasazeni/     nahrávání na hosting přes FTPS
└── archiv/       stažení starého webu do statické kopie
```

Jak se vzhled postaví znovu od nuly, popisuje [wordpress/vzhled/README.md](wordpress/vzhled/README.md).
Pravidla práce v repozitáři a nasbírané pasti jsou v [CLAUDE.md](CLAUDE.md).
Přihlašovací údaje v repozitáři nejsou a nebudou — **repozitář je veřejný**. Skripty je čtou
ze souboru, na který ukazuje proměnná `BM_WP`.

## Zahozený Next.js

Repozitář původně držel rozpracovanou verzi na Next.js 15 a Strapi 5, od které se v září 2026
ustoupilo. Datový model z ní se do WordPressu přenesl, kód ne — a v listopadu 2026 se smazal,
aby repozitář neukazoval na stack, který neexistuje. V historii gitu zůstává, poslední commit
s ním je `8ec231e`.
