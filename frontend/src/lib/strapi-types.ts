export type StrapiMedia = {
  id: number;
  documentId: string;
  url: string;
  alternativeText: string | null;
  width: number | null;
  height: number | null;
};

export type KingCandidate = {
  id: number;
  documentId: string;
  name: string;
  school: string;
  schoolTheme: string | null;
  votes: number;
  photo: StrapiMedia | null;
  year: number;
  order: number;
  isActive: boolean;
};

export type Partner = {
  id: number;
  documentId: string;
  name: string;
  logo: StrapiMedia;
  websiteUrl: string | null;
  tier: "main" | "media" | "other";
  order: number;
};

export type StrapiResponse<T> = {
  data: T | null;
  meta: Record<string, unknown>;
};

export type StrapiListResponse<T> = {
  data: T[];
  meta: {
    pagination: {
      page: number;
      pageSize: number;
      pageCount: number;
      total: number;
    };
  };
};

export type FestivalSetting = {
  id: number;
  documentId: string;
  festivalDate: string | null;
  festivalEndDate: string | null;
};

export type Page = {
  id: number;
  documentId: string;
  slug: string;
  title: string;
  subtitle: string | null;
  content: string | null;
  metaTitle: string | null;
  metaDescription: string | null;
};

export type FaqItem = {
  id: number;
  documentId: string;
  question: string;
  answer: string;
  order: number;
};

export type ProgramEvent = {
  id: number;
  documentId: string;
  title: string;
  description: string | null;
  date: string;
  endDate: string | null;
  stage: string | null;
  order: number;
};

export type ContactSetting = {
  id: number;
  documentId: string;
  email: string | null;
  phone: string | null;
  instagramUrl: string | null;
  facebookUrl: string | null;
  youtubeUrl: string | null;
};
