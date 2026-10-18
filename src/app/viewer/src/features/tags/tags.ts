import type { AttachedTag } from '../../generated/types.gen.js';

export type Tag = AttachedTag;

type Tagged = { tags: Tag[] };

// 一覧のセレクトに出すため、記録に付いたタグを重複なく表示順に集める
export function collectTags(items: Tagged[]): Tag[] {
  const byId = new Map<string, Tag>();

  for (const tag of items.flatMap((item) => item.tags)) {
    byId.set(tag.tagId, tag);
  }

  return [...byId.values()].sort(
    (left, right) => left.orderNo - right.orderNo || left.tagId.localeCompare(right.tagId),
  );
}

// FilterBar が dataset から読む、空白区切りのタグ ID
export const tagIdsText = (item: Tagged): string => item.tags.map((tag) => tag.tagId).join(' ');
