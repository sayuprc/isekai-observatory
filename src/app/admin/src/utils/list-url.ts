/**
 * 一覧画面から `?back=` で受け取った検索条件を付けて、一覧画面の URL を組み立てる
 * back はクエリ文字列 (`?` 始まり) だけを受け付け、それ以外は条件なしの一覧に戻す
 */
export const getListUrl = (listPath: string): string => {
  if (typeof window === 'undefined') return listPath;

  const back = new URLSearchParams(window.location.search).get('back') ?? '';
  if (!back.startsWith('?')) return listPath;

  const query = new URLSearchParams(back.slice(1)).toString();
  return query ? `${listPath}?${query}` : listPath;
};
