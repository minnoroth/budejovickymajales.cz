import { Box, Container, Typography } from "@mui/material";
import type { Metadata } from "next";
import ReactMarkdown from "react-markdown";
import { fetchStrapi } from "@/lib/strapi";
import type { Page, StrapiListResponse } from "@/lib/strapi-types";

export async function generateMetadata(): Promise<Metadata> {
  const result = await fetchStrapi<StrapiListResponse<Page>>(
    "/pages?filters[slug][$eq]=gdpr&pagination[pageSize]=1"
  );
  const page = result?.data?.[0];
  return {
    title: page?.metaTitle ?? "Ochrana osobních údajů | Budějovický Majáles",
    description:
      page?.metaDescription ??
      "Informace o zpracování osobních údajů festivalu Budějovický Majáles.",
  };
}

export default async function GdprPage() {
  const result = await fetchStrapi<StrapiListResponse<Page>>(
    "/pages?filters[slug][$eq]=gdpr&pagination[pageSize]=1"
  );
  const page = result?.data?.[0];

  return (
    <Box sx={{ minHeight: "100vh", pt: { xs: 10, sm: 12 }, pb: 10 }}>
      <Container maxWidth="md">
        <Typography
          variant="h1"
          sx={{ mb: 2, fontSize: { xs: "2rem", sm: "3rem" } }}
        >
          {page?.title ?? "Ochrana osobních údajů"}
        </Typography>

        {page?.content ? (
          <Box
            sx={{
              mt: 4,
              "& p": { color: "text.secondary", fontSize: "1rem", lineHeight: 1.8, mb: 3 },
              "& h2": { fontSize: { xs: "1.1rem", sm: "1.4rem" }, mt: 5, mb: 2 },
              "& h3": { fontSize: { xs: "1rem", sm: "1.2rem" }, mt: 4, mb: 1.5 },
              "& strong": { color: "text.primary" },
              "& hr": { borderColor: "rgba(255,255,255,0.06)", my: 4 },
            }}
          >
            <ReactMarkdown>{page.content}</ReactMarkdown>
          </Box>
        ) : (
          <Typography
            variant="body2"
            sx={{ color: "text.secondary", mt: 4 }}
          >
            Obsah bude brzy dostupný.
          </Typography>
        )}
      </Container>
    </Box>
  );
}
