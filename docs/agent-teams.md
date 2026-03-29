# Agent Teams — Průvodce pro tento projekt

> Tento dokument je určen jak lidem, tak samotným agentům. Pokud jsi agent a čteš toto, dodržuj níže uvedená pravidla a doporučení.

---

## Co jsou Agent Teams?

Agent Teams je experimentální funkce Claude Code (od verze v2.1.32), která umožňuje koordinovat více instancí Claude Code najednou. Jedna session funguje jako **team lead**, který zadává úkoly a koordinuje práci. Ostatní instance jsou **teammates** — pracují nezávisle ve vlastních kontextových oknech a mohou spolu přímo komunikovat.

### Klíčový rozdíl: Subagenti vs. Týmoví agenti

| | Subagenti | Týmoví agenti |
|---|---|---|
| Komunikace | Jen zpět k hlavnímu agentovi | Přímá inter-agent komunikace |
| Kontext | Omezený | Každý má vlastní plný kontext |
| Vhodné pro | Izolované dílčí úkoly | Komplexní, paralelní práci vyžadující spolupráci |
| Cena (tokeny) | Nižší | Vyšší — každý teammate = samostatná instance |

---

## Kdy použít Agent Teams v tomto projektu

### Vhodné případy

- **Paralelní vývoj modulů** — např. jeden teammate pracuje na frontend komponentě, druhý na Strapi API endpointu, třetí píše testy
- **Code review** — jeden reviewer hledá bezpečnostní problémy, druhý výkonnostní, třetí kontroluje TypeScript typy a dodržování konvencí z `AGENTS.md`
- **Průzkum a výzkum** — zjišťování kompatibility knihoven, porovnávání přístupů (každý teammate prozkoumá jinou variantu)
- **Debugging s více hypotézami** — každý teammate testuje jiný možný zdroj problému

### Nevhodné případy

- Sekvenční úkoly (B závisí na výstupu A)
- Úpravy ve stejném souboru (riziko konfliktů)
- Jednoduchá, přímočará práce — zbytečně vysoká cena tokenů

---

## Aktivace

Přidej do `settings.json` nebo nastav jako env proměnnou:

```json
{
  "env": {
    "CLAUDE_CODE_EXPERIMENTAL_AGENT_TEAMS": "1"
  }
}
```

Nebo spusť s příznakem:

```bash
CLAUDE_CODE_EXPERIMENTAL_AGENT_TEAMS=1 claude
```

---

## Architektura týmu

```
Team Lead (hlavní session)
│
├── Shared Task List  (~/.claude/tasks/{team-name}/)
│   ├── task-1: pending
│   ├── task-2: in-progress → Teammate A
│   └── task-3: completed
│
├── Mailbox (inter-agent messaging)
│
├── Teammate A (vlastní kontext, vlastní shell)
├── Teammate B (vlastní kontext, vlastní shell)
└── Teammate C (vlastní kontext, vlastní shell)
```

Každý teammate:
- Načte projektový kontext (`AGENTS.md`, MCP servery, skills)
- Zdědí oprávnění leada
- **Nezíská** historii konverzace leada

---

## Pravidla pro agenty v tomto projektu

Každý agent (lead i teammate) **musí** před zahájením práce prostudovat:

1. **`AGENTS.md`** — technické konvence, pravidla kódu, tech stack, design approach
2. **`README.md`** — přehled projektu
3. **`docs/`** — veškerá projektová dokumentace (tento soubor, architektura apod.)
4. Relevantní části `frontend/src/` nebo `backend/src/` podle přidělené oblasti

### Konvence kódu (shrnutí pro agenty)

- Kód, komentáře, identifikátory, logy: **anglicky**
- TypeScript: **nikdy `any`**, minimalizovat `as` type assertions
- Komponenty: funkcionální, malé (kognitivní složitost ≤ 3), hooky
- Mobile-first: xs → sm → md → lg breakpointy
- Package manager: **yarn**
- Formuláře: pouze **react-hook-form**
- Schéma je source of truth — typy vždy z backendu

---

## Jak strukturovat tým — doporučení

### Velikost týmu

- **Začni s 3–5 teammates** — tokeny rostou lineárně s počtem agentů
- Pro review úkoly: 3 agenti (každý jiné zaměření)
- Pro paralelní vývoj: 1 agent na modul/oblast

### Zadávání úkolů

Každý task by měl:
- Být **samostatně vykonatelný** (žádné skryté závislosti)
- Jasně specifikovat **které soubory** agent vlastní
- Obsahovat **dostatek kontextu** přímo ve spawn promptu

**Špatně:**
```
Implementuj contact form.
```

**Dobře:**
```
Implementuj contact form komponentu.
- Pracuj POUZE v: frontend/src/app/kontakt/
- Použij react-hook-form, MUI TextField a Button
- Formulář odešle POST na /api/contact-settings (viz backend/src/api/contact-setting/)
- Mobile-first, TypeScript strict, žádný `any`
- Po dokončení označ task jako completed
```

### Vlastnictví souborů

Rozděluj práci tak, aby každý teammate vlastnil **jiné soubory** — eliminuje merge konflikty:

```
Teammate A → frontend/src/app/program/
Teammate B → frontend/src/app/faq/
Teammate C → backend/src/api/program-event/
```

---

## Jak se tým sám zlepšuje (self-improvement)

### 1. Aktualizuj paměť po každém týmu

Po dokončení teamové session uložte nové poznatky do `~/.claude/projects/.../memory/`:
- Co fungovalo (dobrá struktura úkolů, správný počet agentů)
- Co nefungovalo (konflikty, závislosti, nevhodné dělení)

### 2. Používej hooks pro quality gates

Nastav hooks v `settings.json` pro automatické akce při teamových událostech:

```json
{
  "hooks": {
    "TeammateIdle": [
      {
        "matcher": "",
        "hooks": [
          {
            "type": "command",
            "command": "echo 'Teammate idle — check task list'"
          }
        ]
      }
    ],
    "TaskCompleted": [
      {
        "matcher": "",
        "hooks": [
          {
            "type": "command",
            "command": "cd /Users/michalmarek/Projects/test/budejovickymajales && yarn type-check"
          }
        ]
      }
    ]
  }
}
```

### 3. Dokumentuj architektonická rozhodnutí

Pokud tým učiní důležité rozhodnutí (nová knihovna, změna struktury), **zapište ho do `docs/`** — ne do paměti, ale do verzovaného souboru, aby měly přístup všechny budoucí sessions.

### 4. Plan approval před implementací

Pro složité úkoly vždy **vyžaduj schválení plánu** než tým začne implementovat. Použij:

```
Vytvořte tým, ale před implementací mi každý teammate předloží svůj plán.
```

---

## Komunikace v týmu

### Lead → Teammate
Automatické přiřazení přes task list, nebo přímou zprávou.

### Teammate → Teammate
Přímá zpráva (bez nutnosti procházet leada) — vhodné pro rychlou koordinaci.

### Interakce člověka s teammates
- **In-process mode**: `Shift+Down` pro přepnutí na konkrétního teammate
- **Split panes mode** (vyžaduje tmux nebo iTerm2): kliknutí na panel

### Stavy tasků

| Stav | Popis |
|---|---|
| `pending` | Čeká na přidělení |
| `in-progress` | Teammate pracuje |
| `completed` | Hotovo — teammate musí explicitně označit |

> **Pozor:** Teammates někdy zapomenou označit task jako `completed`. Lead by měl kontrolovat průběh pravidelně.

---

## Omezení, která musíš znát

- Žádné vnořené týmy — teammates nemohou spawnovat vlastní týmy
- `/resume` a `/rewind` neobnoví teammates po restartu
- Jeden tým na session
- Lead je pevně daný po celou dobu existence týmu
- Vypínání týmu může být pomalé

---

## Příklady pro tento projekt

### Příklad 1: Paralelní code review

```
Vytvoř tým 3 agentů pro review frontend/src/components/:
- Reviewer A: TypeScript typy, dodržení AGENTS.md konvencí
- Reviewer B: Výkon — re-rendery, useEffect závislosti, memoizace
- Reviewer C: Mobile-first, MUI breakpointy, přístupnost
Každý reviewer vytvoří seznam nálezů a na konci ho pošle ostatním.
```

### Příklad 2: Paralelní vývoj nových stránek

```
Vytvoř tým pro implementaci zbývajících stránek festivalu.
Každý teammate vlastní jednu stránku a nepřekračuje do souborů ostatních:
- Teammate A: frontend/src/app/o-festivalu/ + backend/src/api/page/
- Teammate B: frontend/src/app/gdpr/
- Teammate C: frontend/src/app/kontakt/ + backend/src/api/contact-setting/
Všichni dodržují AGENTS.md, mobile-first přístup a TypeScript strict mode.
```

### Příklad 3: Debugging s hypotézami

```
Vytvoř tým 3 investigátorů pro debug problému X.
Každý testuje jinou hypotézu:
- Investigátor A: problém v Strapi REST API (zkontroluj backend/src/api/)
- Investigátor B: problém v Next.js fetch/cache (zkontroluj frontend/src/lib/)
- Investigátor C: problém v TypeScript typech (porovnej strapi-types.ts s API odpovědí)
Po 15 minutách sdílejte nálezy a dohodněte se na příčině.
```

---

## Rychlý checklist pro každý tým

Před spuštěním:
- [ ] Přečetl jsem `AGENTS.md` a `docs/`
- [ ] Úkoly jsou rozděleny bez překrývajících se souborů
- [ ] Každý task má dostatek kontextu ve spawn promptu
- [ ] Tým má 3–5 members (ne víc bez důvodu)

Během práce:
- [ ] Pravidelně kontroluji task list
- [ ] Komunikuji přímo s teammates, ne vždy přes leada
- [ ] Po dokončení označím task jako completed

Po dokončení:
- [ ] Nová architektonická rozhodnutí zdokumented v `docs/`
- [ ] Výrazné poznatky uloženy do paměti projektu
- [ ] Výsledný kód projde `yarn type-check`

---

*Dokument udržuj aktuální — pokud narazíš na nové poznatky o Agent Teams v kontextu tohoto projektu, doplň je sem.*
