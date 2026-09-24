import { describe, expect, it } from 'bun:test';
import { groupEvents } from './group';
import { eventShortDate, type Event } from './types';

const event = (title: string, startOn: string | null, endOn: string | null = null): Event => ({
  eventId: title,
  title,
  description: '',
  typeValue: 1,
  schedule: { startOn, endOn },
  statusValue: 1,
  venues: [],
  media: [],
  sources: [],
  performances: [],
  setlist: [],
});

const titles = (events: Event[]) => events.map(item => item.title);

describe('イベントの一覧グループ', () => {
  const events = [
    event('過去2025-03', '2025-03-01'),
    event('予定2026-12', '2026-12-01'),
    event('未定B', null),
    event('当日', '2026-09-24'),
    event('過去2024', '2024-08-07'),
    event('未定A', null),
    event('過去2025-11', '2025-11-01'),
  ];
  const groups = groupEvents(events, '2026-09-24');

  it('当日以降を今後の予定として日付昇順に並べる', () => {
    expect(titles(groups.upcoming)).toEqual(['当日', '予定2026-12']);
  });

  it('日付未定を別枠にする', () => {
    expect(titles(groups.undated)).toEqual(['未定A', '未定B']);
  });

  it('過去を年ごとに日付降順で並べる', () => {
    expect(groups.pastByYear.map(group => [group.year, titles(group.events)])).toEqual([
      ['2025', ['過去2025-11', '過去2025-03']],
      ['2024', ['過去2024']],
    ]);
  });
});

describe('イベントカードの日付', () => {
  it('年見出しの下では年を省く', () => {
    expect(eventShortDate(event('単日', '2024-08-07'), false)).toBe('08.07');
    expect(eventShortDate(event('期間', '2024-08-07', '2024-08-09'), false)).toBe('08.07 – 08.09');
  });

  it('年をまたぐ期間は年を省かない', () => {
    expect(eventShortDate(event('年跨ぎ', '2024-12-31', '2025-01-01'), false)).toBe('2024.12.31 – 2025.01.01');
  });

  it('日付未定を表示する', () => {
    expect(eventShortDate(event('未定', null), true)).toBe('日付未定');
  });
});
