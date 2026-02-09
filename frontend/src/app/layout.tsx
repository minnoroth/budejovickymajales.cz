import type { Metadata } from "next";
import ThemeRegistry from "@/components/ThemeRegistry";
import TranslationProvider from "@/providers/TranslationProvider";
import cs from "@/translations/cs.json";

export const metadata: Metadata = {
  title: cs["meta.title"],
  description: cs["meta.description"],
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="cs">
      <body>
        <ThemeRegistry>
          <TranslationProvider>{children}</TranslationProvider>
        </ThemeRegistry>
      </body>
    </html>
  );
}
