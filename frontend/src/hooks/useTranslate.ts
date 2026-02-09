import { useContext } from "react";
import { TranslationContext } from "@/providers/TranslationProvider";
import type { TranslationKey } from "@/translations/types";

export default function useTranslate() {
	const translations = useContext(TranslationContext);

	return function t(key: TranslationKey): string {
		return translations[key] ?? key;
	};
}
