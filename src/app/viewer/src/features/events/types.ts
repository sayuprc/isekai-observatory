import type { Event as EventModel } from '../../generated/types.gen.js';

export type Event = EventModel;

export function eventDate(event: Event): string | null {
  if (!event.schedule.startOn) return null;
  return event.schedule.endOn ? `${event.schedule.startOn}〜${event.schedule.endOn}` : event.schedule.startOn;
}

export function eventTypeName(value: Event['typeValue']): string {
  return ({ 1: 'ライブ', 2: '配信', 3: '個展', 99: 'その他' })[value] ?? 'その他';
}

export function eventStatusName(value: Event['statusValue']): string | null {
  return value === 1 ? '延期' : value === 2 ? '中止' : null;
}
