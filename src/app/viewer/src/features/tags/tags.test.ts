import { describe, expect, it } from 'bun:test';
import { collectTags, tagFilterHref, tagIdsText } from './tags';

const tag = (tagId: string, orderNo: number) => ({ tagId, name: `タグ${tagId}`, orderNo });

describe('collectTags', () => {
  it('重複を除き、表示順に並べる', () => {
    const items = [{ tags: [tag('b', 20), tag('a', 10)] }, { tags: [tag('c', 30), tag('a', 10)] }];

    expect(collectTags(items).map((entry) => entry.tagId)).toEqual(['a', 'b', 'c']);
  });

  it('タグが 1 つも無ければ空配列を返す', () => {
    expect(collectTags([{ tags: [] }])).toEqual([]);
  });
});

describe('tagIdsText', () => {
  it('タグ ID を空白区切りにする', () => {
    expect(tagIdsText({ tags: [tag('a', 1), tag('b', 2)] })).toBe('a b');
  });
});

describe('tagFilterHref', () => {
  it('一覧のパスに tag クエリを付ける', () => {
    expect(tagFilterHref('/songs', 'a')).toBe('/songs?tag=a');
  });
});
