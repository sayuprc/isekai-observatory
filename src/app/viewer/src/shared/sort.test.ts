import { describe, expect, it } from 'bun:test';
import { byTitle, orderRanks, sortRanks } from './sort';

describe('orderRanks', () => {
  it('渡した並びの位置を順位にする', () => {
    const ranks = orderRanks(['c', 'a', 'b']);

    expect(ranks.get('c')).toBe(0);
    expect(ranks.get('a')).toBe(1);
    expect(ranks.get('b')).toBe(2);
  });
});

describe('sortRanks', () => {
  it('並べ替えた後の位置を順位にし、元の配列は変えない', () => {
    const items = [{ title: 'う' }, { title: 'あ' }, { title: 'い' }];
    const ranks = sortRanks(items, byTitle);

    expect(items.map((item) => ranks.get(item))).toEqual([2, 0, 1]);
    expect(items.map((item) => item.title)).toEqual(['う', 'あ', 'い']);
  });
});

describe('byTitle', () => {
  it('ひらがなとカタカナを五十音で並べる', () => {
    const titles = ['ウ', 'あ', 'イ'].map((title) => ({ title })).sort(byTitle);

    expect(titles.map((item) => item.title)).toEqual(['あ', 'イ', 'ウ']);
  });
});
