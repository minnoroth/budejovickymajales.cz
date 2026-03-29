import { Box, Container, Divider, Typography } from "@mui/material";
import type { Metadata } from "next";
import { fetchStrapi } from "@/lib/strapi";
import type { ProgramEvent, StrapiListResponse } from "@/lib/strapi-types";

export const metadata: Metadata = {
  title: "Program | Budějovický Majáles",
  description: "Program festivalu Budějovický Majáles 2026 — lineup, harmonogram po dnech.",
};

function formatEventDate(dateStr: string): string {
  return new Intl.DateTimeFormat("cs", {
    weekday: "long",
    day: "numeric",
    month: "long",
    hour: "2-digit",
    minute: "2-digit",
  }).format(new Date(dateStr));
}

function groupByDay(events: ProgramEvent[]): Map<string, ProgramEvent[]> {
  const map = new Map<string, ProgramEvent[]>();
  for (const event of events) {
    const day = new Date(event.date).toLocaleDateString("cs", {
      weekday: "long",
      day: "numeric",
      month: "long",
    });
    const existing = map.get(day) ?? [];
    map.set(day, [...existing, event]);
  }
  return map;
}

export default async function ProgramPage() {
  const result = await fetchStrapi<StrapiListResponse<ProgramEvent>>(
    "/program-events?sort[0]=date:asc&sort[1]=order:asc&pagination[pageSize]=100"
  );
  const events = result?.data ?? [];

  return (
    <Box sx={{ minHeight: "100vh", pt: { xs: 10, sm: 12 }, pb: 10 }}>
      <Container maxWidth="md">
        <Typography
          variant="h1"
          sx={{ mb: 2, fontSize: { xs: "2rem", sm: "3rem", md: "3.5rem" } }}
        >
          Program
        </Typography>
        <Typography
          variant="h3"
          component="p"
          sx={{ color: "primary.main", mb: 6, fontWeight: 400 }}
        >
          31. května – 5. června 2026
        </Typography>

        {events.length === 0 ? (
          <Box
            sx={{
              border: "1px solid",
              borderColor: "rgba(232,168,73,0.3)",
              borderRadius: 3,
              p: { xs: 3, sm: 5 },
              textAlign: "center",
              bgcolor: "rgba(232,168,73,0.05)",
            }}
          >
            <Typography
              variant="h2"
              sx={{
                mb: 2,
                fontSize: { xs: "1.25rem", sm: "1.75rem" },
                color: "primary.main",
              }}
            >
              Program bude brzy zveřejněn
            </Typography>
            <Typography variant="body1" sx={{ color: "text.secondary", lineHeight: 1.8 }}>
              Pracujeme na finálním programu letošního ročníku. Sledujte naše
              sociální sítě, abyste byli první, kdo se dozví o vystupujících.
            </Typography>
          </Box>
        ) : (
          <Box sx={{ display: "flex", flexDirection: "column", gap: 6 }}>
            {Array.from(groupByDay(events)).map(([day, dayEvents]) => (
              <Box key={day}>
                <Typography
                  variant="h2"
                  sx={{
                    mb: 3,
                    fontSize: { xs: "1.25rem", sm: "1.75rem" },
                    color: "primary.main",
                    textTransform: "capitalize",
                  }}
                >
                  {day}
                </Typography>
                <Box sx={{ display: "flex", flexDirection: "column", gap: 2 }}>
                  {dayEvents.map((event) => (
                    <Box
                      key={event.id}
                      sx={{
                        bgcolor: "background.paper",
                        borderRadius: 2,
                        border: "1px solid rgba(255,255,255,0.06)",
                        p: { xs: 2.5, sm: 3 },
                      }}
                    >
                      <Box
                        sx={{
                          display: "flex",
                          justifyContent: "space-between",
                          alignItems: "flex-start",
                          gap: 2,
                          flexWrap: "wrap",
                        }}
                      >
                        <Typography variant="h3" sx={{ fontSize: { xs: "1.1rem", sm: "1.25rem" } }}>
                          {event.title}
                        </Typography>
                        <Typography
                          variant="body2"
                          sx={{ color: "primary.main", fontWeight: 600, flexShrink: 0 }}
                        >
                          {new Date(event.date).toLocaleTimeString("cs", {
                            hour: "2-digit",
                            minute: "2-digit",
                          })}
                          {event.endDate &&
                            ` – ${new Date(event.endDate).toLocaleTimeString("cs", {
                              hour: "2-digit",
                              minute: "2-digit",
                            })}`}
                        </Typography>
                      </Box>
                      {event.stage && (
                        <Typography variant="body2" sx={{ color: "text.secondary", mt: 0.5 }}>
                          {event.stage}
                        </Typography>
                      )}
                      {event.description && (
                        <Typography
                          variant="body2"
                          sx={{ color: "text.secondary", mt: 1.5, lineHeight: 1.6 }}
                        >
                          {event.description}
                        </Typography>
                      )}
                    </Box>
                  ))}
                </Box>
                <Divider sx={{ borderColor: "rgba(255,255,255,0.06)", mt: 4 }} />
              </Box>
            ))}
          </Box>
        )}
      </Container>
    </Box>
  );
}
