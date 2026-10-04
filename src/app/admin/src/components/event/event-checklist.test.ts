import { describe, expect, it } from 'bun:test';
import { buildEventChecklist, type ChecklistInput } from './event-checklist';

const filled: ChecklistInput = {
  typeValue: 1,
  statusValue: 1,
  venueCount: 1,
  sourceCount: 2,
  performanceCount: 19,
  setlistCount: 31,
  unassignedPerformanceCount: 0,
  relatedCount: 0,
};

const stateOf = (input: ChecklistInput, key: string) =>
  buildEventChecklist(input).find((item) => item.key === key)?.state;

describe('イベントの入力状況', () => {
  it('すべて入力済みなら任意項目以外は完了になる', () => {
    const states = buildEventChecklist(filled).map((item) => [item.key, item.state]);

    expect(states).toEqual([
      ['venues', 'done'],
      ['sources', 'done'],
      ['performances', 'done'],
      ['setlist', 'done'],
      ['related', 'optional'],
    ]);
  });

  it('件数が 0 の項目は未入力になる', () => {
    const input = { ...filled, venueCount: 0, sourceCount: 0, performanceCount: 0, setlistCount: 0 };

    expect(stateOf(input, 'venues')).toBe('missing');
    expect(stateOf(input, 'sources')).toBe('missing');
    expect(stateOf(input, 'performances')).toBe('missing');
    expect(stateOf(input, 'setlist')).toBe('missing');
  });

  it('セットリストに未紐づけの楽曲披露があれば未入力として件数を出す', () => {
    const item = buildEventChecklist({ ...filled, unassignedPerformanceCount: 2 }).find(
      (candidate) => candidate.key === 'setlist',
    );

    expect(item?.state).toBe('missing');
    expect(item?.detail).toBe('未紐づけの楽曲披露 2 件');
  });

  it('延期・中止のイベントは楽曲披露とセットリストが対象外になる', () => {
    const input = { ...filled, statusValue: 3 as const, performanceCount: 0, setlistCount: 0 };

    expect(stateOf(input, 'performances')).toBe('notApplicable');
    expect(stateOf(input, 'setlist')).toBe('notApplicable');
  });

  it('ライブと配信以外はセットリストだけが対象外になる', () => {
    const input = { ...filled, typeValue: 3 as const, setlistCount: 0 };

    expect(stateOf(input, 'performances')).toBe('done');
    expect(stateOf(input, 'setlist')).toBe('notApplicable');
  });
});
