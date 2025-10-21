"use client";

import CssBaseline from "@mui/material/CssBaseline";
import { ThemeProvider } from "@mui/material/styles";
import { AppRouterCacheProvider } from "@mui/material-nextjs/v15-appRouter";
import theme from "@/theme/theme";

type ThemeRegistryProps = {
	children: React.ReactNode;
};

/**
 * Theme registry component that wraps the app with MUI theme provider
 * Uses AppRouterCacheProvider for Next.js App Router compatibility
 */
export default function ThemeRegistry({ children }: ThemeRegistryProps) {
	return (
		<AppRouterCacheProvider>
			<ThemeProvider theme={theme}>
				<CssBaseline />
				{children}
			</ThemeProvider>
		</AppRouterCacheProvider>
	);
}
