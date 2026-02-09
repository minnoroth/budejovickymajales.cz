import HeroSection from "@/components/HeroSection";
import { fetchStrapi } from "@/lib/strapi";
import type { StrapiResponse, FestivalSetting } from "@/lib/strapi-types";

export default async function Home() {
  const result =
    await fetchStrapi<StrapiResponse<FestivalSetting>>("/festival-setting");
  const festivalDate = result?.data?.festivalDate ?? null;
  const festivalEndDate = result?.data?.festivalEndDate ?? null;

  return (
    <HeroSection
      festivalDate={festivalDate}
      festivalEndDate={festivalEndDate}
    />
  );
}
