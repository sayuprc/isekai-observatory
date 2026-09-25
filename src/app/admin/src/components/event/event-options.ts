import type { EventStatusValue, EventTypeValue } from '../../generated';

// フォームの選択肢。登録済みイベントの表示名は API の name を使う
export const EVENT_TYPE_OPTIONS: { value: EventTypeValue; label: string }[] = [
  { value: 1, label: 'ライブ' },
  { value: 2, label: '配信' },
  { value: 3, label: '個展' },
  { value: 4, label: 'ラジオ' },
  { value: 99, label: 'その他' },
];

export const EVENT_STATUS_OPTIONS: { value: EventStatusValue; label: string }[] = [
  { value: 1, label: '通常' },
  { value: 2, label: '延期' },
  { value: 3, label: '中止' },
];

// セットリストはライブと配信だけが持てる
export const allowsSetlist = (typeValue: EventTypeValue): boolean => typeValue === 1 || typeValue === 2;

// 延期・中止のイベントは楽曲披露とセットリストを持てない
export const allowsPerformances = (statusValue: EventStatusValue): boolean => statusValue === 1;
