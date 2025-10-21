import { Box, Button, Container, Stack, Typography } from "@mui/material";

export default function Home() {
	return (
		<Container maxWidth="lg">
			<Box
				sx={{
					minHeight: "100vh",
					display: "flex",
					flexDirection: "column",
					justifyContent: "center",
					alignItems: "center",
					textAlign: "center",
					py: 8,
				}}
			>
				<Typography
					variant="h1"
					component="h1"
					sx={{
						fontSize: { xs: "2.5rem", md: "4rem" },
						fontWeight: 700,
						mb: 2,
					}}
				>
					Budějovický Majáles
				</Typography>

				<Typography
					variant="h5"
					component="h2"
					color="text.secondary"
					sx={{ mb: 4, maxWidth: "600px" }}
				>
					Welcome to the official website of Budějovický Majáles festival
				</Typography>

				<Typography
					variant="body1"
					color="text.secondary"
					sx={{ mb: 6, maxWidth: "700px" }}
				>
					This is a modern platform for managing festival content and marketing.
					Built with Next.js, TypeScript, MUI, and Strapi CMS.
				</Typography>

				<Stack direction={{ xs: "column", sm: "row" }} spacing={2}>
					<Button variant="contained" size="large" sx={{ minWidth: 200 }}>
						Learn More
					</Button>
					<Button variant="outlined" size="large" sx={{ minWidth: 200 }}>
						Contact Us
					</Button>
				</Stack>

				<Box sx={{ mt: 8 }}>
					<Typography variant="caption" color="text.secondary">
						© 2025 Budějovický Majáles. All rights reserved.
					</Typography>
				</Box>
			</Box>
		</Container>
	);
}
