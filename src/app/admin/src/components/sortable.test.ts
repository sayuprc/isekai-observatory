import { describe, expect, it } from 'bun:test';
import { reorderItems } from './sortable';

describe('並べ替え', () => {
  it('指定位置へ項目を移動する', () => {
    expect(reorderItems(['a', 'b', 'c', 'd'], 1, 3)).toEqual(['a', 'c', 'd', 'b']);
    expect(reorderItems(['a', 'b', 'c', 'd'], 3, 1)).toEqual(['a', 'd', 'b', 'c']);
  });

  it('範囲外の位置では順序を変えない', () => {
    expect(reorderItems(['a', 'b'], 0, -1)).toEqual(['a', 'b']);
    expect(reorderItems(['a', 'b'], 1, 2)).toEqual(['a', 'b']);
  });
});
