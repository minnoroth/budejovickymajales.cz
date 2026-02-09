const STRAPI_API_URL = process.env.STRAPI_API_URL || "http://localhost:1337";

export async function fetchStrapi<T>(endpoint: string): Promise<T | null> {
  try {
    const res = await fetch(`${STRAPI_API_URL}/api${endpoint}`, {
      next: { revalidate: 60 },
    });
    if (!res.ok) return null;
    return res.json();
  } catch {
    return null;
  }
}
