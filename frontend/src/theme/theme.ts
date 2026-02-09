import { createTheme } from "@mui/material/styles";

const theme = createTheme({
  palette: {
    mode: "dark",
    primary: {
      main: "#E8A849",
      light: "#F2C97E",
      dark: "#C48A2A",
      contrastText: "#0A0F0D",
    },
    secondary: {
      main: "#D4614C",
      light: "#E08473",
      dark: "#B04434",
      contrastText: "#FFFFFF",
    },
    background: {
      default: "#0A0F0D",
      paper: "#141C18",
    },
    text: {
      primary: "#F5F0EB",
      secondary: "#B8AFA6",
    },
  },
  typography: {
    fontFamily: [
      "-apple-system",
      "BlinkMacSystemFont",
      '"Segoe UI"',
      "Roboto",
      '"Helvetica Neue"',
      "Arial",
      "sans-serif",
    ].join(","),
    h1: {
      fontSize: "2.5rem",
      fontWeight: 800,
      lineHeight: 1.1,
      letterSpacing: "-0.02em",
      "@media (min-width:600px)": {
        fontSize: "3.5rem",
      },
      "@media (min-width:900px)": {
        fontSize: "5rem",
      },
    },
    h2: {
      fontSize: "1.75rem",
      fontWeight: 700,
      lineHeight: 1.2,
      "@media (min-width:600px)": {
        fontSize: "2.25rem",
      },
      "@media (min-width:900px)": {
        fontSize: "3rem",
      },
    },
    h3: {
      fontSize: "1.25rem",
      fontWeight: 600,
      lineHeight: 1.3,
      "@media (min-width:600px)": {
        fontSize: "1.5rem",
      },
    },
    body1: {
      fontSize: "1rem",
      lineHeight: 1.6,
    },
    button: {
      fontWeight: 700,
      textTransform: "none" as const,
      letterSpacing: "0.02em",
    },
  },
  shape: {
    borderRadius: 12,
  },
  components: {
    MuiButton: {
      styleOverrides: {
        root: {
          borderRadius: 8,
          padding: "10px 24px",
        },
        sizeLarge: {
          padding: "14px 32px",
          fontSize: "1.1rem",
        },
      },
    },
  },
});

export default theme;
