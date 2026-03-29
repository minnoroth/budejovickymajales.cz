import type { Metadata } from "next";
import { Space_Grotesk } from "next/font/google";
import ThemeRegistry from "@/components/ThemeRegistry";
import TranslationProvider from "@/providers/TranslationProvider";
import Header from "@/components/Header";
import Footer from "@/components/Footer";
import cs from "@/translations/cs.json";

const spaceGrotesk = Space_Grotesk({
  subsets: ["latin"],
  variable: "--font-space-grotesk",
  weight: ["300", "400", "500", "600", "700"],
});

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
    <html lang="cs" className={spaceGrotesk.variable}>
      <body>
        <ThemeRegistry>
          <TranslationProvider>
            <Header />
            <main>{children}</main>
            <Footer />
          </TranslationProvider>
        </ThemeRegistry>
      </body>
    </html>
  );
}
