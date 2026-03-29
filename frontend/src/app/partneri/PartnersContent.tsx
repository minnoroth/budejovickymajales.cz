"use client";

import Image from "next/image";
import { Box, Grid, Typography } from "@mui/material";
import { motion } from "framer-motion";
import type { Partner } from "@/lib/strapi-types";
import cs from "@/translations/cs.json";

type Props = {
  partners: Partner[];
};

function getStrapiMediaUrl(url: string | undefined): string | null {
  if (!url) return null;
  if (url.startsWith("http")) return url;
  const base = process.env.NEXT_PUBLIC_STRAPI_URL ?? "http://localhost:1337";
  return `${base}${url}`;
}

const fadeIn = {
  hidden: { opacity: 0, y: 20 },
  visible: {
    opacity: 1,
    y: 0,
    transition: { duration: 0.5, ease: "easeOut" as const },
  },
};

const stagger = {
  hidden: {},
  visible: {
    transition: { staggerChildren: 0.08 },
  },
};

type PartnerCardProps = {
  partner: Partner;
  logoMaxHeight: number;
};

function PartnerCard({ partner, logoMaxHeight }: PartnerCardProps) {
  const logoUrl = getStrapiMediaUrl(partner.logo?.url);

  const card = (
    <Box
      sx={{
        bgcolor: "rgba(255,255,255,0.05)",
        border: "1px solid rgba(232,168,73,0.2)",
        borderRadius: 2,
        p: 3,
        display: "flex",
        flexDirection: "column",
        alignItems: "center",
        justifyContent: "center",
        gap: 1.5,
        height: "100%",
        transition: "border-color 0.2s ease, box-shadow 0.2s ease",
        "&:hover": {
          borderColor: "primary.main",
          boxShadow: "0 4px 20px rgba(232,168,73,0.12)",
        },
      }}
    >
      {logoUrl ? (
        <Box
          sx={{
            position: "relative",
            width: "100%",
            height: logoMaxHeight,
          }}
        >
          <Image
            src={logoUrl}
            alt={partner.logo?.alternativeText ?? partner.name}
            fill
            sizes="(max-width: 600px) 45vw, (max-width: 900px) 30vw, 20vw"
            style={{ objectFit: "contain" }}
          />
        </Box>
      ) : (
        <Box
          sx={{
            height: logoMaxHeight,
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
          }}
        >
          <Typography
            sx={{
              fontWeight: 700,
              color: "text.secondary",
              fontSize: "0.9rem",
              textAlign: "center",
            }}
          >
            {partner.name}
          </Typography>
        </Box>
      )}

      {logoUrl && (
        <Typography
          variant="body2"
          sx={{
            color: "text.secondary",
            fontSize: "0.8rem",
            textAlign: "center",
          }}
        >
          {partner.name}
        </Typography>
      )}

      {partner.websiteUrl && (
        <Typography
          variant="body2"
          sx={{
            color: "primary.main",
            fontSize: "0.75rem",
            fontWeight: 600,
            textAlign: "center",
          }}
        >
          {cs["partners.visit"]} →
        </Typography>
      )}
    </Box>
  );

  if (partner.websiteUrl) {
    return (
      <Box
        component="a"
        href={partner.websiteUrl}
        target="_blank"
        rel="noopener noreferrer"
        sx={{ display: "block", textDecoration: "none", height: "100%" }}
      >
        {card}
      </Box>
    );
  }

  return card;
}

type SectionConfig = {
  tier: Partner["tier"];
  labelKey: keyof typeof cs;
  columns: { xs: number; sm: number; md: number };
  logoMaxHeight: number;
};

const SECTIONS: SectionConfig[] = [
  {
    tier: "main",
    labelKey: "partners.main",
    columns: { xs: 6, sm: 4, md: 4 },
    logoMaxHeight: 80,
  },
  {
    tier: "media",
    labelKey: "partners.media",
    columns: { xs: 6, sm: 4, md: 3 },
    logoMaxHeight: 50,
  },
  {
    tier: "other",
    labelKey: "partners.other",
    columns: { xs: 4, sm: 3, md: 2 },
    logoMaxHeight: 40,
  },
];

export default function PartnersContent({ partners }: Props) {
  if (partners.length === 0) {
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
          {cs["partners.emptyTitle"]}
        </Typography>
      </Box>
    );
  }

  return (
    <Box sx={{ display: "flex", flexDirection: "column", gap: { xs: 8, sm: 10 } }}>
      {SECTIONS.map(({ tier, labelKey, columns, logoMaxHeight }) => {
        const tierPartners = partners.filter((p) => p.tier === tier);
        if (tierPartners.length === 0) return null;

        return (
          <motion.div
            key={tier}
            variants={fadeIn}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true, margin: "-80px" }}
          >
            <Typography
              variant="h2"
              sx={{
                mb: { xs: 3, sm: 4 },
                fontSize: { xs: "1.5rem", sm: "2rem", md: "2.5rem" },
              }}
            >
              {cs[labelKey]}
            </Typography>

            <motion.div variants={stagger} initial="hidden" whileInView="visible" viewport={{ once: true }}>
              <Grid container spacing={{ xs: 2, sm: 3 }}>
                {tierPartners.map((partner) => (
                  <Grid key={partner.id} size={columns}>
                    <motion.div variants={fadeIn} style={{ height: "100%" }}>
                      <PartnerCard
                        partner={partner}
                        logoMaxHeight={logoMaxHeight}
                      />
                    </motion.div>
                  </Grid>
                ))}
              </Grid>
            </motion.div>
          </motion.div>
        );
      })}
    </Box>
  );
}
