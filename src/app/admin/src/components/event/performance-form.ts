import type { RequestSetlistItem, RequestSongPerformance, SetlistItem, SongPerformance } from '../../generated';
import { reorderItems } from '../sortable';

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

// 指定した複数の披露へ同じ共演者をまとめて追加する. 既に付いている披露はそのまま
export const addCoVocalistToPerformances = (
  performances: PerformanceForm[],
  performanceIds: ReadonlySet<string>,
  person: { personId: string; name: string },
): PerformanceForm[] =>
  performances.reduce(
    (acc, performance, index) =>
      performanceIds.has(performance.performanceId) ? addCoVocalist(acc, index, person) : acc,
    performances,
  );

// イベント内で付いている共演者を、初出順に重複なく並べる
export const collectCoVocalists = (performances: PerformanceForm[]): { personId: string; name: string }[] => {
  const persons = new Map<string, { personId: string; name: string }>();
  for (const person of performances.flatMap((performance) => performance.coVocalists)) {
    if (!persons.has(person.personId)) {
      persons.set(person.personId, { personId: person.personId, name: person.name });
    }
  }

  return [...persons.values()];
};

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

export const moveCoVocalist = (
  performances: PerformanceForm[],
  performanceIndex: number,
  fromIndex: number,
  toIndex: number,
): PerformanceForm[] =>
  updateCoVocalists(performances, performanceIndex, (coVocalists) => reorderItems(coVocalists, fromIndex, toIndex));

// まだどの項目にも紐づいていない楽曲披露を、披露順に 1 件 1 項目でセットリスト末尾へ足す
export const appendUnassignedPerformances = (
  setlist: SetlistItemForm[],
  performances: PerformanceForm[],
): SetlistItemForm[] => {
  const assignedIds = new Set(setlist.flatMap((item) => item.performanceIds));
  const added = performances
    .filter((performance) => !assignedIds.has(performance.performanceId))
    .map((performance) => ({ setlistItemId: newId(), label: '', performanceIds: [performance.performanceId] }));

  return [...setlist, ...added];
};

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
