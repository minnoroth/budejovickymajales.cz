# Představení projektu

## Obecné

- Budeme vytvářet projekt pro neziskovou organizaci Budějovický Majáles
  - Podívej se, co je to za projekt a prozkoumej jejich webové stránky www.budejovickymajales.cz
  - Půjde o přepis jejich stránky společně s marketingovým systémem
- Momentální techstack: Používají wordpress pro jejich stránky.

## Techstack

- DISCLAIMER: U všech verzí si nejprve zkontroluj kompatibilitu s Strapi a poté použij nejvyšší možnou stabilní verzi
- Budeme chtít používat Next.js jako hlavní fullstackový framework
  - To znamená sloučeninu Typescriptu a Reactu, zároveň celý projekt budeme zakládat na MUI
- Jako package manager chci používat yarn
- Pro CMS budeme chtít používat Strapi, jako Open-source řešení
- Databázi budeme časem chtít mít v Postgres a používat REST jako primární komunikaci

## Cíl č.1

- Správně setupnout projekt, abychom ho mohli později rozšiřovat o komplexnější funkcionality
- Vytvořit malé MVP s hlavní stránkou

________________
# Technical conventions and rules

## General Rules

- Primary language: all code comments, identifiers, logs, and UI texts **must be English**; our chat remains **Czech**.
- Think first: ask clarifying questions, sketch a layered solution, _then_ write code.
- Schema model first: Schema is the single source of truth — always try to use types from backend.
- Communication style: keep messages brief, precise, and unambiguous for humans. Skip unnecessary praise and don't feel obligated to agree with everything. Focus on productive, critical workflow - challenge ideas when appropriate.

## Development Principles

### Code Quality and Readability

- Write clean, readable, and maintainable code
- Always consider the entire codebase context before making changes
- Prefer simple and straightforward solutions
- Ensure proper TypeScript typing = **NEVER use `any` type** - always use specific types, `unknown`, or proper generics
  - We also want to avoid using `as` type assertions as much as possible
- Follow the project's existing coding style
- Prefer modern TS and react features.

### Code Duplication

- Avoid code duplication whenever possible
- Before implementing new functionality, check if similar code already exists
- If similar functionality exists, consider refactoring and reusing it
- When adding a new pattern or technology, remove the old implementation

### Scope of Changes

- Focus only on code areas relevant to the current task
- Don't touch code that is not directly related to the task
- Be careful and make only the requested changes
- Avoid large changes in the architecture of functional code unless explicitly requested
- Always consider the impact of changes on other parts of the code

### Problem Solving

- When fixing a problem or bug, first exhaust all possibilities of the existing implementation
- Don't introduce a new pattern or technology without thorough consideration
- If it's necessary to introduce a new implementation, make sure to remove the old one

### Organization

- Keep the codebase clean and organized
- Respect the existing project structure
- Follow file and component naming conventions

### React Components

- Use functional components with hooks
- Prefer TypeScript types over interfaces where appropriate
- Properly type props and all values
- Create small, functional, and reusable components (cognitive complexity should not exceed 3)
- Avoid prop drilling through more than 3 levels
- Consider using React context when it makes sense, but do not overuse it—less is more, and only use it above components that actually consume the context
- Avoid unnecessary re-renders—include render count considerations in your solution design
  - Minimize use of useEffect() as much as possible
  - Use useCallback() and useMemo() when appropriate, and always check the dependencies array
- Propose native React features that we do not use yet, but whose implementation would be more efficient
- For forms, use only react-hook-form and its features—FormContext, setValue, watch, etc.
- Suggest possible component refactoring if it improves code readability, application speed, reduces re-renders, or lowers complexity