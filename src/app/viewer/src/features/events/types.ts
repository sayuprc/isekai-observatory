import type { Event as EventModel } from '../../generated/types.gen.js';

export type Event = EventModel;

export function eventDate(event: Event): string | null {
  if (!event.schedule.startOn) return null;
  return event.schedule.endOn ? `${event.schedule.startOn}〜${event.schedule.endOn}` : event.schedule.startOn;
}

const WEEKDAYS = ['日', '月', '火', '水', '木', '金', '土'];

// 一覧の日付列。単日は曜日、期間は終了日を添える
// 年は一覧では見出しに出すので省き、年見出しのないホームでは付ける
export function eventDateColumn(event: Event, withYear = false): { main: string; sub: string } | null {
  const { startOn, endOn } = event.schedule;
  if (!startOn) return null;

  const dotted = (value: string) => value.replaceAll('-', '.');
  const monthDay = (value: string) => dotted(value.slice(5));
  const main = withYear ? dotted(startOn) : monthDay(startOn);

  // 終了日は開始日と同じ年なら月日だけにする
  if (endOn) {
    const sameYear = startOn.slice(0, 4) === endOn.slice(0, 4);
    return { main, sub: `– ${sameYear ? monthDay(endOn) : dotted(endOn)}` };
  }

  const weekday = WEEKDAYS[new Date(`${startOn}T12:00:00Z`).getUTCDay()] ?? '';
  return { main, sub: weekday };
}

export function eventTypeName(value: Event['typeValue']): string {
  return ({ 1: 'ライブ', 2: '配信', 3: '個展', 99: 'その他' })[value] ?? 'その他';
}

export function eventStatusName(value: Event['statusValue']): string | null {
  return value === 2 ? '延期' : value === 3 ? '中止' : null;
}
