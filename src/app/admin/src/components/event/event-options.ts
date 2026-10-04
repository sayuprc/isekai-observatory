import type { EventStatusValue, EventTypeValue } from '../../generated';
import { normalizeDateValue } from '../../utils/date';

// フォームの選択肢。登録済みイベントの表示名は API の name を使う
export const EVENT_TYPE_OPTIONS: { value: EventTypeValue; label: string }[] = [
  { value: 1, label: 'ライブ' },
  { value: 2, label: '配信' },
  { value: 3, label: '展覧会' },
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

// 開催時期の表示。日付未定、単日、期間を出し分ける
export const formatSchedule = (schedule: { startOn: string | null; endOn: string | null }): string => {
  if (!schedule.startOn) {
    return '未定';
  }
  const startOn = normalizeDateValue(schedule.startOn);
  const endOn = schedule.endOn ? normalizeDateValue(schedule.endOn) : null;
  return endOn && endOn !== startOn ? `${startOn}〜${endOn}` : startOn;
};
