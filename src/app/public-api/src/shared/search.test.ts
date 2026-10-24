import { describe, expect, it } from 'bun:test';
import { buildIndex, parseConditions, queryPositions, SEARCH_SPECS, tokenize } from './search.ts';

const spec = SEARCH_SPECS.songs!;
const songs = [
  { title: '未遂', type: 1 },
  { title: '未遂 SINKA LIVE SERIES ver', type: 2 },
  { title: '風の名前', type: 1 },
];
const index = buildIndex(spec, songs);
const find = (query: string) => queryPositions(index, parseConditions(spec, new URLSearchParams(query)));

describe('tokenize', () => {
  it('日本語は 2-gram、英数字は単語のまま分ける', () => {
    expect(tokenize('風の名前 LIVE')).toEqual(['風の', 'の名', '名前', 'live']);
  });
});

describe('検索条件', () => {
  it('検索できないフィールド名のパラメータは無視する', () => {
    const conditions = parseConditions(spec, new URLSearchParams('description=ライブ&q=未遂'));

    expect(conditions.textQueries).toEqual([]);
    expect(conditions.filters).toEqual([]);
  });

  it('全文検索と絞り込みを AND で組み合わせる', () => {
    expect(find('title=未遂')).toEqual(expect.arrayContaining([0, 1]));
    expect(find('title=未遂&type=2')).toEqual([1]);
  });

  it('絞り込みはカンマ区切りで OR になり、一覧の順で返る', () => {
    expect(find('type=2,1')).toEqual([0, 1, 2]);
  });

  it('空のパラメータは条件に含めない', () => {
    expect(parseConditions(spec, new URLSearchParams('title=&type=')).textQueries).toEqual([]);
  });
});
