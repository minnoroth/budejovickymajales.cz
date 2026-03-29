import { Box, Container, Divider, Link, Typography } from "@mui/material";
import EmailIcon from "@mui/icons-material/Email";
import PhoneIcon from "@mui/icons-material/Phone";
import InstagramIcon from "@mui/icons-material/Instagram";
import FacebookIcon from "@mui/icons-material/Facebook";
import YouTubeIcon from "@mui/icons-material/YouTube";
import type { Metadata } from "next";
import { fetchStrapi } from "@/lib/strapi";
import type { ContactSetting, StrapiResponse } from "@/lib/strapi-types";

export const metadata: Metadata = {
  title: "Kontakt | Budějovický Majáles",
  description: "Kontaktujte organizátory festivalu Budějovický Majáles.",
};

const FALLBACK: Omit<ContactSetting, "id" | "documentId"> = {
  email: "info@budejovickymajales.cz",
  phone: null,
  instagramUrl: "https://www.instagram.com/budejovickymajales/",
  facebookUrl: "https://www.facebook.com/budejovickymajales",
  youtubeUrl: "https://www.youtube.com/@budejovickymajales",
};

export default async function ContactPage() {
  const result = await fetchStrapi<StrapiResponse<ContactSetting>>("/contact-setting");
  const contact = result?.data ?? FALLBACK;

  const socialLinks = [
    contact.instagramUrl && {
      icon: <InstagramIcon sx={{ color: "primary.main" }} />,
      label: "Instagram",
      href: contact.instagramUrl,
      handle: "@budejovickymajales",
    },
    contact.facebookUrl && {
      icon: <FacebookIcon sx={{ color: "primary.main" }} />,
      label: "Facebook",
      href: contact.facebookUrl,
      handle: "budejovickymajales",
    },
    contact.youtubeUrl && {
      icon: <YouTubeIcon sx={{ color: "primary.main" }} />,
      label: "YouTube",
      href: contact.youtubeUrl,
      handle: "@budejovickymajales",
    },
  ].filter(Boolean) as Array<{
    icon: React.ReactNode;
    label: string;
    href: string;
    handle: string;
  }>;

  return (
    <Box sx={{ minHeight: "100vh", pt: { xs: 10, sm: 12 }, pb: 10 }}>
      <Container maxWidth="sm">
        <Typography
          variant="h1"
          sx={{ mb: 2, fontSize: { xs: "2rem", sm: "3rem", md: "3.5rem" } }}
        >
          Kontakt
        </Typography>
        <Typography
          variant="h3"
          component="p"
          sx={{ color: "primary.main", mb: 6, fontWeight: 400 }}
        >
          Máte dotaz? Napište nám.
        </Typography>

        <Box sx={{ display: "flex", flexDirection: "column", gap: 3 }}>
          {contact.email && (
            <Box sx={{ display: "flex", alignItems: "center", gap: 2 }}>
              <EmailIcon sx={{ color: "primary.main", flexShrink: 0 }} />
              <Box>
                <Typography variant="body2" sx={{ color: "text.secondary", mb: 0.5 }}>
                  E-mail
                </Typography>
                <Link
                  href={`mailto:${contact.email}`}
                  sx={{
                    color: "text.primary",
                    textDecoration: "none",
                    "&:hover": { color: "primary.main" },
                  }}
                >
                  {contact.email}
                </Link>
              </Box>
            </Box>
          )}
          {contact.phone && (
            <Box sx={{ display: "flex", alignItems: "center", gap: 2 }}>
              <PhoneIcon sx={{ color: "primary.main", flexShrink: 0 }} />
              <Box>
                <Typography variant="body2" sx={{ color: "text.secondary", mb: 0.5 }}>
                  Telefon
                </Typography>
                <Link
                  href={`tel:${contact.phone}`}
                  sx={{
                    color: "text.primary",
                    textDecoration: "none",
                    "&:hover": { color: "primary.main" },
                  }}
                >
                  {contact.phone}
                </Link>
              </Box>
            </Box>
          )}
        </Box>

        {socialLinks.length > 0 && (
          <>
            <Divider sx={{ borderColor: "rgba(255,255,255,0.08)", my: 5 }} />
            <Typography
              variant="h2"
              sx={{ mb: 3, fontSize: { xs: "1.25rem", sm: "1.75rem" } }}
            >
              Sociální sítě
            </Typography>
            <Box sx={{ display: "flex", flexDirection: "column", gap: 2.5 }}>
              {socialLinks.map(({ icon, label, href, handle }) => (
                <Box key={label} sx={{ display: "flex", alignItems: "center", gap: 2 }}>
                  {icon}
                  <Box>
                    <Typography variant="body2" sx={{ color: "text.secondary", mb: 0.5 }}>
                      {label}
                    </Typography>
                    <Link
                      href={href}
                      target="_blank"
                      rel="noopener noreferrer"
                      sx={{
                        color: "text.primary",
                        textDecoration: "none",
                        "&:hover": { color: "primary.main" },
                      }}
                    >
                      {handle}
                    </Link>
                  </Box>
                </Box>
              ))}
            </Box>
          </>
        )}
      </Container>
    </Box>
  );
}
