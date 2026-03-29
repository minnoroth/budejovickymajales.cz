"use client";

import { useState, useEffect } from "react";
import { usePathname } from "next/navigation";
import {
  AppBar,
  Box,
  Drawer,
  IconButton,
  List,
  ListItem,
  ListItemButton,
  ListItemText,
  Toolbar,
  Typography,
} from "@mui/material";
import MenuIcon from "@mui/icons-material/Menu";
import CloseIcon from "@mui/icons-material/Close";
import useTranslate from "@/hooks/useTranslate";
import type { TranslationKey } from "@/translations/types";

type NavItem = {
  labelKey: TranslationKey;
  href: string;
};

const NAV_ITEMS: NavItem[] = [
  { labelKey: "nav.about", href: "/o-festivalu" },
  { labelKey: "nav.program", href: "/program" },
  { labelKey: "nav.faq", href: "/faq" },
  { labelKey: "nav.kings", href: "/kralove" },
  { labelKey: "nav.partners", href: "/partneri" },
  { labelKey: "nav.contact", href: "/kontakt" },
];

export default function Header() {
  const t = useTranslate();
  const pathname = usePathname();
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      setScrolled(window.scrollY > 50);
    };
    window.addEventListener("scroll", handleScroll, { passive: true });
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  return (
    <>
      <AppBar
        position="fixed"
        elevation={0}
        sx={{
          transition: "background 0.3s ease, backdrop-filter 0.3s ease, border-color 0.3s ease",
          background: scrolled
            ? "rgba(10,15,13,0.85)"
            : "linear-gradient(to bottom, rgba(10,15,13,0.95) 0%, rgba(10,15,13,0) 100%)",
          backdropFilter: scrolled ? "blur(16px)" : "none",
          borderBottom: scrolled
            ? "1px solid rgba(232,168,73,0.1)"
            : "1px solid transparent",
        }}
      >
        <Toolbar sx={{ justifyContent: "space-between" }}>
          {/* Logo */}
          <Typography
            component="a"
            href="/"
            variant="h6"
            sx={{
              fontWeight: 800,
              color: "primary.main",
              textDecoration: "none",
              letterSpacing: "-0.01em",
              fontSize: { xs: "1rem", sm: "1.2rem" },
            }}
          >
            Budějovický Majáles
          </Typography>

          {/* Desktop nav */}
          <Box
            component="nav"
            sx={{ display: { xs: "none", md: "flex" }, gap: 1 }}
          >
            {NAV_ITEMS.map(({ labelKey, href }) => {
              const isActive = pathname === href;
              return (
                <Typography
                  key={href}
                  component="a"
                  href={href}
                  sx={{
                    px: 2,
                    py: 1,
                    color: isActive ? "primary.main" : "text.secondary",
                    textDecoration: "none",
                    fontWeight: isActive ? 700 : 500,
                    fontSize: "0.95rem",
                    borderRadius: 1,
                    transition: "color 0.2s",
                    borderBottom: isActive ? "2px solid" : "2px solid transparent",
                    borderColor: isActive ? "primary.main" : "transparent",
                    "&:hover": { color: "primary.main" },
                  }}
                >
                  {t(labelKey)}
                </Typography>
              );
            })}
          </Box>

          {/* Mobile hamburger */}
          <IconButton
            aria-label={t("nav.openMenu")}
            onClick={() => setDrawerOpen(true)}
            sx={{ display: { xs: "flex", md: "none" }, color: "text.primary" }}
          >
            <MenuIcon />
          </IconButton>
        </Toolbar>
      </AppBar>

      {/* Mobile Drawer */}
      <Drawer
        anchor="right"
        open={drawerOpen}
        onClose={() => setDrawerOpen(false)}
        PaperProps={{
          sx: {
            width: 260,
            bgcolor: "background.paper",
            pt: 2,
          },
        }}
      >
        <Box sx={{ display: "flex", justifyContent: "flex-end", px: 2, pb: 1 }}>
          <IconButton
            aria-label={t("nav.closeMenu")}
            onClick={() => setDrawerOpen(false)}
            sx={{ color: "text.secondary" }}
          >
            <CloseIcon />
          </IconButton>
        </Box>
        <List>
          {NAV_ITEMS.map(({ labelKey, href }) => {
            const isActive = pathname === href;
            return (
              <ListItem key={href} disablePadding>
                <ListItemButton
                  component="a"
                  href={href}
                  onClick={() => setDrawerOpen(false)}
                  sx={{
                    px: 3,
                    py: 1.5,
                    borderLeft: isActive ? "3px solid" : "3px solid transparent",
                    borderColor: isActive ? "primary.main" : "transparent",
                  }}
                >
                  <ListItemText
                    primary={t(labelKey)}
                    slotProps={{
                      primary: {
                        fontWeight: isActive ? 700 : 600,
                        fontSize: "1.05rem",
                        color: isActive ? "primary.main" : undefined,
                      },
                    }}
                  />
                </ListItemButton>
              </ListItem>
            );
          })}
        </List>
      </Drawer>
    </>
  );
}
