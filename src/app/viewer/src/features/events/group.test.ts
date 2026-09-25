import { describe, expect, it } from 'bun:test';
import { groupEvents, upcomingEvents } from './group';
import { eventDateColumn, eventDetailDate, type Event } from './types';

const event = (title: string, startOn: string | null, endOn: string | null = null): Event => ({
  eventId: title,
  title,
  description: '',
  type: { name: 'ライブ', value: 1 },
  schedule: { startOn, endOn },
  status: { name: '通常', value: 1 },
  venues: [],
  media: [],
  sources: [],
  performances: [],
  setlist: [],
});

const titles = (events: Event[]) => events.map(item => item.title);

const events = [
  event('2025-03', '2025-03-01'),
  event('2026-12', '2026-12-01'),
  event('未定B', null),
  event('2026-09', '2026-09-24'),
  event('2024-08', '2024-08-07'),
  event('未定A', null),
  event('2025-11', '2025-11-01'),
];

describe('イベント一覧のグループ', () => {
  const groups = groupEvents(events);

  it('日付未定を別枠にする', () => {
    expect(titles(groups.undated)).toEqual(['未定A', '未定B']);
  });

  it('未来と過去を区別せず、年ごとに新しい順で並べる', () => {
    expect(groups.byYear.map(group => [group.year, titles(group.events)])).toEqual([
      ['2026', ['2026-12', '2026-09']],
      ['2025', ['2025-11', '2025-03']],
      ['2024', ['2024-08']],
    ]);
  });
});

describe('ホームの今後の予定', () => {
  it('基準日以降を日付昇順で返す', () => {
    expect(titles(upcomingEvents(events, '2026-09-24'))).toEqual(['2026-09', '2026-12']);
  });
});

describe('イベントの日付表示', () => {
  it('一覧の単日は月日と曜日を出す', () => {
    expect(eventDateColumn(event('単日', '2024-08-07'))).toEqual({ main: '08.07', sub: '水' });
  });

  it('一覧の期間は終了日を添え、年をまたぐときは年も出す', () => {
    expect(eventDateColumn(event('期間', '2024-08-07', '2024-08-09'))).toEqual({ main: '08.07', sub: '– 08.09' });
    expect(eventDateColumn(event('年跨ぎ', '2024-12-31', '2025-01-01'))).toEqual({
      main: '12.31',
      sub: '– 2025.01.01',
    });
  });

  it('一覧の日付未定は日付列を出さない', () => {
    expect(eventDateColumn(event('未定', null))).toBeNull();
  });

  it('年見出しのないホームでは開始日に年を付ける', () => {
    expect(eventDateColumn(event('単日', '2024-08-07'), true)).toEqual({ main: '2024.08.07', sub: '水' });
    expect(eventDateColumn(event('期間', '2024-08-07', '2024-08-09'), true)).toEqual({
      main: '2024.08.07',
      sub: '– 08.09',
    });
  });
});

describe('イベント詳細の開催時期', () => {
  it('単日は年月日と曜日を出す', () => {
    expect(eventDetailDate(event('単日', '2024-08-07'))).toEqual(['2024.08.07 (水)']);
  });

  it('期間は開始日と終了日をつなぐ', () => {
    expect(eventDetailDate(event('期間', '2026-10-10', '2026-10-16'))).toEqual(['2026.10.10 (土)', '2026.10.16 (金)']);
  });

  it('日付未定は値を持たない', () => {
    expect(eventDetailDate(event('未定', null))).toBeNull();
  });
});
