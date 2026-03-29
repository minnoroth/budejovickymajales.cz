import type { Metadata } from "next";
import { Box, Container, Typography } from "@mui/material";
import { fetchStrapi } from "@/lib/strapi";
import type { Partner, StrapiListResponse } from "@/lib/strapi-types";
import PartnersContent from "./PartnersContent";
import cs from "@/translations/cs.json";

export const metadata: Metadata = {
  title: `${cs["partners.title"]} | ${cs["meta.title"]}`,
  description: cs["partners.subtitle"],
};

export default async function PartneriPage() {
  const data = await fetchStrapi<StrapiListResponse<Partner>>(
    "/partners?sort[0]=tier:asc&sort[1]=order:asc&populate=logo&pagination[pageSize]=100"
  );

  const partners = data?.data ?? [];

  return (
    <Box
      sx={{
        minHeight: "100svh",
        pt: { xs: 12, sm: 14 },
        pb: { xs: 8, sm: 10 },
      }}
    >
      <Container maxWidth="lg">
        {/* Page header */}
        <Box sx={{ textAlign: "center", mb: { xs: 6, sm: 8 } }}>
          <Typography variant="h1" component="h1" sx={{ mb: 2 }}>
            {cs["partners.title"]}
          </Typography>
          <Typography
            variant="h3"
            component="p"
            sx={{ color: "primary.main", fontWeight: 400 }}
          >
            {cs["partners.subtitle"]}
          </Typography>
        </Box>

        <PartnersContent partners={partners} />
      </Container>
    </Box>
  );
}
