import type { EventSource, Venue } from '../../generated';

export type VenueEntry = {
  venueId: string;
  name: string;
  kindName: string;
};

export type SourceForm = {
  displayName: string;
  url: string;
};

export const toVenueEntry = (venue: Venue): VenueEntry => ({
  venueId: venue.venueId,
  name: venue.name,
  kindName: venue.kind.name,
});

// 同じ開催先は Event 内で重複できないので、追加済みなら何もしない
export const addVenue = (entries: VenueEntry[], venue: Venue): VenueEntry[] =>
  entries.some(entry => entry.venueId === venue.venueId) ? entries : [...entries, toVenueEntry(venue)];

export const toSourceForms = (sources: EventSource[]): SourceForm[] =>
  sources.map(source => ({ displayName: source.displayName, url: source.url }));

export const toSourcesPayload = (sources: SourceForm[]): EventSource[] =>
  sources.map((source, index) => ({
    displayName: source.displayName.trim(),
    url: source.url.trim(),
    orderNo: index + 1,
  }));

export const validateSources = (sources: SourceForm[]): string | null => {
  for (const [index, source] of sources.entries()) {
    if (source.displayName.trim() === '' || source.url.trim() === '') {
      return `出典 ${index + 1} 行目: 表示名と URL の両方が必要です`;
    }
  }

  const urls = sources.map(source => source.url.trim());
  if (new Set(urls).size !== urls.length) {
    return '出典の URL が重複しています';
  }

  return null;
};
