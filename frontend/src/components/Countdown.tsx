"use client";

import { useState, useEffect } from "react";
import { Box, Typography } from "@mui/material";
import { motion, AnimatePresence } from "framer-motion";
import useTranslate from "@/hooks/useTranslate";

type CountdownProps = {
  targetDate: string;
};

type TimeLeft = {
  days: number;
  hours: number;
  minutes: number;
  seconds: number;
};

const UNIT_KEYS = ["days", "hours", "minutes", "seconds"] as const;

const UNIT_TRANSLATION_KEYS = {
  days: "countdown.days",
  hours: "countdown.hours",
  minutes: "countdown.minutes",
  seconds: "countdown.seconds",
} as const;

function calculateTimeLeft(targetDate: string): TimeLeft {
  const difference = new Date(targetDate).getTime() - Date.now();

  if (difference <= 0) {
    return { days: 0, hours: 0, minutes: 0, seconds: 0 };
  }

  return {
    days: Math.floor(difference / (1000 * 60 * 60 * 24)),
    hours: Math.floor((difference / (1000 * 60 * 60)) % 24),
    minutes: Math.floor((difference / (1000 * 60)) % 60),
    seconds: Math.floor((difference / 1000) % 60),
  };
}

function CountdownUnit({ value, label }: { value: number; label: string }) {
  const formatted = String(value).padStart(2, "0");

  return (
    <Box sx={{ textAlign: "center", minWidth: { xs: 60, sm: 80 } }}>
      <AnimatePresence mode="popLayout">
        <motion.div
          key={formatted}
          initial={{ y: -20, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          exit={{ y: 20, opacity: 0 }}
          transition={{ duration: 0.3 }}
        >
          <Typography
            variant="h2"
            component="span"
            sx={{
              fontVariantNumeric: "tabular-nums",
              color: "primary.main",
            }}
          >
            {formatted}
          </Typography>
        </motion.div>
      </AnimatePresence>
      <Typography
        variant="body2"
        sx={{
          color: "text.secondary",
          mt: 0.5,
          fontSize: { xs: "0.75rem", sm: "0.875rem" },
        }}
      >
        {label}
      </Typography>
    </Box>
  );
}

export default function Countdown({ targetDate }: CountdownProps) {
  const t = useTranslate();
  const [timeLeft, setTimeLeft] = useState<TimeLeft | null>(null);

  useEffect(() => {
    const tick = () => setTimeLeft(calculateTimeLeft(targetDate));
    tick();
    const interval = setInterval(tick, 1000);
    return () => clearInterval(interval);
  }, [targetDate]);

  if (!timeLeft) return null;

  return (
    <Box
      sx={{
        display: "flex",
        gap: { xs: 2, sm: 4 },
        justifyContent: "center",
      }}
    >
      {UNIT_KEYS.map((key) => (
        <CountdownUnit
          key={key}
          value={timeLeft[key]}
          label={t(UNIT_TRANSLATION_KEYS[key])}
        />
      ))}
    </Box>
  );
}
