import type { Metadata } from "next";
import { Box, Container, Typography } from "@mui/material";
import { fetchStrapi } from "@/lib/strapi";
import type { KingCandidate, StrapiListResponse } from "@/lib/strapi-types";
import KingsCandidatesGrid from "./KingsCandidatesGrid";
import cs from "@/translations/cs.json";

export const metadata: Metadata = {
  title: `${cs["kings.title"]} | ${cs["meta.title"]}`,
  description: cs["kings.subtitle"],
};

export default async function KralovePage() {
  const data = await fetchStrapi<StrapiListResponse<KingCandidate>>(
    "/king-candidates?filters[isActive][$eq]=true&sort[0]=votes:desc&sort[1]=order:asc&populate=photo&pagination[pageSize]=20"
  );

  const candidates = data?.data ?? [];

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
            {cs["kings.title"]}
          </Typography>
          <Typography
            variant="h3"
            component="p"
            sx={{ color: "primary.main", fontWeight: 400 }}
          >
            {cs["kings.subtitle"]}
          </Typography>
        </Box>

        <KingsCandidatesGrid candidates={candidates} />
      </Container>
    </Box>
  );
}
