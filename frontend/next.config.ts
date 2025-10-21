import type { NextConfig } from "next";

const nextConfig: NextConfig = {
	/* config options here */
	output: "standalone",
	experimental: {
		outputFileTracingRoot: require("path").join(__dirname, "../"),
	},
};

export default nextConfig;
