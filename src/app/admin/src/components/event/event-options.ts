import type { EventStatusValue, EventTypeValue } from '../../generated';

export const EVENT_TYPE_LABELS: Record<EventTypeValue, string> = {
  1: 'ライブ',
  2: '配信',
  3: '個展',
  99: 'その他',
};

export const EVENT_STATUS_LABELS: Record<EventStatusValue, string> = {
  1: '通常',
  2: '延期',
  3: '中止',
};

const EVENT_TYPE_VALUES: EventTypeValue[] = [1, 2, 3, 99];
const EVENT_STATUS_VALUES: EventStatusValue[] = [1, 2, 3];

export const EVENT_TYPE_OPTIONS = EVENT_TYPE_VALUES.map(value => ({ value, label: EVENT_TYPE_LABELS[value] }));
export const EVENT_STATUS_OPTIONS = EVENT_STATUS_VALUES.map(value => ({ value, label: EVENT_STATUS_LABELS[value] }));

// セットリストはライブと配信だけが持てる
export const allowsSetlist = (typeValue: EventTypeValue): boolean => typeValue === 1 || typeValue === 2;

// 延期・中止のイベントは楽曲披露とセットリストを持てない
export const allowsPerformances = (statusValue: EventStatusValue): boolean => statusValue === 1;
