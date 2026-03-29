"use client";

import { useState } from "react";
import {
  Accordion,
  AccordionDetails,
  AccordionSummary,
  Box,
  Typography,
} from "@mui/material";
import ExpandMoreIcon from "@mui/icons-material/ExpandMore";
import type { FaqItem } from "@/lib/strapi-types";

type Props = {
  items: FaqItem[];
};

const FALLBACK_ITEMS: FaqItem[] = [
  {
    id: 0,
    documentId: "0",
    question: "Kde se festival koná?",
    answer: "Budějovický Majáles se koná v Českých Budějovicích. Přesné místo konání najdete v programu festivalu.",
    order: 0,
  },
  {
    id: 1,
    documentId: "1",
    question: "Kdy probíhá festival 2026?",
    answer: "Letošní ročník se koná 31. května – 5. června 2026.",
    order: 1,
  },
  {
    id: 2,
    documentId: "2",
    question: "Kde najdu aktuální informace?",
    answer: "Sledujte nás na Instagramu, Facebooku nebo YouTubu — @budejovickymajales.",
    order: 2,
  },
];

export default function FaqAccordion({ items }: Props) {
  const [expanded, setExpanded] = useState<string | false>(false);
  const displayItems = items.length > 0 ? items : FALLBACK_ITEMS;

  const handleChange =
    (panel: string) => (_: React.SyntheticEvent, isExpanded: boolean) => {
      setExpanded(isExpanded ? panel : false);
    };

  return (
    <Box sx={{ display: "flex", flexDirection: "column", gap: 1 }}>
      {displayItems.map((item) => {
        const panel = `panel-${item.id}`;
        return (
          <Accordion
            key={item.id}
            expanded={expanded === panel}
            onChange={handleChange(panel)}
            sx={{
              bgcolor: "background.paper",
              "&:before": { display: "none" },
              borderRadius: "12px !important",
              border: "1px solid",
              borderColor:
                expanded === panel
                  ? "rgba(232,168,73,0.3)"
                  : "rgba(255,255,255,0.06)",
              transition: "border-color 0.2s",
            }}
          >
            <AccordionSummary
              expandIcon={<ExpandMoreIcon sx={{ color: "primary.main" }} />}
              sx={{ px: 3, py: 0.5 }}
            >
              <Typography fontWeight={600}>{item.question}</Typography>
            </AccordionSummary>
            <AccordionDetails sx={{ px: 3, pb: 3 }}>
              <Typography sx={{ color: "text.secondary", lineHeight: 1.7 }}>
                {item.answer}
              </Typography>
            </AccordionDetails>
          </Accordion>
        );
      })}
    </Box>
  );
}
