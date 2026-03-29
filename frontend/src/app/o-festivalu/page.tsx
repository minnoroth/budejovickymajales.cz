import { Box, Container, Typography } from "@mui/material";
import type { Metadata } from "next";
import ReactMarkdown from "react-markdown";
import { fetchStrapi } from "@/lib/strapi";
import type { Page, StrapiListResponse } from "@/lib/strapi-types";

export async function generateMetadata(): Promise<Metadata> {
  const result = await fetchStrapi<StrapiListResponse<Page>>(
    "/pages?filters[slug][$eq]=o-festivalu&pagination[pageSize]=1"
  );
  const page = result?.data?.[0];
  return {
    title: page?.metaTitle ?? "O festivalu | Budějovický Majáles",
    description:
      page?.metaDescription ??
      "Budějovický Majáles je největší studentský festival v jižních Čechách.",
  };
}

export default async function AboutPage() {
  const result = await fetchStrapi<StrapiListResponse<Page>>(
    "/pages?filters[slug][$eq]=o-festivalu&pagination[pageSize]=1"
  );
  const page = result?.data?.[0];

  return (
    <Box sx={{ minHeight: "100vh", pt: { xs: 10, sm: 12 }, pb: 10 }}>
      <Container maxWidth="md">
        <Typography
          variant="h1"
          sx={{ mb: 2, fontSize: { xs: "2rem", sm: "3rem", md: "3.5rem" } }}
        >
          {page?.title ?? "O festivalu"}
        </Typography>
        {page?.subtitle && (
          <Typography
            variant="h3"
            component="p"
            sx={{ color: "primary.main", mb: 6, fontWeight: 400 }}
          >
            {page.subtitle}
          </Typography>
        )}

        {page?.content ? (
          <Box
            sx={{
              "& p": { color: "text.secondary", fontSize: "1.1rem", lineHeight: 1.8, mb: 3 },
              "& h2": { fontSize: { xs: "1.5rem", sm: "2rem" }, mt: 5, mb: 2 },
              "& h3": { fontSize: { xs: "1.25rem", sm: "1.5rem" }, mt: 4, mb: 1.5 },
              "& strong": { color: "text.primary" },
              "& hr": { borderColor: "rgba(255,255,255,0.08)", my: 4 },
              "& ul, & ol": { color: "text.secondary", pl: 3, mb: 3, lineHeight: 1.8 },
            }}
          >
            <ReactMarkdown>{page.content}</ReactMarkdown>
          </Box>
        ) : (
          <Typography variant="body1" sx={{ color: "text.secondary", fontSize: "1.1rem", lineHeight: 1.8 }}>
            Informace o festivalu budou brzy dostupné.
          </Typography>
        )}
      </Container>
    </Box>
  );
}
