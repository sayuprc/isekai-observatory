import { afterEach, describe, expect, it } from 'bun:test';
import { getListUrl } from './list-url';

const setSearch = (search: string) => {
  Object.defineProperty(globalThis, 'window', {
    value: { location: { search } },
    configurable: true,
  });
};

describe('getListUrl', () => {
  afterEach(() => {
    Reflect.deleteProperty(globalThis, 'window');
  });

  it('back の検索条件を一覧 URL に付ける', () => {
    setSearch(`?back=${encodeURIComponent('?name=abc&page=2')}`);
    expect(getListUrl('/venues')).toBe('/venues?name=abc&page=2');
  });

  it('back がない、または ? 始まりでなければ条件なしの一覧を返す', () => {
    setSearch('');
    expect(getListUrl('/venues')).toBe('/venues');

    setSearch(`?back=${encodeURIComponent('//example.com')}`);
    expect(getListUrl('/venues')).toBe('/venues');
  });

  it('window がない環境では条件なしの一覧を返す', () => {
    expect(getListUrl('/venues')).toBe('/venues');
  });
});
