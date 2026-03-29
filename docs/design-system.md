# Design System — Budějovický Majáles

> Verze 1.0 · Aktualizováno 2026-03-29
> Tech stack: Next.js 16 · MUI 7 · Framer Motion · Dark theme · Mobile-first

---

## 1. Design Philosophy

Budějovický Majáles je festival, který studenti dělají pro studenty — design tomu musí odpovídat. Vizuální jazyk je **tmavý, energický a autentický**: žádná sterilní korporátní estetika, ale ani chaotický amatérismus. Každý prvek komunukuje pocit "tuto noc si budeme pamatovat".

Priorita je **mobile-first čitelnost** — drtivá většina návštěvníků přijde z telefonu, uprostřed hluku a slabého světla. Typografie je proto velká a kontrastní, interaktivní plochy mají dostatečnou výšku pro dotek.

**Purposeful motion**: animace existují proto, aby orientovaly uživatele (přechody stránek, stagger load) nebo zvýrazňovaly stav (pulse na odpočtu, glow na kartě s nejvíce hlasy). Pohyb nikdy není jen dekorace.

Barevná paleta kombinuje **zlatou (energy, prestiž, sluneční festival)** a **korálovou (mladost, odvaha, tanec)** na hlubokém zelenočerném pozadí — kombinace, která funguje dobře při špatném osvětlení i v přímém slunci.

---

## 2. Brand Identity & Colors

### 2.1 Primární paleta

| Token | Hex | Použití |
|---|---|---|
| `primary.main` | `#E8A849` | Hlavní CTA, logo, aktivní stavy, ikony |
| `primary.light` | `#F2C97E` | Hover stav na gold prvcích, jemné akcenty |
| `primary.dark` | `#C48A2A` | Pressed stav, border u gold outline buttonů |
| `secondary.main` | `#D4614C` | Akcentová barva, coral tag, vedlejší CTA |
| `secondary.light` | `#E08473` | Hover stav coral prvků |
| `secondary.dark` | `#B04434` | Pressed stav coral prvků |

### 2.2 Pozadí a povrchy

| Token | Hex | Použití |
|---|---|---|
| `background.default` | `#0A0F0D` | Základní pozadí stránek |
| `background.paper` | `#141C18` | Karty, drawer, footer, modály |
| `background.elevated` | `#1C2620` | Povrch nad `paper` — nested karty, tooltips |
| `background.overlay` | `rgba(10,15,13,0.85)` | Hero overlay, scrim za drawerem |

### 2.3 Text

| Token | Hex | Použití |
|---|---|---|
| `text.primary` | `#F5F0EB` | Nadpisy, hlavní body text |
| `text.secondary` | `#B8AFA6` | Podnadpisy, metadata, placeholdery |
| `text.disabled` | `#5C6B63` | Deaktivované prvky |
| `text.hint` | `#7A8C82` | Pomocné popisky, captions |

### 2.4 Accent & Gradient

```css
/* Vivid gold-coral gradient — pro speciální akcenty, borders, backgrounds */
--gradient-brand: linear-gradient(135deg, #E8A849 0%, #D4614C 100%);

/* Subtle gold glow — pro leading candidate, featured karty */
--gradient-gold-glow: radial-gradient(ellipse at center, rgba(232,168,73,0.15) 0%, transparent 70%);

/* Dark background fade — hero overlay, section transitions */
--gradient-bg-fade: linear-gradient(to bottom, rgba(10,15,13,0.6) 0%, rgba(10,15,13,0.95) 100%);
```

Příklad použití v MUI `sx`:
```tsx
background: "linear-gradient(135deg, #E8A849 0%, #D4614C 100%)",
WebkitBackgroundClip: "text",
WebkitTextFillColor: "transparent",
// → gradient text pro sekce nadpisů
```

### 2.5 Stavové barvy (Status Colors)

| Token | Hex | Použití |
|---|---|---|
| `success.main` | `#4CAF7D` | Úspěšná registrace, aktivní soutěž |
| `success.light` | `#7DC9A0` | Success badge pozadí |
| `warning.main` | `#F2B830` | Upozornění, "brzy vyprodáno" |
| `error.main` | `#E05252` | Chyby formuláře, selhání |
| `info.main` | `#5B9BD5` | Informační bannery |

### 2.6 Border & Divider

```css
--border-default:  rgba(255,255,255,0.08);   /* jemné oddělení karet */
--border-subtle:   rgba(255,255,255,0.05);   /* footer divider, section divider */
--border-accent:   rgba(232,168,73,0.35);    /* gold outline button, featured card */
--border-coral:    rgba(212,97,76,0.35);     /* secondary accent border */
--border-focus:    #E8A849;                  /* focus ring u interaktivních prvků */
```

### 2.7 Kdy použít Gold vs Coral

**Gold (#E8A849) — prestiž, energie, výzva k akci:**
- Primární CTA tlačítko ("Koupit vstupenku", "Hlasovat")
- Logo a brand name
- Aktivní navigační stav (underline)
- Vote counter, leading candidate badge
- Countdown čísla
- Sekce headers s gradientem

**Coral (#D4614C) — mladost, vzrušení, vedlejší důraz:**
- School badge / pill
- Sekundární tlačítka a outlined akcenty
- Tags u programu (žánr, kategorie)
- Hover state u social ikon
- Error-adjacent upozornění (ne přímo error, ale urgence)
- Dekorativní prvky v hero oblasti

---

## 3. Typography

### 3.1 Font Stack

**Primární font: Space Grotesk** (Google Fonts)
- Výrazný geometrický grotesque s charakterem
- Výborná čitelnost na malých displejích
- Unikátní "R" a "G" — přidávají osobnost bez ztráty legibility
- Ideální pro energetický festival targeting 16–21 let

**Sekundární / body font: DM Sans**
- Neutrálnější, vyšší legibility při malých velikostech
- Doplňuje Space Grotesk bez konkurence
- Volitelné — Space Grotesk sám o sobě funguje dobře pro body text

```tsx
// frontend/src/app/layout.tsx — integrace fontů
import { Space_Grotesk, DM_Sans } from "next/font/google";

const spaceGrotesk = Space_Grotesk({
  subsets: ["latin", "latin-ext"],
  weight: ["400", "500", "600", "700", "800"],
  variable: "--font-space-grotesk",
  display: "swap",
});

const dmSans = DM_Sans({
  subsets: ["latin"],
  weight: ["400", "500"],
  variable: "--font-dm-sans",
  display: "swap",
});

// V RootLayout:
<html lang="cs" className={`${spaceGrotesk.variable} ${dmSans.variable}`}>
```

```ts
// frontend/src/theme/theme.ts
typography: {
  fontFamily: "var(--font-space-grotesk), var(--font-dm-sans), sans-serif",
  // ...
}
```

### 3.2 Type Scale

| Varianta | Mobile size | Desktop size | Weight | Line-height | Letter-spacing |
|---|---|---|---|---|---|
| `h1` | 2.5rem / 40px | 5rem / 80px | 800 | 1.1 | -0.03em |
| `h2` | 1.75rem / 28px | 3rem / 48px | 700 | 1.2 | -0.02em |
| `h3` | 1.25rem / 20px | 1.75rem / 28px | 600 | 1.3 | -0.01em |
| `h4` | 1.125rem / 18px | 1.375rem / 22px | 600 | 1.35 | -0.01em |
| `h5` | 1rem / 16px | 1.125rem / 18px | 600 | 1.4 | 0 |
| `h6` | 0.875rem / 14px | 0.875rem / 14px | 700 | 1.4 | 0.04em |
| `body1` | 1rem / 16px | 1rem / 16px | 400 | 1.6 | 0 |
| `body2` | 0.875rem / 14px | 0.875rem / 14px | 400 | 1.5 | 0 |
| `caption` | 0.75rem / 12px | 0.75rem / 12px | 400 | 1.4 | 0.03em |
| `overline` | 0.75rem / 12px | 0.75rem / 12px | 600 | 1.4 | 0.15em |
| `button` | — | — | 700 | — | 0.02em |

> **Poznámka k `h6` a `overline`:** Používají uppercase + letter-spacing jako section label ("PROGRAM 2026", "NAŠI PARTNEŘI"). Overline je výchozí uppercase v MUI.

```ts
// Rozšíření theme.ts na kompletní type scale
typography: {
  fontFamily: "var(--font-space-grotesk), sans-serif",
  h1: {
    fontSize: "2.5rem",
    fontWeight: 800,
    lineHeight: 1.1,
    letterSpacing: "-0.03em",
    "@media (min-width:600px)": { fontSize: "3.5rem" },
    "@media (min-width:900px)": { fontSize: "5rem" },
  },
  h2: {
    fontSize: "1.75rem",
    fontWeight: 700,
    lineHeight: 1.2,
    letterSpacing: "-0.02em",
    "@media (min-width:600px)": { fontSize: "2.25rem" },
    "@media (min-width:900px)": { fontSize: "3rem" },
  },
  h3: {
    fontSize: "1.25rem",
    fontWeight: 600,
    lineHeight: 1.3,
    letterSpacing: "-0.01em",
    "@media (min-width:600px)": { fontSize: "1.5rem" },
    "@media (min-width:900px)": { fontSize: "1.75rem" },
  },
  h4: {
    fontSize: "1.125rem",
    fontWeight: 600,
    lineHeight: 1.35,
    letterSpacing: "-0.01em",
    "@media (min-width:600px)": { fontSize: "1.375rem" },
  },
  h5: {
    fontSize: "1rem",
    fontWeight: 600,
    lineHeight: 1.4,
    "@media (min-width:600px)": { fontSize: "1.125rem" },
  },
  h6: {
    fontSize: "0.875rem",
    fontWeight: 700,
    lineHeight: 1.4,
    textTransform: "uppercase",
    letterSpacing: "0.04em",
  },
  body1: {
    fontSize: "1rem",
    lineHeight: 1.6,
    fontWeight: 400,
  },
  body2: {
    fontSize: "0.875rem",
    lineHeight: 1.5,
    fontWeight: 400,
  },
  caption: {
    fontSize: "0.75rem",
    lineHeight: 1.4,
    letterSpacing: "0.03em",
  },
  overline: {
    fontSize: "0.75rem",
    fontWeight: 600,
    letterSpacing: "0.15em",
    textTransform: "uppercase",
  },
  button: {
    fontWeight: 700,
    textTransform: "none",
    letterSpacing: "0.02em",
  },
},
```

---

## 4. Spacing & Layout

### 4.1 Spacing Scale (base 8px)

MUI spacing(n) = n × 8px

| Token | Hodnota | Typické použití |
|---|---|---|
| `spacing(0.5)` | 4px | Micro gap (ikona + text) |
| `spacing(1)` | 8px | Gap uvnitř komponent |
| `spacing(1.5)` | 12px | Padding malých prvků (chip, badge) |
| `spacing(2)` | 16px | Padding karet, gap v gridu |
| `spacing(3)` | 24px | Section padding (xs) |
| `spacing(4)` | 32px | Section padding (sm), card padding |
| `spacing(5)` | 40px | Velký gap mezi sekcemi |
| `spacing(6)` | 48px | Section padding (md+) |
| `spacing(8)` | 64px | Velký section gap |
| `spacing(10)` | 80px | Hero padding, major sections |
| `spacing(12)` | 96px | Maximum section padding |

### 4.2 Container Max-widths

```ts
// Odpovídá MUI Container maxWidth props
xs:  "100%"      // fluid do 600px
sm:  600px       // small breakpoint
md:  900px       // main content (text pages, FAQ)
lg:  1200px      // wide content (partneri, program grid)
xl:  1536px      // maximum (nikdy nepoužívat pro main content)
```

Doporučené použití per-sekce:
- Hero, full-bleed bannery: bez Containeru (fluid)
- Textové stránky (O festivalu, FAQ, GDPR): `maxWidth="md"`
- Standardní sekce (Program, Partneři): `maxWidth="lg"`
- Specifické karty (Králové, Featured): `maxWidth="lg"` s interním gridem

### 4.3 Breakpoints

```ts
breakpoints: {
  values: {
    xs: 0,    // mobilní telefony (portrait)
    sm: 600,  // mobilní telefony (landscape) / malé tablety
    md: 900,  // tablety, malé laptopy
    lg: 1200, // desktopy
    xl: 1536, // velké monitory
  },
},
```

### 4.4 Grid System

MUI Grid 2 (v7) — 12-sloupcový grid:

```tsx
// Typický section layout
<Grid container spacing={{ xs: 2, sm: 3 }}>
  {/* Kandidát karta — 12/6/4 cols */}
  <Grid size={{ xs: 12, sm: 6, lg: 4 }}>...</Grid>

  {/* Partner logo — 6/4/3/2 cols */}
  <Grid size={{ xs: 6, sm: 4, md: 3, lg: 2 }}>...</Grid>
</Grid>
```

---

## 5. Component Specs

### 5.1 Buttons

#### Primary Button (Gold Filled)
```tsx
// Použití: hlavní CTA — "Koupit vstupenku", "Hlasovat"
<Button variant="contained" size="large">
  Koupit vstupenku
</Button>
```

```ts
// theme.ts — MuiButton override
MuiButton: {
  styleOverrides: {
    root: {
      borderRadius: 8,
      padding: "10px 24px",
      fontWeight: 700,
      fontSize: "0.95rem",
      textTransform: "none",
      letterSpacing: "0.02em",
      transition: "all 0.2s ease",
      "&:hover": {
        transform: "translateY(-1px)",
        boxShadow: "0 4px 20px rgba(232,168,73,0.35)",
      },
      "&:active": {
        transform: "translateY(0)",
      },
    },
    sizeLarge: {
      padding: "14px 32px",
      fontSize: "1.05rem",
    },
    sizeSmall: {
      padding: "6px 16px",
      fontSize: "0.85rem",
      borderRadius: 6,
    },
  },
},
```

Visual spec:
- Background: `#E8A849`
- Text color: `#0A0F0D` (tmavý pro kontrast)
- Hover: `background #F2C97E`, `boxShadow 0 4px 20px rgba(232,168,73,0.35)`
- Active/pressed: `background #C48A2A`, no shadow
- Disabled: `background rgba(232,168,73,0.3)`, `color rgba(10,15,13,0.5)`
- Focus ring: `outline 2px solid #E8A849`, `outlineOffset 2px`

#### Secondary Button (Outline)
```tsx
<Button variant="outlined" color="primary">
  Více informací
</Button>
```

Visual spec:
- Border: `1px solid rgba(232,168,73,0.5)`
- Text color: `#E8A849`
- Hover: `border-color #E8A849`, `background rgba(232,168,73,0.08)`
- Coral varianta: `color="secondary"` — border `#D4614C`, text `#D4614C`

#### Ghost Button
```tsx
<Button variant="text" color="primary">
  Přeskočit
</Button>
```

Visual spec:
- Žádný border ani background
- Text color: `#B8AFA6` (text.secondary)
- Hover: `color #F5F0EB`, `background rgba(255,255,255,0.05)`

---

### 5.2 Cards

#### Standard Card
Použití: Kandidáti, partneři střední tier, FAQ items

```tsx
<Card sx={{
  bgcolor: "background.paper",
  borderRadius: 3,   // = 12px (shape.borderRadius)
  border: "1px solid rgba(255,255,255,0.08)",
  transition: "all 0.2s ease",
  "&:hover": {
    transform: "translateY(-4px) scale(1.01)",
    borderColor: "rgba(232,168,73,0.35)",
    boxShadow: "0 8px 32px rgba(0,0,0,0.4)",
  },
}}>
```

Vnitřní padding: `24px` (desktop) / `16px` (mobile)

#### Featured Card (Hlavní partneři, vedoucí kandidát)
```tsx
<Card sx={{
  bgcolor: "background.paper",
  borderRadius: 3,
  border: "1px solid rgba(232,168,73,0.4)",
  boxShadow: "0 0 24px rgba(232,168,73,0.12)",
  position: "relative",
  "&::before": {
    content: '""',
    position: "absolute",
    inset: 0,
    borderRadius: "inherit",
    background: "linear-gradient(135deg, rgba(232,168,73,0.06) 0%, transparent 60%)",
    pointerEvents: "none",
  },
  "&:hover": {
    borderColor: "rgba(232,168,73,0.7)",
    boxShadow: "0 0 40px rgba(232,168,73,0.2)",
    transform: "translateY(-2px)",
  },
}}>
```

#### Glass-morphism Card
Použití: Speciální call-to-action, overlay na hero sekci, featured announcement

```tsx
<Box sx={{
  background: "rgba(20, 28, 24, 0.6)",
  backdropFilter: "blur(16px)",
  WebkitBackdropFilter: "blur(16px)",
  border: "1px solid rgba(255,255,255,0.12)",
  borderRadius: 3,
  boxShadow: "0 8px 32px rgba(0,0,0,0.4)",
}}>
```

> Poznámka: Glassmorphism funguje pouze pokud je pod kartou viditelný obsah (obrázek, pozadí). Na plném `background.default` efekt není viditelný.

---

### 5.3 Navigation (Header)

#### Transparent → Frosted Glass scroll efekt

Současný Header používá statický gradient. Pro plnohodnotné frosted glass při scrollu:

```tsx
"use client";
import { useScrollTrigger } from "@mui/material";

const scrolled = useScrollTrigger({ disableHysteresis: true, threshold: 60 });

<AppBar
  position="fixed"
  elevation={0}
  sx={{
    transition: "all 0.3s ease",
    background: scrolled
      ? "rgba(10,15,13,0.85)"
      : "linear-gradient(to bottom, rgba(10,15,13,0.9) 0%, transparent 100%)",
    backdropFilter: scrolled ? "blur(16px)" : "none",
    WebkitBackdropFilter: scrolled ? "blur(16px)" : "none",
    borderBottom: scrolled
      ? "1px solid rgba(255,255,255,0.08)"
      : "1px solid transparent",
  }}
>
```

#### Desktop Nav — Active State
```tsx
// Aktivní odkaz: gold underline
const isActive = pathname === href;

sx={{
  color: isActive ? "primary.main" : "text.secondary",
  position: "relative",
  "&::after": {
    content: '""',
    position: "absolute",
    bottom: -2,
    left: "50%",
    transform: isActive ? "translateX(-50%) scaleX(1)" : "translateX(-50%) scaleX(0)",
    width: "80%",
    height: 2,
    bgcolor: "primary.main",
    borderRadius: 1,
    transition: "transform 0.2s ease",
  },
  "&:hover::after": {
    transform: "translateX(-50%) scaleX(1)",
  },
}}
```

#### Mobile Drawer
- Šířka: `260px` (standardní) nebo `85vw` (full-bleed varianta)
- Background: `background.paper` s `backdropFilter: "blur(8px)"`
- Animace: Framer Motion `x: 260 → 0`, `duration: 0.25s`, `ease: [0.32, 0, 0.67, 0]`
- Nav items: `fontSize 1.1rem`, `fontWeight 600`, `py 1.5`
- Aktivní item: gold left border `4px solid #E8A849`

#### Logo Design Guidance
- Text: "Budějovický Majáles" nebo zkratka "BM" pro velmi malé breakpointy
- Font-weight: `800`, `color: primary.main`
- Letter-spacing: `-0.01em`
- Mobile size: `1rem`, Desktop: `1.2rem`
- Hover: jemný opacity transition `0.8 → 1`
- Budoucí: SVG logo přidat jako `<Image>` vedle textu, height `32px`

---

### 5.4 Section Layout

#### Section Header Pattern
```tsx
// Standardní sekce header (nadpis + podnadpis)
<Box sx={{ textAlign: "center", mb: { xs: 4, sm: 6 } }}>
  {/* Overline label */}
  <Typography
    variant="overline"
    sx={{ color: "primary.main", display: "block", mb: 1 }}
  >
    LABEL SEKCE
  </Typography>

  {/* Hlavní nadpis */}
  <Typography variant="h2" sx={{ mb: 2 }}>
    Nadpis sekce
  </Typography>

  {/* Podnadpis — volitelný */}
  <Typography
    variant="body1"
    sx={{ color: "text.secondary", maxWidth: 560, mx: "auto" }}
  >
    Krátký popis sekce, maximálně 2 věty.
  </Typography>
</Box>
```

#### Gradient Divider
```tsx
<Box
  sx={{
    height: 1,
    background: "linear-gradient(90deg, transparent 0%, rgba(232,168,73,0.4) 50%, transparent 100%)",
    my: { xs: 4, sm: 6 },
  }}
/>
```

#### Empty State Pattern
```tsx
<Box sx={{
  textAlign: "center",
  py: { xs: 6, sm: 10 },
  px: 2,
}}>
  {/* Ikona — MUI SvgIcon nebo emoji v Box */}
  <Typography sx={{ fontSize: "3rem", mb: 2 }}>🎪</Typography>

  <Typography variant="h5" sx={{ mb: 1 }}>
    Brzy se dozvíte více
  </Typography>

  <Typography variant="body2" sx={{ color: "text.secondary", maxWidth: 400, mx: "auto" }}>
    Tato sekce zatím není aktivní. Sledujte nás na sociálních sítích.
  </Typography>
</Box>
```

---

## 6. Sekce Králové (King Election)

### 6.1 Kontext

Celotýdenní soutěž kde každý den přibývají hlasy. Kandidáti mají jméno, školu + téma týdne, fotku a počet hlasů. Vedoucí kandidát je zvýrazněn.

### 6.2 Candidate Card

#### Mobile — Vertikální layout (xs/sm)
```
┌─────────────────────────┐
│  [FOTO — aspect 4:5]    │
│  ┌─ CROWN ikona (leading only)
│                         │
├─────────────────────────┤
│  Jméno Kandidáta        │  ← h5, font-weight 700
│  [School Badge]         │  ← Chip/Pill
│                         │
│  ┌──────────────────┐   │
│  │  🏆  1 247 hlasů │   │  ← Vote Counter
│  └──────────────────┘   │
│  [Hlasovat →]           │  ← Button sm
└─────────────────────────┘
```

#### Desktop — Grid 3 columns (md+)
```
┌──────┬────────────────────────┐
│ FOTO │ Jméno Kandidáta        │
│ 1:1  │ [School Badge]         │
│      │                        │
│      │  🏆  1 247 hlasů       │
│      │                        │
│      │  [Hlasovat →]          │
└──────┴────────────────────────┘
```

#### Candidate Card — MUI implementace

```tsx
<Card sx={{
  bgcolor: "background.paper",
  border: isLeading
    ? "1px solid rgba(232,168,73,0.5)"
    : "1px solid rgba(255,255,255,0.08)",
  borderRadius: 3,
  overflow: "hidden",
  transition: "all 0.2s ease",
  boxShadow: isLeading ? "0 0 32px rgba(232,168,73,0.15)" : "none",
  "&:hover": {
    transform: "translateY(-4px)",
    borderColor: "rgba(232,168,73,0.4)",
    boxShadow: "0 12px 40px rgba(0,0,0,0.4)",
  },
}}>
  {/* Foto */}
  <Box sx={{ position: "relative" }}>
    <Box
      component="img"
      src={candidate.photo}
      alt={candidate.name}
      sx={{
        width: "100%",
        aspectRatio: { xs: "4/5", md: "1/1" },
        objectFit: "cover",
        display: "block",
      }}
    />
    {isLeading && (
      <Box sx={{
        position: "absolute",
        top: 12,
        right: 12,
        bgcolor: "primary.main",
        color: "#0A0F0D",
        borderRadius: "50%",
        width: 36,
        height: 36,
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        fontSize: "1.1rem",
      }}>
        👑
      </Box>
    )}
  </Box>

  {/* Content */}
  <CardContent sx={{ p: { xs: 2, sm: 3 } }}>
    <Typography variant="h5" sx={{ fontWeight: 700, mb: 1 }}>
      {candidate.name}
    </Typography>

    {/* School Badge */}
    <Chip
      label={candidate.school}
      size="small"
      sx={{
        bgcolor: "rgba(212,97,76,0.15)",
        color: "secondary.light",
        border: "1px solid rgba(212,97,76,0.3)",
        fontWeight: 600,
        fontSize: "0.75rem",
        mb: 2,
      }}
    />

    {/* Vote Counter */}
    <Box sx={{
      display: "flex",
      alignItems: "baseline",
      gap: 1,
      mb: 2,
    }}>
      <Typography sx={{
        fontSize: { xs: "2rem", sm: "2.5rem" },
        fontWeight: 800,
        color: "primary.main",
        lineHeight: 1,
        fontVariantNumeric: "tabular-nums",
      }}>
        {candidate.votes.toLocaleString("cs")}
      </Typography>
      <Typography variant="body2" sx={{ color: "text.secondary" }}>
        hlasů
      </Typography>
    </Box>

    <Button variant="outlined" fullWidth size="small">
      Hlasovat
    </Button>
  </CardContent>
</Card>
```

### 6.3 Vote Counter — Standalone

Používá se pro celkový count nebo real-time update:

```tsx
<Box sx={{ textAlign: "center" }}>
  <motion.div
    key={votes}
    initial={{ scale: 1.2, color: "#F2C97E" }}
    animate={{ scale: 1, color: "#E8A849" }}
    transition={{ duration: 0.4, ease: "easeOut" }}
  >
    <Typography sx={{
      fontSize: { xs: "3.5rem", sm: "5rem" },
      fontWeight: 800,
      color: "primary.main",
      lineHeight: 1,
      fontVariantNumeric: "tabular-nums",
    }}>
      {votes.toLocaleString("cs")}
    </Typography>
  </motion.div>
  <Typography variant="overline">celkem hlasů dnes</Typography>
</Box>
```

### 6.4 School Badge (Chip)

```tsx
// Varianta coral (default pro kandidáty)
<Chip
  label="FEI JU"
  sx={{
    bgcolor: "rgba(212,97,76,0.15)",
    color: "#E08473",
    border: "1px solid rgba(212,97,76,0.3)",
    fontWeight: 600,
    fontSize: "0.75rem",
    height: 24,
    "& .MuiChip-label": { px: 1.5 },
  }}
/>

// Varianta gold (pro vítěze / leading candidate)
<Chip
  label="Vítěz"
  sx={{
    bgcolor: "rgba(232,168,73,0.15)",
    color: "#E8A849",
    border: "1px solid rgba(232,168,73,0.4)",
    fontWeight: 700,
  }}
/>
```

### 6.5 Leading Indicator

Kandidát s nejvíce hlasy dostane:
1. Crown ikona (👑 nebo MUI `EmojiEventsIcon`) v pravém horním rohu fotky — gold circle badge
2. Gold border: `border: "1px solid rgba(232,168,73,0.5)"`
3. Subtle gold glow: `boxShadow: "0 0 32px rgba(232,168,73,0.15)"`
4. "Vede" gold Chip nad jménem
5. Karta je mírně větší v desktop gridu: `gridColumn: "span 1"` s `transform: "scale(1.02)"`

### 6.6 Empty State — Soutěž není aktivní

```tsx
<Box sx={{ textAlign: "center", py: 10 }}>
  <Typography sx={{ fontSize: "3.5rem", mb: 3 }}>👑</Typography>
  <Typography variant="h3" sx={{ mb: 2 }}>
    Soutěž o Krále zatím nezačala
  </Typography>
  <Typography variant="body1" sx={{ color: "text.secondary", maxWidth: 480, mx: "auto", mb: 4 }}>
    Soutěž startuje první den festivalu. Sleduj naše sociální sítě,
    ať nepropásneš start hlasování.
  </Typography>
  <Button variant="outlined" href="https://instagram.com/..." target="_blank">
    Sledovat na Instagramu
  </Button>
</Box>
```

### 6.7 Page Layout /kralove

```
Header (sticky)
│
├─ Hero band (gradient bg, min-height 280px)
│   ├─ Overline: "SOUTĚŽ KRÁLŮ"
│   ├─ h1: "Kdo bude Králem Majálesu?"
│   └─ Popis soutěže (1–2 věty)
│
├─ VoteCounter section (py 4, centered)
│   └─ Celkový počet dnešních hlasů
│
├─ Candidate grid (Container lg)
│   ├─ Section header: "Kandidáti [rok]"
│   └─ Grid: xs=1col, sm=2col, lg=3col
│       └─ Candidate Cards (leading first)
│
└─ Footer
```

---

## 7. Sekce Partneři

### 7.1 Tier Hierarchy

```
Hlavní partneři      → biggest treatment, named cards
Mediální partneři    → medium cards, logo only
Ostatní partneři     → compact logo grid
```

### 7.2 Hlavní partneři (Tier 1)

Logo max-width: `200px`, min-height logo area: `80px`

```tsx
<Card sx={{
  bgcolor: "#FFFFFF",              // bílé pozadí pro logo
  borderRadius: 2,
  p: { xs: 2, sm: 3 },
  display: "flex",
  flexDirection: "column",
  alignItems: "center",
  justifyContent: "center",
  gap: 1.5,
  border: "1px solid rgba(255,255,255,0.1)",
  transition: "all 0.2s ease",
  "&:hover": {
    transform: "translateY(-4px)",
    boxShadow: "0 8px 32px rgba(232,168,73,0.2)",
    border: "1px solid rgba(232,168,73,0.4)",
  },
}}>
  <Box
    component="img"
    src={partner.logo}
    alt={partner.name}
    sx={{
      maxWidth: 200,
      maxHeight: 80,
      objectFit: "contain",
      width: "100%",
    }}
  />
  {/* Volitelně název pod logem */}
  <Typography variant="caption" sx={{ color: "#333", fontWeight: 600 }}>
    {partner.name}
  </Typography>
</Card>
```

Grid layout:
- xs: 1 karta na řádek
- sm: 2 karty na řádek
- md: 3 karty na řádek

### 7.3 Mediální partneři (Tier 2)

Logo max-width: `140px`

```tsx
<Card sx={{
  bgcolor: "rgba(255,255,255,0.95)",
  borderRadius: 2,
  p: { xs: 1.5, sm: 2 },
  display: "flex",
  alignItems: "center",
  justifyContent: "center",
  "&:hover": {
    transform: "scale(1.04)",
    boxShadow: "0 4px 20px rgba(0,0,0,0.3)",
  },
  transition: "all 0.2s ease",
}}>
  <Box
    component="img"
    src={partner.logo}
    alt={partner.name}
    sx={{ maxWidth: 140, maxHeight: 56, objectFit: "contain", width: "100%" }}
  />
</Card>
```

Grid layout:
- xs: 2 karty na řádek
- sm: 3 karty na řádek
- md: 4 karty na řádek

### 7.4 Ostatní partneři (Tier 3)

Logo max-width: `100px`, kompaktní

```tsx
<Box sx={{
  bgcolor: "rgba(255,255,255,0.9)",
  borderRadius: 1.5,
  p: 1.5,
  display: "flex",
  alignItems: "center",
  justifyContent: "center",
  opacity: 0.85,
  transition: "all 0.2s ease",
  "&:hover": {
    opacity: 1,
    transform: "scale(1.05)",
  },
}}>
  <Box
    component="img"
    src={partner.logo}
    alt={partner.name}
    sx={{ maxWidth: 100, maxHeight: 44, objectFit: "contain", width: "100%" }}
  />
</Box>
```

Grid layout:
- xs: 3 karty na řádek
- sm: 4 karty na řádek
- md: 6 karty na řádek

### 7.5 Page Layout /partneri

```
Header (sticky)
│
├─ Page Header (py 8, text center)
│   ├─ Overline: "SPOLUPRÁCE"
│   └─ h1: "Naši partneři"
│
├─ Tier 1 — Hlavní partneři (Container md)
│   ├─ Section header + gradient divider
│   └─ Grid (xs=1, sm=2, md=3)
│
├─ Gradient Divider
│
├─ Tier 2 — Mediální partneři (Container lg)
│   ├─ h3: "Mediální partneři"
│   └─ Grid (xs=2, sm=3, md=4)
│
├─ Gradient Divider
│
├─ Tier 3 — Ostatní partneři (Container lg)
│   ├─ h3: "Ostatní partneři"
│   └─ Grid (xs=3, sm=4, md=6)
│
└─ Footer
```

---

## 8. Motion & Animation Guidelines

### 8.1 Princip

**"Purposeful motion"** — každá animace plní funkci:
- Orientuje uživatele (page transitions, stagger load)
- Potvrzuje interakci (hover, click feedback)
- Komunikuje stav (pulse na countdown, glow na leading kandidátovi)
- Přitahuje pozornost k důležitému (bounce na CTA po page load)

**Nikdy:** animace pro samotnou estetiku, rotující loga, parallax efekty bez obsahu.

### 8.2 Easing Curves

```ts
// Pojmenované easing hodnoty pro konzistenci
const easings = {
  standard:    [0.4, 0, 0.2, 1],    // výchozí pro většinu přechodů (MUI default)
  decelerate:  [0, 0, 0.2, 1],      // prvky přicházející do view (fade-up, slide-in)
  accelerate:  [0.4, 0, 1, 1],      // prvky odcházející (dismiss, hide)
  sharp:       [0.4, 0, 0.6, 1],    // rychlé okamžité přechody (menu toggle)
  spring:      { type: "spring", stiffness: 400, damping: 30 },  // hover scale, bounce
} as const;
```

### 8.3 Stagger Fade-up (Page Load)

Pattern z HeroSection — zachovat v celé aplikaci:

```tsx
// Framer Motion variants — standardní stagger
const staggerContainer = {
  hidden: {},
  visible: {
    transition: { staggerChildren: 0.12, delayChildren: 0.1 },
  },
};

const fadeUp = {
  hidden: { opacity: 0, y: 24 },
  visible: {
    opacity: 1,
    y: 0,
    transition: { duration: 0.5, ease: [0, 0, 0.2, 1] },
  },
};

// Použití
<motion.div variants={staggerContainer} initial="hidden" animate="visible">
  <motion.div variants={fadeUp}><Typography variant="h1">...</Typography></motion.div>
  <motion.div variants={fadeUp}><Typography variant="body1">...</Typography></motion.div>
  <motion.div variants={fadeUp}><Button>...</Button></motion.div>
</motion.div>
```

### 8.4 Card Hover

```tsx
// Framer Motion whileHover (preferovaný pro interaktivní karty)
<motion.div
  whileHover={{ scale: 1.02, y: -4 }}
  transition={{ duration: 0.2, ease: [0.4, 0, 0.2, 1] }}
>
  <Card>...</Card>
</motion.div>

// Nebo čistě CSS via MUI sx (jednodušší, vhodné pro statické karty)
sx={{
  transition: "transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease",
  "&:hover": {
    transform: "translateY(-4px) scale(1.01)",
    boxShadow: "0 12px 40px rgba(0,0,0,0.4)",
    borderColor: "rgba(232,168,73,0.4)",
  },
}}
```

### 8.5 Page Transitions

```tsx
// Wrapper pro každou stránku — fade in
const pageVariants = {
  hidden: { opacity: 0 },
  visible: { opacity: 1, transition: { duration: 0.3, ease: [0.4, 0, 0.2, 1] } },
  exit:   { opacity: 0, transition: { duration: 0.2 } },
};

// V layout nebo page komponentě:
<motion.main
  variants={pageVariants}
  initial="hidden"
  animate="visible"
  exit="exit"
>
  {children}
</motion.main>
```

### 8.6 Countdown Pulse

```tsx
// Pulse animace pro živost countdown čísel
const pulse = {
  animate: {
    scale: [1, 1.04, 1],
    transition: {
      duration: 1,
      repeat: Infinity,
      repeatType: "loop",
      ease: "easeInOut",
    },
  },
};

// Aplikovat na sekundy nebo celý countdown
<motion.div variants={pulse} animate="animate">
  <CountdownUnit value={seconds} label="sekund" />
</motion.div>
```

### 8.7 Vote Counter Update

Při změně hodnoty hlasů (real-time nebo page refresh):

```tsx
<motion.span
  key={voteCount}          // key trigger re-animace při změně hodnoty
  initial={{ scale: 1.3, color: "#F2C97E", opacity: 0.6 }}
  animate={{ scale: 1, color: "#E8A849", opacity: 1 }}
  transition={{ duration: 0.4, ease: [0, 0, 0.2, 1] }}
>
  {voteCount.toLocaleString("cs")}
</motion.span>
```

### 8.8 Timing Reference

| Akce | Duration | Easing |
|---|---|---|
| Hover state (color, border) | 200ms | ease |
| Card hover (transform) | 200ms | `[0.4,0,0.2,1]` |
| Page fade in | 300ms | `[0,0,0.2,1]` |
| Page fade out | 200ms | `[0.4,0,1,1]` |
| Stagger item | 120ms delay | `[0,0,0.2,1]` |
| Countdown pulse | 1000ms loop | `easeInOut` |
| Vote counter update | 400ms | `[0,0,0.2,1]` |
| Drawer slide | 250ms | `[0.32,0,0.67,0]` |
| Modal/Dialog open | 250ms | `[0,0,0.2,1]` |

---

## 9. Dark Theme Specific

### 9.1 Background Layers

Tmavý theme používá vrstvení povrchů — každá vrstva je o cca 5–8% světlejší:

```
Level 0 — default:   #0A0F0D   (základní pozadí stránek)
Level 1 — paper:     #141C18   (karty, sidebar, footer)
Level 2 — elevated:  #1C2620   (nested karty, tooltips, dropdowny)
Level 3 — modal:     #222E28   (dialogy, modály)
Level 4 — overlay:   rgba(10,15,13,0.85)  (scrim za modálem)
```

### 9.2 Border Colors

```css
/* Kontextová použití */
--border-default:   rgba(255,255,255,0.08);   /* standard karta na paper */
--border-subtle:    rgba(255,255,255,0.05);   /* sekce divider, footer */
--border-medium:    rgba(255,255,255,0.12);   /* elevated povrchy */
--border-strong:    rgba(255,255,255,0.20);   /* aktivní / focused stav */
--border-accent:    rgba(232,168,73,0.35);    /* gold accent */
--border-accent-strong: rgba(232,168,73,0.65); /* hover/focus na gold prvcích */
--border-coral:     rgba(212,97,76,0.35);     /* coral accent */
--border-focus:     #E8A849;                  /* focus ring (accessibility) */
```

### 9.3 Shadow System — Glows místo klasických stínů

Na tmavém theamu klasické šedé stíny nejsou viditelné. Místo nich se používají:

**Tmavé stíny (hloubka):**
```css
--shadow-sm:  0 2px 8px rgba(0,0,0,0.3);
--shadow-md:  0 4px 20px rgba(0,0,0,0.45);
--shadow-lg:  0 8px 40px rgba(0,0,0,0.6);
--shadow-xl:  0 16px 64px rgba(0,0,0,0.7);
```

**Gold Glow (důraz, featured prvky):**
```css
--glow-gold-sm:  0 0 12px rgba(232,168,73,0.2);
--glow-gold-md:  0 0 24px rgba(232,168,73,0.25);
--glow-gold-lg:  0 0 48px rgba(232,168,73,0.3);
```

**Coral Glow (sekundární akcenty):**
```css
--glow-coral-sm: 0 0 12px rgba(212,97,76,0.2);
--glow-coral-md: 0 0 24px rgba(212,97,76,0.25);
```

**Použití v MUI sx:**
```tsx
// Leading candidate karta
boxShadow: "0 0 32px rgba(232,168,73,0.2), 0 8px 32px rgba(0,0,0,0.5)"

// Primary button hover
boxShadow: "0 4px 20px rgba(232,168,73,0.35)"

// Featured partner karta
boxShadow: "0 0 24px rgba(232,168,73,0.12)"
```

### 9.4 Glass-morphism Recipe

```css
/* Recept pro glass efekt — tmavý theme varianta */
background: rgba(20, 28, 24, 0.55);      /* paper color s průhledností */
backdrop-filter: blur(16px) saturate(180%);
-webkit-backdrop-filter: blur(16px) saturate(180%);
border: 1px solid rgba(255, 255, 255, 0.10);
box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
```

```tsx
// MUI sx zápis
sx={{
  background: "rgba(20, 28, 24, 0.55)",
  backdropFilter: "blur(16px) saturate(180%)",
  WebkitBackdropFilter: "blur(16px) saturate(180%)",
  border: "1px solid rgba(255,255,255,0.10)",
  boxShadow: "0 8px 32px rgba(0,0,0,0.4)",
  borderRadius: 3,
}}
```

**Varianta pro header při scrollu (méně rozmazání):**
```tsx
background: "rgba(10, 15, 13, 0.85)",
backdropFilter: "blur(12px)",
```

---

## 10. Implementation Notes

### 10.1 Google Fonts v Next.js App Router

```tsx
// frontend/src/app/layout.tsx
import { Space_Grotesk, DM_Sans } from "next/font/google";

const spaceGrotesk = Space_Grotesk({
  subsets: ["latin", "latin-ext"],
  weight: ["400", "500", "600", "700", "800"],
  variable: "--font-space-grotesk",
  display: "swap",
  preload: true,
});

const dmSans = DM_Sans({
  subsets: ["latin"],
  weight: ["400", "500"],
  variable: "--font-dm-sans",
  display: "swap",
  preload: false,    // sekundární font — neprioritizovat
});

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="cs" className={`${spaceGrotesk.variable} ${dmSans.variable}`}>
      <body>
        <ThemeRegistry>
          ...
        </ThemeRegistry>
      </body>
    </html>
  );
}
```

### 10.2 Rozšíření MUI Theme

```ts
// frontend/src/theme/theme.ts — kompletní doporučená konfigurace

import { createTheme, type ThemeOptions } from "@mui/material/styles";

const themeOptions: ThemeOptions = {
  palette: {
    mode: "dark",
    primary: {
      main: "#E8A849",
      light: "#F2C97E",
      dark: "#C48A2A",
      contrastText: "#0A0F0D",
    },
    secondary: {
      main: "#D4614C",
      light: "#E08473",
      dark: "#B04434",
      contrastText: "#FFFFFF",
    },
    background: {
      default: "#0A0F0D",
      paper: "#141C18",
    },
    text: {
      primary: "#F5F0EB",
      secondary: "#B8AFA6",
      disabled: "#5C6B63",
    },
    success: {
      main: "#4CAF7D",
      light: "#7DC9A0",
    },
    warning: {
      main: "#F2B830",
    },
    error: {
      main: "#E05252",
    },
    divider: "rgba(255,255,255,0.08)",
  },
  typography: {
    fontFamily: "var(--font-space-grotesk), sans-serif",
    // ... (viz sekce 3.2)
  },
  shape: {
    borderRadius: 12,
  },
  breakpoints: {
    values: { xs: 0, sm: 600, md: 900, lg: 1200, xl: 1536 },
  },
  components: {
    MuiButton: {
      styleOverrides: {
        root: {
          borderRadius: 8,
          padding: "10px 24px",
          fontWeight: 700,
          textTransform: "none",
          letterSpacing: "0.02em",
          transition: "all 0.2s ease",
          "&:hover": {
            transform: "translateY(-1px)",
          },
        },
        sizeLarge: { padding: "14px 32px", fontSize: "1.05rem" },
        sizeSmall: { padding: "6px 16px", fontSize: "0.85rem", borderRadius: 6 },
      },
    },
    MuiCard: {
      styleOverrides: {
        root: {
          backgroundImage: "none",   // přepsat MUI gradient overlay
          border: "1px solid rgba(255,255,255,0.08)",
        },
      },
    },
    MuiChip: {
      styleOverrides: {
        root: {
          borderRadius: 6,
        },
      },
    },
    MuiFocusVisible: {
      // Pokryto přes CSS globálně — viz níže
    },
  },
};

const theme = createTheme(themeOptions);
export default theme;
```

**Přidání custom color tokenů (TypeScript augmentation):**
```ts
// frontend/src/theme/theme.d.ts
import "@mui/material/styles";

declare module "@mui/material/styles" {
  interface TypeBackground {
    elevated: string;
    overlay: string;
  }
}
```

```ts
// V theme.ts po augmentaci:
background: {
  default: "#0A0F0D",
  paper: "#141C18",
  elevated: "#1C2620",
  overlay: "rgba(10,15,13,0.85)",
},
```

### 10.3 Přístupnost (Accessibility)

**Kontrast:**
- `text.primary` `#F5F0EB` na `background.default` `#0A0F0D` → contrast ratio **~14:1** ✓ (AAA)
- `text.secondary` `#B8AFA6` na `background.default` → ~**6.8:1** ✓ (AA)
- `primary.main` `#E8A849` na `background.default` → ~**6.5:1** ✓ (AA)
- `secondary.main` `#D4614C` na `background.default` → ~**3.2:1** — POZOR: pro dekoraci OK, pro text minimálně 4.5:1 je potřeba použít `secondary.light`
- Minimální kontrast pro text: **4.5:1** (AA), pro velký text (18pt+): **3:1**

**Focus states:**
```css
/* globals.css — výchozí focus ring pro přístupnost */
*:focus-visible {
  outline: 2px solid #E8A849;
  outline-offset: 2px;
  border-radius: 4px;
}
```

**Touch targets:**
- Minimální velikost: **44×44px** pro všechny interaktivní prvky na mobile
- Buttony: výchozí MUI `size="large"` splňuje (48px výška)
- Nav links: přidat `minHeight: 44` na mobile

**ARIA:**
- Drawer toggle: `aria-label` pro otevření a zavření menu (viz Header.tsx — již implementováno)
- Candidate cards: obrázek musí mít smysluplný `alt` text s jménem kandidáta
- Vote counter: pokud se aktualizuje live, použít `aria-live="polite"`
- Countdown: `aria-live="off"` (sekundy — příliš časté pro screen reader)

**Pohyb (Reduced Motion):**
```tsx
// Framer Motion — respektovat prefers-reduced-motion
import { useReducedMotion } from "framer-motion";

function AnimatedCard() {
  const reduceMotion = useReducedMotion();
  return (
    <motion.div
      whileHover={reduceMotion ? {} : { scale: 1.02, y: -4 }}
    >
      ...
    </motion.div>
  );
}
```

```css
/* globals.css — fallback pro CSS animace */
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
```

---

*Tento dokument je živý — aktualizuj při každé větší změně designu nebo přidání nové sekce.*
