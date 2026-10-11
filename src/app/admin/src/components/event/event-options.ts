import type { EventStatusValue, EventTypeValue } from '../../generated';
import { normalizeDateValue } from '../../utils/date';
import { EVENT_STATUS_NAMES, EVENT_TYPE_NAMES, toOptions } from '../../utils/enum-names';

export const EVENT_TYPE_OPTIONS = toOptions(EVENT_TYPE_NAMES);

export const EVENT_STATUS_OPTIONS = toOptions(EVENT_STATUS_NAMES);

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
