import MiniSearch from 'minisearch';
import type { Options, Query } from 'minisearch';
import type { Resource } from './layout.ts';

/**
 * 検索索引の構成
 * 索引の書き出し (snapshot) と読み込み (worker) が同じ定義を使う必要がある
 */

type Item = Record<string, unknown>;

type SearchSpec = {
  /** 全文検索できるフィールド。クエリパラメータ名と同じにする */
  text: Record<string, (item: Item) => string[]>;
  /** 完全一致で絞り込めるフィールド。クエリパラメータ名と同じにする */
  filters: Record<string, (item: Item) => unknown>;
};

const strings = (...values: unknown[]): string[] =>
  values.filter((v): v is string => typeof v === 'string' && v !== '');

const children = (value: unknown): Item[] => (Array.isArray(value) ? (value as Item[]) : []);

export const SEARCH_SPECS: Partial<Record<Resource, SearchSpec>> = {
  'events': {
    text: {
      title: (e) => strings(e.title),
      songTitle: (e) => children(e.performances).flatMap((p) => strings(p.songTitle)),
    },
    filters: { type: (e) => e.type, status: (e) => e.status },
  },
  'songs': {
    text: { title: (s) => strings(s.title) },
    filters: { type: (s) => s.type },
  },
  'release-groups': {
    text: {
      title: (g) => strings(g.title),
      songTitle: (g) =>
        children(g.releases).flatMap((r) =>
          children(r.media).flatMap((m) =>
            children(m.tracks).flatMap((t) => [
              ...strings(t.title),
              ...children(t.songs).flatMap((s) => strings(s.title)),
            ]),
          ),
        ),
    },
    filters: { type: (g) => g.type },
  },
  'media': {
    text: { title: (m) => strings(m.title) },
    filters: { type: (m) => m.type },
  },
  'people': { text: { name: (p) => strings(p.name) }, filters: {} },
  'person-groups': { text: { name: (p) => strings(p.name) }, filters: {} },
  'tags': { text: { name: (t) => strings(t.name) }, filters: {} },
  'venues': { text: { name: (v) => strings(v.name) }, filters: { kind: (v) => v.kind } },
};

export const searchIndexPath = (resource: Resource): string => `/search/${resource}.json`;

const CJK = /[぀-ヿ㐀-鿿ｦ-ﾟ]/;

/**
 * 空白では区切れない日本語を引けるよう、CJK を含む連なりは 2-gram にする
 */
export const tokenize = (text: string): string[] =>
  text
    .normalize('NFKC')
    .toLowerCase()
    .split(/[^\p{L}\p{N}]+/u)
    .filter((run) => run !== '')
    .flatMap((run) => {
      if (!CJK.test(run)) {
        return [run];
      }

      const chars = [...run];

      return chars.length === 1 ? chars : chars.slice(0, -1).map((char, i) => char + chars[i + 1]);
    });

const searchOptions = (spec: SearchSpec): Options => ({
  idField: 'position',
  fields: Object.keys(spec.text),
  storeFields: Object.keys(spec.filters),
  tokenize,
  processTerm: (term) => term,
});

/** 一覧での位置 (0 始まり) を ID として、一覧の順に items を索引へ入れる */
export const buildIndex = (spec: SearchSpec, items: Item[]): MiniSearch => {
  const index = new MiniSearch(searchOptions(spec));

  index.addAll(
    items.map((item, position) => ({
      position,
      ...Object.fromEntries(Object.entries(spec.text).map(([name, pick]) => [name, pick(item).join(' ')])),
      ...Object.fromEntries(Object.entries(spec.filters).map(([name, pick]) => [name, String(pick(item))])),
    })),
  );

  return index;
};

export const loadIndex = (spec: SearchSpec, json: string): MiniSearch => MiniSearch.loadJSON(json, searchOptions(spec));

export type SearchConditions = {
  textQueries: Query[];
  filters: { name: string; accepted: Set<string> }[];
};

/**
 * クエリパラメータから検索条件を組み立てる。空のパラメータは無いものとして扱う
 */
export const parseConditions = (spec: SearchSpec, params: URLSearchParams): SearchConditions => ({
  textQueries: Object.keys(spec.text).flatMap((name) => {
    const value = params.get(name);

    return value === null || value === ''
      ? []
      : [{ queries: [value], fields: [name], combineWith: 'AND', prefix: true }];
  }),
  filters: Object.keys(spec.filters).flatMap((name) => {
    const value = params.get(name);

    return value === null || value === '' ? [] : [{ name, accepted: new Set(value.split(',')) }];
  }),
});

/**
 * 条件に合う要素の一覧での位置を返す
 * 全文検索の条件があれば関連度の高い順、絞り込みだけなら一覧の順
 */
export const queryPositions = (index: MiniSearch, { textQueries, filters }: SearchConditions): number[] => {
  const results = index.search(
    textQueries.length === 0 ? MiniSearch.wildcard : { queries: textQueries, combineWith: 'AND' },
    {
      filter: (result) => filters.every(({ name, accepted }) => accepted.has(String(result[name]))),
    },
  );
  const positions = results.map(({ id }) => Number(id));

  return textQueries.length === 0 ? positions.sort((a, b) => a - b) : positions;
};
