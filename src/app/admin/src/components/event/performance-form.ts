import type { RequestSetlistItem, RequestSongPerformance, SetlistItem, SongPerformance } from '../../generated';

export type CoVocalistForm = {
  personId: string;
  name: string;
  creditName: string;
};

export type PerformanceForm = {
  performanceId: string;
  songId: string;
  songTitle: string;
  coVocalists: CoVocalistForm[];
};

export type SetlistItemForm = {
  setlistItemId: string;
  label: string;
  performanceIds: string[];
};

export const newId = (): string => crypto.randomUUID();

export const toPerformanceForms = (performances: SongPerformance[]): PerformanceForm[] =>
  performances.map((performance) => ({
    performanceId: performance.performanceId,
    songId: performance.songId,
    songTitle: performance.songTitle,
    coVocalists: performance.coVocalists.map((person) => ({
      personId: person.personId,
      name: person.name,
      creditName: person.creditName ?? '',
    })),
  }));

export const toSetlistItemForms = (setlist: SetlistItem[]): SetlistItemForm[] =>
  setlist.map((item) => ({
    setlistItemId: item.setlistItemId,
    label: item.label ?? '',
    performanceIds: item.performances.map((performance) => performance.performanceId),
  }));

export const addPerformance = (
  performances: PerformanceForm[],
  song: { songId: string; title: string },
): PerformanceForm[] => [
  ...performances,
  { performanceId: newId(), songId: song.songId, songTitle: song.title, coVocalists: [] },
];

const updateCoVocalists = (
  performances: PerformanceForm[],
  performanceIndex: number,
  updater: (coVocalists: CoVocalistForm[]) => CoVocalistForm[],
): PerformanceForm[] =>
  performances.map((performance, index) =>
    index === performanceIndex ? { ...performance, coVocalists: updater(performance.coVocalists) } : performance,
  );

// 同じ披露に同一人物を重複して追加しない
export const addCoVocalist = (
  performances: PerformanceForm[],
  performanceIndex: number,
  person: { personId: string; name: string },
): PerformanceForm[] =>
  updateCoVocalists(performances, performanceIndex, (coVocalists) =>
    coVocalists.some((item) => item.personId === person.personId)
      ? coVocalists
      : [...coVocalists, { personId: person.personId, name: person.name, creditName: '' }],
  );

export const setCreditName = (
  performances: PerformanceForm[],
  performanceIndex: number,
  personIndex: number,
  creditName: string,
): PerformanceForm[] =>
  updateCoVocalists(performances, performanceIndex, (coVocalists) =>
    coVocalists.map((person, index) => (index === personIndex ? { ...person, creditName } : person)),
  );

export const removeCoVocalist = (
  performances: PerformanceForm[],
  performanceIndex: number,
  personIndex: number,
): PerformanceForm[] =>
  updateCoVocalists(performances, performanceIndex, (coVocalists) =>
    coVocalists.filter((_, index) => index !== personIndex),
  );

export const toPerformancesPayload = (performances: PerformanceForm[]): RequestSongPerformance[] =>
  performances.map((performance, index) => ({
    performanceId: performance.performanceId,
    songId: performance.songId,
    orderNo: index + 1,
    coVocalists: performance.coVocalists.map((person, personIndex) => ({
      personId: person.personId,
      creditName: person.creditName.trim() === '' ? null : person.creditName.trim(),
      orderNo: personIndex + 1,
    })),
  }));

export const toSetlistPayload = (setlist: SetlistItemForm[], performances: PerformanceForm[]): RequestSetlistItem[] => {
  const performanceIds = new Set(performances.map((performance) => performance.performanceId));

  return setlist.map((item, index) => ({
    setlistItemId: item.setlistItemId,
    orderNo: index + 1,
    label: item.label.trim() === '' ? null : item.label.trim(),
    performanceIds: item.performanceIds.filter((performanceId) => performanceIds.has(performanceId)),
  }));
};

export const validateSetlistItems = (setlist: SetlistItemForm[]): string | null => {
  for (const [index, item] of setlist.entries()) {
    if (item.label.trim() === '' && item.performanceIds.length === 0) {
      return `セットリスト ${index + 1} 行目: 表示名か楽曲披露のどちらかが必要です`;
    }
  }

  return null;
};
