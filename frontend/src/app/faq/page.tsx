import { Box, Container, Typography } from "@mui/material";
import type { Metadata } from "next";
import { fetchStrapi } from "@/lib/strapi";
import type { FaqItem, StrapiListResponse } from "@/lib/strapi-types";
import FaqAccordion from "./FaqAccordion";

export const metadata: Metadata = {
  title: "FAQ | Budějovický Majáles",
  description: "Časté otázky k festivalu Budějovický Majáles.",
};

export default async function FaqPage() {
  const result = await fetchStrapi<StrapiListResponse<FaqItem>>(
    "/faq-items?sort=order:asc&pagination[pageSize]=50"
  );
  const items = result?.data ?? [];

  return (
    <Box sx={{ minHeight: "100vh", pt: { xs: 10, sm: 12 }, pb: 10 }}>
      <Container maxWidth="md">
        <Typography
          variant="h1"
          sx={{ mb: 2, fontSize: { xs: "2rem", sm: "3rem", md: "3.5rem" } }}
        >
          FAQ
        </Typography>
        <Typography
          variant="h3"
          component="p"
          sx={{ color: "primary.main", mb: 6, fontWeight: 400 }}
        >
          Časté otázky
        </Typography>
        <FaqAccordion items={items} />
      </Container>
    </Box>
  );
}
