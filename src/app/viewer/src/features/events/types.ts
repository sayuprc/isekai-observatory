import type { Event as EventModel } from '../../generated/types.gen.js';

export type Event = EventModel;

export function eventDate(event: Event): string | null {
  if (!event.schedule.startOn) return null;
  return event.schedule.endOn ? `${event.schedule.startOn}〜${event.schedule.endOn}` : event.schedule.startOn;
}

// 一覧カード向けの短い日付。年見出しの下では年を省く
export function eventShortDate(event: Event, withYear: boolean): string {
  const { startOn, endOn } = event.schedule;
  if (!startOn) return '日付未定';

  const format = (value: string, year: boolean) => (year ? value : value.slice(5)).replaceAll('-', '.');
  if (!endOn) return format(startOn, withYear);

  const crossesYear = startOn.slice(0, 4) !== endOn.slice(0, 4);
  return `${format(startOn, withYear || crossesYear)} – ${format(endOn, withYear || crossesYear)}`;
}

export function eventTypeName(value: Event['typeValue']): string {
  return ({ 1: 'ライブ', 2: '配信', 3: '個展', 99: 'その他' })[value] ?? 'その他';
}

export function eventStatusName(value: Event['statusValue']): string | null {
  return value === 2 ? '延期' : value === 3 ? '中止' : null;
}
