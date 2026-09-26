import type { Event } from './types.js';

export type EventGroups = {
  undated: Event[];
  byYear: { year: string; events: Event[] }[];
};

const byStartOn = (left: Event, right: Event): number =>
  (left.schedule.startOn ?? '').localeCompare(right.schedule.startOn ?? '');

// 一覧は日付未定を別枠にし、残りは未来・過去を区別せず年ごとに新しい順で並べる
export const groupEvents = (events: Event[]): EventGroups => {
  const dated = events.filter((event) => event.schedule.startOn !== null).sort((left, right) => byStartOn(right, left));
  const years = [...new Set(dated.map((event) => (event.schedule.startOn ?? '').slice(0, 4)))];

  return {
    undated: events
      .filter((event) => event.schedule.startOn === null)
      .sort((left, right) => left.title.localeCompare(right.title, 'ja')),
    byYear: years.map((year) => ({ year, events: dated.filter((event) => event.schedule.startOn?.startsWith(year)) })),
  };
};

// ホームの今後の予定の候補。基準日以降を日付昇順で返し、表示の最終判断はブラウザ側に任せる
export const upcomingEvents = (events: Event[], base: string): Event[] =>
  events.filter((event) => (event.schedule.startOn ?? '') >= base).sort(byStartOn);
