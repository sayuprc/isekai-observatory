import { readFileSync } from 'node:fs';
import { describe, expect, it } from 'bun:test';
import { buildIndex, findInvalidFilter, parseConditions, queryPositions, SEARCH_SPECS, tokenize } from './search.ts';

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

  it('全文検索の空のパラメータは条件に含めない', () => {
    expect(parseConditions(spec, new URLSearchParams('title=')).textQueries).toEqual([]);
  });
});

describe('絞り込みの値の検証', () => {
  const invalid = (query: string) => findInvalidFilter(spec, parseConditions(spec, new URLSearchParams(query)));

  it('契約の enum にある値は不正としない', () => {
    expect(invalid('type=1,2,3')).toBeNull();
    expect(invalid('title=未遂')).toBeNull();
  });

  it('enum に無い値、数値でない値、空の値は不正として、そのパラメータ名を返す', () => {
    expect(invalid('type=4')).toBe('type');
    expect(invalid('type=abc')).toBe('type');
    expect(invalid('type=')).toBe('type');
    expect(invalid('type=1,,2')).toBe('type');
  });
});

describe('契約との一致', () => {
  const oas = Bun.YAML.parse(
    readFileSync(new URL('../../../contracts/generated/oas/IsekaiObservatory.Public.v1.yaml', import.meta.url), 'utf8'),
  ) as { components: { schemas: Record<string, { enum: number[] }> } };

  it('絞り込みの値は OpenAPI の enum と一致する', () => {
    for (const [resource, resourceSpec] of Object.entries(SEARCH_SPECS)) {
      for (const [name, { schema, values }] of Object.entries(resourceSpec.filters)) {
        expect({ resource, name, values: [...values] }).toEqual({
          resource,
          name,
          values: oas.components.schemas[schema]?.enum,
        });
      }
    }
  });
});
