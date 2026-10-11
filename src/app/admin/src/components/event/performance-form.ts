import type { RequestSetlistItem, RequestSongPerformance, SetlistItem, SongPerformance } from '../../generated';
import { reorderItems } from '../sortable';

export type PersonGroupRef = {
  personGroupId: string;
  name: string;
};

// personGroup はグループとして全員で出演したときだけ持つ
export type CoVocalistForm = {
  personId: string;
  name: string;
  creditName: string;
  personGroup: PersonGroupRef | null;
};

// 画面上の共演者の 1 単位. グループで追加したメンバーは 1 つにまとめて扱う
export type CoVocalistUnit =
  | { type: 'person'; coVocalist: CoVocalistForm }
  | { type: 'group'; personGroup: PersonGroupRef; members: CoVocalistForm[] };

export type PersonGroupPreset = PersonGroupRef & {
  members: { personId: string; name: string }[];
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
      personGroup: person.personGroup,
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

// グループのメンバーは最初のメンバーの位置にまとめる
export const toCoVocalistUnits = (coVocalists: CoVocalistForm[]): CoVocalistUnit[] => {
  const units: CoVocalistUnit[] = [];
  const groupUnits = new Map<string, Extract<CoVocalistUnit, { type: 'group' }>>();

  for (const coVocalist of coVocalists) {
    if (coVocalist.personGroup === null) {
      units.push({ type: 'person', coVocalist });
      continue;
    }

    const existing = groupUnits.get(coVocalist.personGroup.personGroupId);
    if (existing) {
      existing.members.push(coVocalist);
      continue;
    }

    const unit = { type: 'group' as const, personGroup: coVocalist.personGroup, members: [coVocalist] };
    groupUnits.set(coVocalist.personGroup.personGroupId, unit);
    units.push(unit);
  }

  return units;
};

const fromCoVocalistUnits = (units: CoVocalistUnit[]): CoVocalistForm[] =>
  units.flatMap((unit) => (unit.type === 'person' ? [unit.coVocalist] : unit.members));

const updateCoVocalistUnits = (
  performances: PerformanceForm[],
  performanceIndex: number,
  updater: (units: CoVocalistUnit[]) => CoVocalistUnit[],
): PerformanceForm[] =>
  updateCoVocalists(performances, performanceIndex, (coVocalists) =>
    fromCoVocalistUnits(updater(toCoVocalistUnits(coVocalists))),
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
      : [...coVocalists, { personId: person.personId, name: person.name, creditName: '', personGroup: null }],
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

// グループのメンバーを末尾にまとめて追加する. 同じ披露に同じグループは重ねない
// 個別に付いていたメンバーは、グループとしての出演に置き換える
export const addPersonGroup = (
  performances: PerformanceForm[],
  performanceIndex: number,
  personGroup: PersonGroupPreset,
): PerformanceForm[] =>
  updateCoVocalists(performances, performanceIndex, (coVocalists) => {
    if (coVocalists.some((item) => item.personGroup?.personGroupId === personGroup.personGroupId)) {
      return coVocalists;
    }

    const memberIds = new Set(personGroup.members.map((member) => member.personId));
    const ref = { personGroupId: personGroup.personGroupId, name: personGroup.name };

    return [
      ...coVocalists.filter((item) => !memberIds.has(item.personId)),
      ...personGroup.members.map((member) => ({
        personId: member.personId,
        name: member.name,
        creditName: '',
        personGroup: ref,
      })),
    ];
  });

export const addPersonGroupToPerformances = (
  performances: PerformanceForm[],
  performanceIds: ReadonlySet<string>,
  personGroup: PersonGroupPreset,
): PerformanceForm[] =>
  performances.reduce(
    (acc, performance, index) =>
      performanceIds.has(performance.performanceId) ? addPersonGroup(acc, index, personGroup) : acc,
    performances,
  );

// イベント内で個別に付いている共演者を、初出順に重複なく並べる
export const collectCoVocalists = (performances: PerformanceForm[]): { personId: string; name: string }[] => {
  const persons = new Map<string, { personId: string; name: string }>();
  for (const person of performances.flatMap((performance) => performance.coVocalists)) {
    if (person.personGroup === null && !persons.has(person.personId)) {
      persons.set(person.personId, { personId: person.personId, name: person.name });
    }
  }

  return [...persons.values()];
};

// イベント内で付いているグループを、初出時のメンバー構成で重複なく並べる
export const collectPersonGroups = (performances: PerformanceForm[]): PersonGroupPreset[] => {
  const groups = new Map<string, PersonGroupPreset>();
  for (const unit of performances.flatMap((performance) => toCoVocalistUnits(performance.coVocalists))) {
    if (unit.type === 'group' && !groups.has(unit.personGroup.personGroupId)) {
      groups.set(unit.personGroup.personGroupId, {
        ...unit.personGroup,
        members: unit.members.map((member) => ({ personId: member.personId, name: member.name })),
      });
    }
  }

  return [...groups.values()];
};

// 以降の unitIndex は toCoVocalistUnits の並びでの位置
// クレジット名は個別の共演者だけが持つ
export const setCreditName = (
  performances: PerformanceForm[],
  performanceIndex: number,
  unitIndex: number,
  creditName: string,
): PerformanceForm[] =>
  updateCoVocalistUnits(performances, performanceIndex, (units) =>
    units.map((unit, index) =>
      index === unitIndex && unit.type === 'person'
        ? { ...unit, coVocalist: { ...unit.coVocalist, creditName } }
        : unit,
    ),
  );

// グループはメンバーごとまとめて外す
export const removeCoVocalist = (
  performances: PerformanceForm[],
  performanceIndex: number,
  unitIndex: number,
): PerformanceForm[] =>
  updateCoVocalistUnits(performances, performanceIndex, (units) => units.filter((_, index) => index !== unitIndex));

export const moveCoVocalist = (
  performances: PerformanceForm[],
  performanceIndex: number,
  fromIndex: number,
  toIndex: number,
): PerformanceForm[] =>
  updateCoVocalistUnits(performances, performanceIndex, (units) => reorderItems(units, fromIndex, toIndex));

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
      personGroupId: person.personGroup?.personGroupId ?? null,
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
