"use client";

import { createContext, type ReactNode } from "react";
import cs from "@/translations/cs.json";
import type { Translations } from "@/translations/types";

export const TranslationContext = createContext<Translations>(cs);

type TranslationProviderProps = {
	children: ReactNode;
	overrides?: Partial<Translations>;
};

export default function TranslationProvider({
	children,
	overrides,
}: TranslationProviderProps) {
	const translations: Translations = overrides
		? { ...cs, ...overrides }
		: cs;

	return (
		<TranslationContext value={translations}>
			{children}
		</TranslationContext>
	);
}
