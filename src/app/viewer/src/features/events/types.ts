import type { Event as EventModel } from '../../generated/types.gen.js';

export type Event = EventModel;

export function eventDate(event: Event): string | null {
  if (!event.schedule.startOn) return null;
  return event.schedule.endOn ? `${event.schedule.startOn}〜${event.schedule.endOn}` : event.schedule.startOn;
}

const WEEKDAYS = ['日', '月', '火', '水', '木', '金', '土'];

const weekdayOf = (value: string): string => WEEKDAYS[new Date(`${value}T12:00:00Z`).getUTCDay()] ?? '';

// 詳細の開催時期。年月日と曜日を出し、期間は開始日と終了日を別の要素で返す
export function eventDetailDate(event: Event): string[] | null {
  const { startOn, endOn } = event.schedule;
  if (!startOn) return null;

  const format = (value: string) => `${value.replaceAll('-', '.')} (${weekdayOf(value)})`;
  return endOn ? [format(startOn), format(endOn)] : [format(startOn)];
}

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

  return { main, sub: weekdayOf(startOn) };
}

// 通常開催は状態を出さず、延期と中止だけを表示する
export function eventStatusName(status: Event['status']): string | null {
  return status.value === 1 ? null : status.name;
}
