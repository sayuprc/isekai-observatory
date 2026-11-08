import type { Media } from '../../generated';

export const mergeMedia = (current: Media[], incoming: Media[]): Media[] => {
  const map = new Map(current.map((item) => [item.mediaId, item]));
  incoming.forEach((item) => map.set(item.mediaId, item));

  return Array.from(map.values()).sort((a, b) => a.title.localeCompare(b.title, 'ja'));
};

export const toErrorMessage = (error: unknown, fallback: string) => {
  if (typeof error === 'object' && error !== null && 'value' in error) {
    const value = (error as { value?: { message?: string; summary?: string; errors?: Array<{ message?: string }> } })
      .value;
    return value?.errors?.[0]?.message ?? value?.message ?? value?.summary ?? fallback;
  }

  return fallback;
};

export const buildMediaDetailUrl = (mediaId: string) => `/media/${mediaId}`;
