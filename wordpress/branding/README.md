# Branding

Podklady z grafiky ročníku **2024**. Nová grafika pro ročník 2027 přijde na konci roku 2026,
takže tohle je referenční styl, ne finální vizuál.

## Logo

| Soubor | Použití |
|---|---|
| `logo-bile.png` | na barevné a tmavé pozadí |
| `logo-cerne.png` | na světlé pozadí |

Obě verze jsou **jednobarevné** — ověřeno, bílá má jedinou barvu, černá dvanáct kvůli vyhlazení
hran. To je pro web výhoda: logo se položí na libovolné pozadí ročníku a nikdy se s ním nepere.

Tiskový originál v křivkách (5548 × 4016) je ve sdílené složce grafiky, do repa nepatří.

## Paleta 2024

Vizuál stojí na **plochých sytých plochách** — každá sekce nebo festivalový den má svou barvu
a logo je na ní jednobarevně.

| Barva | Kód |
|---|---|
| zelenomodrá | `#1EA198` |
| korálová | `#EE7270` |
| levandulová | `#B6B4DA` |
| žlutá (akcent) | `#F8E862` |

Odvozeno z instagramových postů a stories ročníku 2024, ne z brand manuálu — ten ve složce není.
Před nasazením stojí za potvrzení grafičkou.

Pro srovnání: **současný web** jede na úplně jiné paletě — modrá `#0000D3` a růžová `#ff0055`.

## Font — nevyřešená licence

Tištěná grafika 2024 používá **Archiv** od **Ilyi Bazhanova**:

```
Archiv-Regular.otf, Archiv-SemiBold.otf
Copyright (c) 2023 by Ilya Bazhanov. All rights reserved.
Verze 0.009 · kontakt: mail@ilyabazhanov.com
```

Web přitom jede na **GT Walsheim** od Grilli Type — jiný font, jiná licence.
Jsou to tedy dvě samostatné otázky.

**Soubory fontu nejsou v tomhle repozitáři** a `.gitignore` je sem nepustí. Repozitář je veřejný
a šířit font s „all rights reserved" bez známých podmínek nejde. Originály jsou ve sdílené složce
grafiky spolku.

### Co je potřeba zjistit

1. **Odkud grafička Archiv vzala** — faktura, e-mail, stránka ke stažení? Verze 0.009 vypadá
   na ranou nebo beta distribuci, ale veřejně se o fontu nic dohledat nedá.
2. **Má majáles licenci na GT Walsheim, a je webová?** Grilli Type licencuje webfonty zvlášť
   a podle počtu zobrazení stránek.
3. Pokud ani jedno nevyjde, **napsat Ilyovi Bazhanovovi přímo**. U indie autorů bývá reálné,
   že studentskému festivalu webovou licenci dají zdarma nebo za symbolickou částku.

### Náhradní řešení

Když se licence nedohledá, sáhnout po OFL fontu z Google Fonts s lokálním hostováním.
Vyřeší to i současný problém webu, kde se Space Mono tahá z Google CDN, což je GDPR problém.

## Jak se to promítne do webu

Princip „jedna barva na sekci" **sedí na datový model, který už v kostře je** — kategorie programu
mají pole `barva`. Co grafička dělala ručně pro Instagram, umí web generovat z dat.

Vzhled se proto staví přes **globální paletu Kadence v Customizeru**: barvy budou v devíti slotech,
CSS na ně odkazuje proměnnými `var(--global-palette1..9)` a nikdy napřímo hex hodnotou.
Rebranding na ročník 2027 je pak vyplnění devíti polí, ne přepisování stylů — a zvládne ho
příští sestava bez vývojáře.
