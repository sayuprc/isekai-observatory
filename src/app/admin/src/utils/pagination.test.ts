import { describe, expect, it } from 'bun:test';
import { pageItems } from './pagination';

describe('pageItems', () => {
  it('7 ページ以下はすべて並べる', () => {
    expect(pageItems(1, 1)).toEqual([1]);
    expect(pageItems(3, 7)).toEqual([1, 2, 3, 4, 5, 6, 7]);
  });

  it('先頭付近では末尾側だけを省略する', () => {
    expect(pageItems(1, 20)).toEqual([1, 2, 3, 4, 5, 'ellipsis', 20]);
    expect(pageItems(4, 20)).toEqual([1, 2, 3, 4, 5, 'ellipsis', 20]);
  });

  it('末尾付近では先頭側だけを省略する', () => {
    expect(pageItems(17, 20)).toEqual([1, 'ellipsis', 16, 17, 18, 19, 20]);
    expect(pageItems(20, 20)).toEqual([1, 'ellipsis', 16, 17, 18, 19, 20]);
  });

  it('中間では現在ページの前後を残して両側を省略する', () => {
    expect(pageItems(5, 20)).toEqual([1, 'ellipsis', 4, 5, 6, 'ellipsis', 20]);
    expect(pageItems(16, 20)).toEqual([1, 'ellipsis', 15, 16, 17, 'ellipsis', 20]);
  });
});
