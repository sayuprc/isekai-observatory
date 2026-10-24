import type MiniSearch from 'minisearch';
import type { Resource } from '../shared/layout.ts';
import {
  findInvalidFilter,
  loadIndex,
  parseConditions,
  queryPositions,
  SEARCH_SPECS,
  searchIndexPath,
} from '../shared/search.ts';

/** 静的ファイルを path で読む。Worker では ASSETS binding が担う */
export type ReadText = (path: string) => Promise<string>;

const indexes = new Map<Resource, Promise<MiniSearch>>();

/** isolate ごと・リソースごとに 1 回だけ索引を復元する。失敗したら次のリクエストでやり直す */
const getIndex = (resource: Resource, readText: ReadText): Promise<MiniSearch> => {
  const spec = SEARCH_SPECS[resource];

  if (spec === undefined) {
    throw new Error(`Resource is not searchable: ${resource}`);
  }

  let index = indexes.get(resource);

  if (index === undefined) {
    index = readText(searchIndexPath(resource)).then((json) => loadIndex(spec, json));
    index.catch(() => indexes.delete(resource));
    indexes.set(resource, index);
  }

  return index;
};

export type FindPositionsResult = { ok: true; positions: number[] | null } | { ok: false; message: string };

/**
 * 検索条件に合う要素の一覧での位置を返す
 * 検索条件が無いときは null を返し、通常の一覧として扱わせる
 */
export const findPositions = async (
  resource: Resource,
  params: URLSearchParams,
  readText: ReadText,
): Promise<FindPositionsResult> => {
  const spec = SEARCH_SPECS[resource];

  if (spec === undefined) {
    return { ok: true, positions: null };
  }

  const conditions = parseConditions(spec, params);
  const invalid = findInvalidFilter(spec, conditions);

  if (invalid !== null) {
    return { ok: false, message: `${invalid} が不正です。契約で定義された値をカンマ区切りで指定してください。` };
  }

  // 検索条件が無いときは索引を読まない
  if (conditions.textQueries.length === 0 && conditions.filters.length === 0) {
    return { ok: true, positions: null };
  }

  return { ok: true, positions: queryPositions(await getIndex(resource, readText), conditions) };
};
