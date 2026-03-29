"use client";

import Image from "next/image";
import { Box, Grid, Typography, Chip } from "@mui/material";
import { motion } from "framer-motion";
import type { KingCandidate } from "@/lib/strapi-types";
import cs from "@/translations/cs.json";

type Props = {
  candidates: KingCandidate[];
};

function getStrapiMediaUrl(url: string | undefined): string | null {
  if (!url) return null;
  if (url.startsWith("http")) return url;
  const base = process.env.NEXT_PUBLIC_STRAPI_URL ?? "http://localhost:1337";
  return `${base}${url}`;
}

function getInitials(name: string): string {
  return name
    .split(" ")
    .map((n) => n[0])
    .join("")
    .toUpperCase()
    .slice(0, 2);
}

const stagger = {
  hidden: {},
  visible: {
    transition: { staggerChildren: 0.1 },
  },
};

const fadeUp = {
  hidden: { opacity: 0, y: 30 },
  visible: {
    opacity: 1,
    y: 0,
    transition: { duration: 0.5, ease: "easeOut" as const },
  },
};

export default function KingsCandidatesGrid({ candidates }: Props) {
  const leader = candidates[0] ?? null;

  if (candidates.length === 0) {
    return (
      <Box
        sx={{
          textAlign: "center",
          py: { xs: 8, sm: 12 },
          px: 3,
        }}
      >
        <Typography
          variant="h2"
          sx={{ mb: 2, fontSize: { xs: "1.5rem", sm: "2rem" } }}
        >
          {cs["kings.emptyTitle"]}
        </Typography>
        <Typography
          variant="body1"
          sx={{ color: "text.secondary", maxWidth: 480, mx: "auto" }}
        >
          {cs["kings.emptyText"]}
        </Typography>
      </Box>
    );
  }

  return (
    <motion.div variants={stagger} initial="hidden" animate="visible">
      <Grid container spacing={{ xs: 2, sm: 3 }}>
        {candidates.map((candidate) => {
          const isLeader = leader !== null && candidate.id === leader.id;
          const photoUrl = getStrapiMediaUrl(candidate.photo?.url);

          return (
            <Grid key={candidate.id} size={{ xs: 6, sm: 4, md: 4 }}>
              <motion.div variants={fadeUp} style={{ height: "100%" }}>
                <Box
                  sx={{
                    height: "100%",
                    bgcolor: "background.paper",
                    borderRadius: 3,
                    overflow: "hidden",
                    border: "2px solid",
                    borderColor: isLeader
                      ? "primary.main"
                      : "rgba(255,255,255,0.06)",
                    transition:
                      "transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease",
                    "&:hover": {
                      transform: "translateY(-4px)",
                      boxShadow: "0 8px 32px rgba(232,168,73,0.15)",
                      borderColor: "primary.main",
                    },
                  }}
                >
                  {/* Photo */}
                  <Box
                    sx={{
                      aspectRatio: "3 / 4",
                      position: "relative",
                      bgcolor: "rgba(255,255,255,0.04)",
                      overflow: "hidden",
                    }}
                  >
                    {photoUrl ? (
                      <Image
                        src={photoUrl}
                        alt={
                          candidate.photo?.alternativeText ?? candidate.name
                        }
                        fill
                        sizes="(max-width: 600px) 50vw, (max-width: 900px) 33vw, 25vw"
                        style={{ objectFit: "cover" }}
                      />
                    ) : (
                      <Box
                        sx={{
                          width: "100%",
                          height: "100%",
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "center",
                          background:
                            "linear-gradient(135deg, rgba(232,168,73,0.15) 0%, rgba(212,97,76,0.1) 100%)",
                        }}
                      >
                        <Typography
                          sx={{
                            fontSize: { xs: "2rem", sm: "3rem" },
                            fontWeight: 900,
                            color: "primary.main",
                            opacity: 0.6,
                          }}
                        >
                          {getInitials(candidate.name)}
                        </Typography>
                      </Box>
                    )}

                    {/* Leader badge */}
                    {isLeader && (
                      <Box
                        sx={{
                          position: "absolute",
                          top: 8,
                          left: 8,
                        }}
                      >
                        <Chip
                          label={`👑 ${cs["kings.leader"]}`}
                          size="small"
                          sx={{
                            bgcolor: "primary.main",
                            color: "primary.contrastText",
                            fontWeight: 700,
                            fontSize: "0.7rem",
                          }}
                        />
                      </Box>
                    )}
                  </Box>

                  {/* Info */}
                  <Box sx={{ p: { xs: 1.5, sm: 2 } }}>
                    <Typography
                      variant="h3"
                      component="h3"
                      sx={{
                        fontWeight: 700,
                        fontSize: { xs: "0.95rem", sm: "1.1rem" },
                        mb: 0.5,
                        lineHeight: 1.3,
                      }}
                    >
                      {isLeader ? `👑 ${candidate.name}` : candidate.name}
                    </Typography>

                    <Typography
                      variant="body2"
                      sx={{
                        color: "text.secondary",
                        fontSize: { xs: "0.75rem", sm: "0.85rem" },
                        mb: candidate.schoolTheme ? 0.25 : 1.5,
                      }}
                    >
                      {candidate.school}
                    </Typography>

                    {candidate.schoolTheme && (
                      <Typography
                        variant="body2"
                        sx={{
                          color: "text.secondary",
                          fontStyle: "italic",
                          fontSize: { xs: "0.7rem", sm: "0.8rem" },
                          mb: 1.5,
                        }}
                      >
                        {candidate.schoolTheme}
                      </Typography>
                    )}

                    {/* Vote count */}
                    <Box
                      sx={{
                        pt: 1.5,
                        borderTop: "1px solid rgba(255,255,255,0.06)",
                        display: "flex",
                        alignItems: "baseline",
                        gap: 0.75,
                      }}
                    >
                      <Typography
                        sx={{
                          fontWeight: 900,
                          fontSize: { xs: "1.4rem", sm: "1.75rem" },
                          lineHeight: 1,
                          background:
                            "linear-gradient(135deg, #E8A849 0%, #F2C97E 100%)",
                          WebkitBackgroundClip: "text",
                          WebkitTextFillColor: "transparent",
                        }}
                      >
                        {candidate.votes.toLocaleString("cs-CZ")}
                      </Typography>
                      <Typography
                        variant="body2"
                        sx={{
                          color: "text.secondary",
                          fontSize: { xs: "0.7rem", sm: "0.8rem" },
                        }}
                      >
                        {cs["kings.votes"]}
                      </Typography>
                    </Box>
                  </Box>
                </Box>
              </motion.div>
            </Grid>
          );
        })}
      </Grid>
    </motion.div>
  );
}
