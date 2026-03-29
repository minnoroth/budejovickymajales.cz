import { Box, Container, Divider, Typography } from "@mui/material";
import SocialLinks from "@/components/SocialLinks";

const FESTIVAL_LINKS = [
  { label: "O festivalu", href: "/o-festivalu" },
  { label: "Program", href: "/program" },
  { label: "FAQ", href: "/faq" },
  { label: "Králové", href: "/kralove" },
  { label: "Partneři", href: "/partneri" },
];

export default function Footer() {
  const year = new Date().getFullYear();

  return (
    <Box component="footer" sx={{ bgcolor: "background.paper", mt: "auto" }}>
      <Divider sx={{ borderColor: "rgba(255,255,255,0.08)" }} />
      <Container maxWidth="lg" sx={{ py: { xs: 4, sm: 5 } }}>
        <Box
          sx={{
            display: "flex",
            flexDirection: { xs: "column", sm: "row" },
            alignItems: { xs: "center", sm: "flex-start" },
            justifyContent: "space-between",
            gap: 4,
          }}
        >
          {/* Brand */}
          <Box sx={{ textAlign: { xs: "center", sm: "left" } }}>
            <Typography
              component="a"
              href="/"
              variant="h6"
              sx={{
                fontWeight: 800,
                color: "primary.main",
                textDecoration: "none",
                letterSpacing: "-0.01em",
                display: "block",
                mb: 0.5,
              }}
            >
              Budějovický Majáles
            </Typography>
            <Typography variant="body2" sx={{ color: "text.secondary" }}>
              Studentský festival v Českých Budějovicích
            </Typography>
          </Box>

          {/* Festival links */}
          <Box sx={{ textAlign: { xs: "center", sm: "left" } }}>
            <Typography
              variant="body2"
              sx={{
                color: "text.primary",
                fontWeight: 700,
                mb: 1.5,
                textTransform: "uppercase",
                letterSpacing: "0.08em",
                fontSize: "0.75rem",
              }}
            >
              Festival
            </Typography>
            <Box
              component="ul"
              sx={{ listStyle: "none", m: 0, p: 0, display: "flex", flexDirection: "column", gap: 0.75 }}
            >
              {FESTIVAL_LINKS.map(({ label, href }) => (
                <li key={href}>
                  <Typography
                    component="a"
                    href={href}
                    variant="body2"
                    sx={{
                      color: "text.secondary",
                      textDecoration: "none",
                      fontSize: "0.9rem",
                      transition: "color 0.2s",
                      "&:hover": { color: "primary.main" },
                    }}
                  >
                    {label}
                  </Typography>
                </li>
              ))}
            </Box>
          </Box>

          {/* Social */}
          <Box sx={{ textAlign: { xs: "center", sm: "left" } }}>
            <Typography
              variant="body2"
              sx={{
                color: "text.primary",
                fontWeight: 700,
                mb: 1.5,
                textTransform: "uppercase",
                letterSpacing: "0.08em",
                fontSize: "0.75rem",
              }}
            >
              Sledujte nás
            </Typography>
            <SocialLinks />
          </Box>
        </Box>

        <Divider sx={{ my: 3, borderColor: "rgba(255,255,255,0.06)" }} />

        <Box
          sx={{
            display: "flex",
            flexDirection: { xs: "column", sm: "row" },
            alignItems: "center",
            justifyContent: "space-between",
            gap: 1,
          }}
        >
          <Typography variant="body2" sx={{ color: "text.secondary", fontSize: "0.8rem" }}>
            © {year} Budějovický Majáles. Všechna práva vyhrazena.
          </Typography>
          <Typography
            component="a"
            href="/gdpr"
            variant="body2"
            sx={{
              color: "text.secondary",
              fontSize: "0.8rem",
              textDecoration: "none",
              "&:hover": { color: "primary.main" },
            }}
          >
            Ochrana osobních údajů
          </Typography>
        </Box>
      </Container>
    </Box>
  );
}
