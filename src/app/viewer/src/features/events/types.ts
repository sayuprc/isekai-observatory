import type { Event as EventModel } from '../../generated/types.gen.js';

export type Event = EventModel;

export function eventDate(event: Event): string | null {
  if (!event.schedule.startOn) return null;
  return event.schedule.endOn ? `${event.schedule.startOn}〜${event.schedule.endOn}` : event.schedule.startOn;
}

// ホームのカード向けの短い日付
export function eventShortDate(event: Event): string {
  const { startOn, endOn } = event.schedule;
  if (!startOn) return '日付未定';

  const format = (value: string) => value.replaceAll('-', '.');
  return endOn ? `${format(startOn)} – ${format(endOn)}` : format(startOn);
}

const WEEKDAYS = ['日', '月', '火', '水', '木', '金', '土'];

// 一覧の日付列。年は見出しに出すので月日だけにし、単日は曜日、期間は終了日を添える
export function eventDateColumn(event: Event): { main: string; sub: string } | null {
  const { startOn, endOn } = event.schedule;
  if (!startOn) return null;

  const monthDay = (value: string) => value.slice(5).replace('-', '.');
  if (endOn) {
    const sameYear = startOn.slice(0, 4) === endOn.slice(0, 4);
    return { main: monthDay(startOn), sub: `– ${sameYear ? monthDay(endOn) : endOn.replaceAll('-', '.')}` };
  }

  const weekday = WEEKDAYS[new Date(`${startOn}T12:00:00Z`).getUTCDay()] ?? '';
  return { main: monthDay(startOn), sub: weekday };
}

export function eventTypeName(value: Event['typeValue']): string {
  return ({ 1: 'ライブ', 2: '配信', 3: '個展', 99: 'その他' })[value] ?? 'その他';
}

export function eventStatusName(value: Event['statusValue']): string | null {
  return value === 2 ? '延期' : value === 3 ? '中止' : null;
}
