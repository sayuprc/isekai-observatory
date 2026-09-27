import { describe, expect, it } from 'bun:test';
import { clickPerformance, emptySelection } from './performance-selection';

const ids = ['p1', 'p2', 'p3', 'p4', 'p5'];

describe('楽曲披露の選択', () => {
  it('クリックした行の選択を切り替える', () => {
    const selected = clickPerformance(emptySelection(), ids, 1, false);
    expect([...selected.selectedIds]).toEqual(['p2']);

    const unselected = clickPerformance(selected, ids, 1, false);
    expect([...unselected.selectedIds]).toEqual([]);
  });

  it('範囲指定のクリックは起点からその行までを選択に加える', () => {
    const anchored = clickPerformance(emptySelection(), ids, 3, false);
    const result = clickPerformance(anchored, ids, 1, true);

    expect([...result.selectedIds].sort()).toEqual(['p2', 'p3', 'p4']);
    expect(result.anchorId).toBe('p4');
  });

  it('範囲指定でも既存の選択は外さない', () => {
    const first = clickPerformance(emptySelection(), ids, 0, false);
    const anchored = clickPerformance(first, ids, 3, false);
    const result = clickPerformance(anchored, ids, 4, true);

    expect([...result.selectedIds].sort()).toEqual(['p1', 'p4', 'p5']);
  });

  it('起点がない範囲指定のクリックは 1 行の切り替えとして扱う', () => {
    const result = clickPerformance(emptySelection(), ids, 2, true);

    expect([...result.selectedIds]).toEqual(['p3']);
    expect(result.anchorId).toBe('p3');
  });

  it('並べ替えのあとも起点の行を ID で追う', () => {
    const anchored = clickPerformance(emptySelection(), ids, 0, false);
    const result = clickPerformance(anchored, ['p2', 'p3', 'p1', 'p4', 'p5'], 4, true);

    expect([...result.selectedIds].sort()).toEqual(['p1', 'p4', 'p5']);
  });
});
