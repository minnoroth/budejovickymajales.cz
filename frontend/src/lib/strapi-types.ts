export type StrapiResponse<T> = {
  data: T | null;
  meta: Record<string, unknown>;
};

export type FestivalSetting = {
  id: number;
  documentId: string;
  festivalDate: string | null;
  festivalEndDate: string | null;
};
