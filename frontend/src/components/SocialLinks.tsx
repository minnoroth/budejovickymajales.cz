"use client";

import { Box, IconButton } from "@mui/material";
import InstagramIcon from "@mui/icons-material/Instagram";
import YouTubeIcon from "@mui/icons-material/YouTube";
import FacebookIcon from "@mui/icons-material/Facebook";
import useTranslate from "@/hooks/useTranslate";
import type { TranslationKey } from "@/translations/types";
import type { SvgIconComponent } from "@mui/icons-material";

type SocialLink = {
  translationKey: TranslationKey;
  href: string;
  icon: SvgIconComponent;
};

const SOCIAL_LINKS: SocialLink[] = [
  {
    translationKey: "social.instagram",
    href: "https://www.instagram.com/budejovickymajales/",
    icon: InstagramIcon,
  },
  {
    translationKey: "social.youtube",
    href: "https://www.youtube.com/@budejovickymajales",
    icon: YouTubeIcon,
  },
  {
    translationKey: "social.facebook",
    href: "https://www.facebook.com/budejovickymajales",
    icon: FacebookIcon,
  },
];

export default function SocialLinks() {
  const t = useTranslate();

  return (
    <Box sx={{ display: "flex", gap: 1 }}>
      {SOCIAL_LINKS.map(({ translationKey, href, icon: Icon }) => (
        <IconButton
          key={translationKey}
          component="a"
          href={href}
          target="_blank"
          rel="noopener noreferrer"
          aria-label={t(translationKey)}
          sx={{
            color: "text.secondary",
            transition: "color 0.2s",
            "&:hover": { color: "primary.main" },
          }}
        >
          <Icon />
        </IconButton>
      ))}
    </Box>
  );
}
