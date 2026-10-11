export type PageItem = number | 'ellipsis';

/** 表示するページ番号の最大数。ページ数によらずボタン数を一定にして幅の揺れを防ぐ */
const MAX_ITEMS = 7;

/**
 * ページ番号ボタンの並びを返す
 * 先頭・末尾・現在ページの前後を残し、間を省略記号で詰める
 */
export const pageItems = (page: number, maxPage: number): PageItem[] => {
  if (maxPage <= MAX_ITEMS) {
    return Array.from({ length: maxPage }, (_, i) => i + 1);
  }
  if (page <= 4) {
    return [1, 2, 3, 4, 5, 'ellipsis', maxPage];
  }
  if (page >= maxPage - 3) {
    return [1, 'ellipsis', maxPage - 4, maxPage - 3, maxPage - 2, maxPage - 1, maxPage];
  }
  return [1, 'ellipsis', page - 1, page, page + 1, 'ellipsis', maxPage];
};
