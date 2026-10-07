import { describe, expect, it } from 'bun:test';
import { RELEASE_GROUP_TYPE_FILTER_ORDER, RELEASE_GROUP_TYPE_NAMES, releaseFormatText } from './types';

describe('リリース種別の絞り込み', () => {
  it('並び順はすべての種別を 1 回ずつ含む', () => {
    const values: number[] = Object.keys(RELEASE_GROUP_TYPE_NAMES).map(Number);
    const order: number[] = [...RELEASE_GROUP_TYPE_FILTER_ORDER];

    expect(order.toSorted((a, b) => a - b)).toEqual(values.toSorted((a, b) => a - b));
  });
});

describe('提供形態の表示名', () => {
  it('提供形態を表示名にして並べる', () => {
    expect(releaseFormatText([2, 3])).toBe('CD・DVD');
  });
});
