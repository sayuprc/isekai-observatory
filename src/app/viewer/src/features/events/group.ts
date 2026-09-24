import type { Event } from './types.js';

export type EventGroups = {
  upcoming: Event[];
  undated: Event[];
  pastByYear: { year: string; events: Event[] }[];
};

const byStartOn = (left: Event, right: Event): number =>
  (left.schedule.startOn ?? '').localeCompare(right.schedule.startOn ?? '');

// 今後の予定は日付昇順、日付未定は別枠、過去は年ごとに日付降順で並べる
export const groupEvents = (events: Event[], base: string): EventGroups => {
  const dated = events.filter(event => event.schedule.startOn !== null);
  const upcoming = dated.filter(event => (event.schedule.startOn ?? '') >= base).sort(byStartOn);
  const past = dated
    .filter(event => (event.schedule.startOn ?? '') < base)
    .sort((left, right) => byStartOn(right, left));
  const years = [...new Set(past.map(event => (event.schedule.startOn ?? '').slice(0, 4)))];

  return {
    upcoming,
    undated: events
      .filter(event => event.schedule.startOn === null)
      .sort((left, right) => left.title.localeCompare(right.title, 'ja')),
    pastByYear: years.map(year => ({ year, events: past.filter(event => event.schedule.startOn?.startsWith(year)) })),
  };
};
