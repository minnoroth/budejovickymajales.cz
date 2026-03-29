"use client";

import { Box, Button, Container, Typography } from "@mui/material";
import { motion } from "framer-motion";
import Countdown from "@/components/Countdown";
import SocialLinks from "@/components/SocialLinks";
import useTranslate from "@/hooks/useTranslate";

type HeroSectionProps = {
  festivalDate: string | null;
  festivalEndDate: string | null;
};

function formatDateRange(start: string, end: string | null): string {
  const startDate = new Date(start);
  const endDate = end ? new Date(end) : null;

  const dayFormat = new Intl.DateTimeFormat("cs", { day: "numeric" });
  const dayMonthFormat = new Intl.DateTimeFormat("cs", {
    day: "numeric",
    month: "long",
  });
  const fullFormat = new Intl.DateTimeFormat("cs", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });

  if (!endDate) {
    return fullFormat.format(startDate);
  }

  const sameMonth =
    startDate.getMonth() === endDate.getMonth() &&
    startDate.getFullYear() === endDate.getFullYear();

  if (sameMonth) {
    return `${dayFormat.format(startDate)}–${fullFormat.format(endDate)}`;
  }

  const sameYear = startDate.getFullYear() === endDate.getFullYear();
  if (sameYear) {
    return `${dayMonthFormat.format(startDate)} – ${fullFormat.format(endDate)}`;
  }

  return `${fullFormat.format(startDate)} – ${fullFormat.format(endDate)}`;
}

const stagger = {
  hidden: {},
  visible: {
    transition: { staggerChildren: 0.15 },
  },
};

const fadeUp = {
  hidden: { opacity: 0, y: 30 },
  visible: {
    opacity: 1,
    y: 0,
    transition: { duration: 0.6, ease: "easeOut" as const },
  },
};

export default function HeroSection({
  festivalDate,
  festivalEndDate,
}: HeroSectionProps) {
  const t = useTranslate();
  const showCountdown =
    festivalDate !== null && new Date(festivalDate) > new Date();
  const dateLabel = festivalDate
    ? formatDateRange(festivalDate, festivalEndDate)
    : null;

  return (
    <Box
      sx={{
        position: "relative",
        minHeight: "100svh",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        overflow: "hidden",
      }}
    >
      {/* Background image */}
      <Box
        sx={{
          position: "absolute",
          inset: 0,
          backgroundImage: "url(/hero-bg.avif)",
          backgroundSize: "cover",
          backgroundPosition: "center 30%",
          "&::after": {
            content: '""',
            position: "absolute",
            inset: 0,
            background:
              "linear-gradient(to bottom, rgba(10,15,13,0.6) 0%, rgba(10,15,13,0.85) 100%)",
          },
        }}
      />

      {/* Content */}
      <Container maxWidth="md" sx={{ position: "relative", zIndex: 1, py: 4 }}>
        <motion.div variants={stagger} initial="hidden" animate="visible">
          <Box
            sx={{
              display: "flex",
              flexDirection: "column",
              alignItems: "center",
              textAlign: "center",
              gap: { xs: 3, sm: 4 },
            }}
          >
            <motion.div variants={fadeUp}>
              <Typography variant="h1" component="h1">
                {t("hero.title")}
              </Typography>
            </motion.div>

            {dateLabel && (
              <motion.div variants={fadeUp}>
                <Typography
                  variant="h3"
                  component="p"
                  sx={{ color: "text.secondary" }}
                >
                  {dateLabel}
                </Typography>
              </motion.div>
            )}

            {showCountdown && festivalDate && (
              <motion.div variants={fadeUp}>
                <Typography
                  variant="body1"
                  sx={{
                    color: "text.secondary",
                    mb: 1,
                    textTransform: "uppercase",
                    letterSpacing: "0.15em",
                    fontSize: "0.85rem",
                  }}
                >
                  {t("hero.countdownLabel")}
                </Typography>
                <Countdown targetDate={festivalDate} />
              </motion.div>
            )}

            <motion.div variants={fadeUp}>
              <Button
                variant="contained"
                size="large"
                component="a"
                href="/program"
                sx={{ mt: 1 }}
              >
                {t("hero.ctaProgram")}
              </Button>
            </motion.div>

            <motion.div variants={fadeUp}>
              <SocialLinks />
            </motion.div>
          </Box>
        </motion.div>
      </Container>
    </Box>
  );
}
