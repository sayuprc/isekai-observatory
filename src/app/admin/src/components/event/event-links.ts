import type {
  EventRelease,
  EventSource,
  ReleaseGroupReferencedRelease,
  ReleaseGroupSummary,
  Venue,
  VenueKindValue,
} from '../../generated';
import { normalizeDateValue } from '../../utils/date';
import { RELEASE_FORMAT_NAMES } from '../../utils/enum-names';

export type VenueEntry = {
  venueId: string;
  name: string;
  kind: VenueKindValue;
};

export type SourceForm = {
  displayName: string;
  url: string;
};

export const toVenueEntry = (venue: Venue): VenueEntry => ({
  venueId: venue.venueId,
  name: venue.name,
  kind: venue.kind,
});

// 同じ開催先は Event 内で重複できないので、追加済みなら何もしない
export const addVenue = (entries: VenueEntry[], venue: Venue): VenueEntry[] =>
  entries.some((entry) => entry.venueId === venue.venueId) ? entries : [...entries, toVenueEntry(venue)];

// 同じリリースは Event 内で重複できないので、追加済みなら何もしない
export const addRelease = (entries: EventRelease[], release: EventRelease): EventRelease[] =>
  entries.some((entry) => entry.releaseId === release.releaseId) ? entries : [...entries, release];

// リリースグループと傘下の版から、イベントに関連づける候補を組み立てる
export const toEventReleases = (
  releaseGroup: Pick<ReleaseGroupSummary, 'releaseGroupId' | 'title' | 'isDisplay'>,
  releases: ReleaseGroupReferencedRelease[],
): EventRelease[] =>
  releases.map((release) => ({
    releaseId: release.releaseId,
    releaseGroupId: releaseGroup.releaseGroupId,
    releaseGroupTitle: releaseGroup.title,
    name: release.name,
    releasedOn: release.releasedOn,
    isDisplay: releaseGroup.isDisplay && release.isDisplay,
    formatValues: release.formatValues,
  }));

export const releaseLabel = (release: EventRelease): string => {
  const formats = release.formatValues.map((value) => RELEASE_FORMAT_NAMES[value]).join('・');
  const name = release.name === '' ? release.releaseGroupTitle : `${release.releaseGroupTitle} ${release.name}`;

  return `${name} (${normalizeDateValue(release.releasedOn)} / ${formats})`;
};

export const toSourceForms = (sources: EventSource[]): SourceForm[] =>
  sources.map((source) => ({ displayName: source.displayName, url: source.url }));

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

  const urls = sources.map((source) => source.url.trim());
  if (new Set(urls).size !== urls.length) {
    return '出典の URL が重複しています';
  }

  return null;
};
