import { describe, expect, it } from 'bun:test';
import { PER_PAGE_OPTIONS, parsePage, pickParam } from './search-list';

describe('pickParam', () => {
  it('候補にある値を返す', () => {
    expect(pickParam('desc', ['asc', 'desc'] as const, 'asc')).toBe('desc');
    expect(pickParam('50', PER_PAGE_OPTIONS, 25)).toBe(50);
  });

  it('候補にない値や null は fallback を返す', () => {
    expect(pickParam('random', ['asc', 'desc'] as const, 'asc')).toBe('asc');
    expect(pickParam('30', PER_PAGE_OPTIONS, 25)).toBe(25);
    expect(pickParam(null, ['asc', 'desc'] as const, 'asc')).toBe('asc');
  });
});

describe('parsePage', () => {
  it('1 以上の数値はそのまま返す', () => {
    expect(parsePage('3')).toBe(3);
  });

  it('未指定・数値でない・1 未満は 1 に丸める', () => {
    expect(parsePage(null)).toBe(1);
    expect(parsePage('abc')).toBe(1);
    expect(parsePage('0')).toBe(1);
    expect(parsePage('-2')).toBe(1);
  });
});
