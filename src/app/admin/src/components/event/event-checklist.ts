import type { EventStatusValue, EventTypeValue } from '../../generated';
import { allowsPerformances, allowsSetlist } from './event-options';

export type ChecklistState = 'done' | 'missing' | 'notApplicable' | 'optional';

export interface ChecklistItem {
  key: 'venues' | 'sources' | 'performances' | 'setlist' | 'related';
  label: string;
  state: ChecklistState;
  detail: string;
  // 入力するタブ
  tab: 'overview' | 'performances' | 'related';
}

export interface ChecklistInput {
  typeValue: EventTypeValue;
  statusValue: EventStatusValue;
  venueCount: number;
  sourceCount: number;
  performanceCount: number;
  setlistCount: number;
  // セットリストのどの項目にも紐づいていない楽曲披露の数
  unassignedPerformanceCount: number;
  relatedCount: number;
}

// イベントの入力状況。種別と開催状態によって持てない項目は対象外とする
export const buildEventChecklist = (input: ChecklistInput): ChecklistItem[] => {
  const performancesAllowed = allowsPerformances(input.statusValue);
  const setlistAllowed = performancesAllowed && allowsSetlist(input.typeValue);

  const performances = (): ChecklistItem => {
    const base = { key: 'performances', label: '楽曲披露', tab: 'performances' } as const;
    if (!performancesAllowed) return { ...base, state: 'notApplicable', detail: '延期・中止のため対象外' };
    if (input.performanceCount === 0) return { ...base, state: 'missing', detail: '未入力' };
    return { ...base, state: 'done', detail: `${input.performanceCount} 件` };
  };

  const setlist = (): ChecklistItem => {
    const base = { key: 'setlist', label: 'セットリスト', tab: 'performances' } as const;
    if (!setlistAllowed) return { ...base, state: 'notApplicable', detail: '種別または開催状態により対象外' };
    if (input.setlistCount === 0) return { ...base, state: 'missing', detail: '未入力' };
    if (input.unassignedPerformanceCount > 0) {
      return { ...base, state: 'missing', detail: `未紐づけの楽曲披露 ${input.unassignedPerformanceCount} 件` };
    }
    return { ...base, state: 'done', detail: `${input.setlistCount} 項目` };
  };

  return [
    {
      key: 'venues',
      label: '開催先',
      tab: 'overview',
      ...(input.venueCount > 0
        ? { state: 'done', detail: `${input.venueCount} 件` }
        : { state: 'missing', detail: '未登録' }),
    },
    {
      key: 'sources',
      label: '出典',
      tab: 'overview',
      ...(input.sourceCount > 0
        ? { state: 'done', detail: `${input.sourceCount} 件` }
        : { state: 'missing', detail: '未登録' }),
    },
    performances(),
    setlist(),
    {
      key: 'related',
      label: '関連メディア・リリース',
      tab: 'related',
      state: 'optional',
      detail: `任意 · ${input.relatedCount} 件`,
    },
  ];
};
