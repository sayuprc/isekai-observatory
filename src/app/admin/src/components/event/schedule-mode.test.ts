import { describe, expect, it } from 'bun:test';
import { initialScheduleState, switchScheduleMode } from './schedule-mode';

const valuesOf = (state: { startOn: string; endOn: string }) => [state.startOn, state.endOn];

describe('開催時期の形式の切り替え', () => {
  it('保存されている日付から形式を決める', () => {
    expect(initialScheduleState('', '').mode).toBe('undecided');
    expect(initialScheduleState('2020-12-26', '').mode).toBe('single');
    expect(initialScheduleState('2025-02-22', '2025-03-09').mode).toBe('range');
  });

  it('単日 → 未定 → 単日で開催日が元に戻る', () => {
    const initial = initialScheduleState('2020-12-26', '');
    const undecided = switchScheduleMode(initial, 'undecided');
    const single = switchScheduleMode(undecided, 'single');

    expect(valuesOf(undecided)).toEqual(['', '']);
    expect(valuesOf(single)).toEqual(['2020-12-26', '']);
  });

  it('期間 → 単日 → 期間で終了日が元に戻る', () => {
    const initial = initialScheduleState('2025-02-22', '2025-03-09');
    const single = switchScheduleMode(initial, 'single');
    const range = switchScheduleMode(single, 'range');

    expect(valuesOf(single)).toEqual(['2025-02-22', '']);
    expect(valuesOf(range)).toEqual(['2025-02-22', '2025-03-09']);
  });

  it('期間 → 未定 → 期間で両方の日付が元に戻る', () => {
    const initial = initialScheduleState('2025-02-22', '2025-03-09');
    const range = switchScheduleMode(switchScheduleMode(initial, 'undecided'), 'range');

    expect(valuesOf(range)).toEqual(['2025-02-22', '2025-03-09']);
  });

  it('表示中に書き換えた日付を覚える', () => {
    const edited = { ...initialScheduleState('2020-12-26', ''), startOn: '2021-01-01' };
    const single = switchScheduleMode(switchScheduleMode(edited, 'undecided'), 'single');

    expect(valuesOf(single)).toEqual(['2021-01-01', '']);
  });

  it('表示中に空にした日付は、空のまま覚える', () => {
    const cleared = { ...initialScheduleState('2020-12-26', ''), startOn: '' };
    const range = switchScheduleMode(cleared, 'range');

    expect(valuesOf(range)).toEqual(['', '']);
  });
});
