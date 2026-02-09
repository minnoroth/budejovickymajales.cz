import cs from "./cs.json";

export type TranslationKey = keyof typeof cs;

export type Translations = Record<TranslationKey, string>;
