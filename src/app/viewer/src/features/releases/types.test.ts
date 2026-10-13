import { describe, expect, it } from 'bun:test';
import {
  RELEASE_GROUP_TYPE_FILTER_ORDER,
  RELEASE_GROUP_TYPE_NAMES,
  releaseFormatText,
  trackSearchTitles,
  trackSongListItem,
} from './types';

describe('リリース種別の絞り込み', () => {
  it('並び順はすべての種別を 1 回ずつ含む', () => {
    const values: number[] = Object.keys(RELEASE_GROUP_TYPE_NAMES).map(Number);
    const order: number[] = [...RELEASE_GROUP_TYPE_FILTER_ORDER];

    expect(order.toSorted((a, b) => a - b)).toEqual(values.toSorted((a, b) => a - b));
  });
});

describe('提供形態の表示名', () => {
  it('提供形態を表示名にして並べる', () => {
    expect(releaseFormatText([2, 3])).toBe('CD・DVD');
  });
});

describe('トラックの曲一覧項目', () => {
  const song = { songId: 'song-1', title: '曲名', isDisplay: true };
  const other = { songId: 'song-2', title: '別の曲', isDisplay: true };

  it('2 桁の番号と楽曲詳細へのリンクを持つ', () => {
    expect(trackSongListItem({ trackNo: 3, title: null, songs: [song] })).toEqual({
      index: '03',
      label: null,
      songs: [{ title: '曲名', href: '/songs/song-1', note: null }],
    });
  });

  it('1 曲のトラック名は楽曲名の上書きとして出す', () => {
    expect(trackSongListItem({ trackNo: 1, title: '曲名 -acoustic-', songs: [song] }).songs).toEqual([
      { title: '曲名 -acoustic-', href: '/songs/song-1', note: null },
    ]);
  });

  it('非表示の楽曲はリンクを持たない', () => {
    expect(
      trackSongListItem({ trackNo: 1, title: null, songs: [{ ...song, isDisplay: false }] }).songs[0].href,
    ).toBeNull();
  });

  it('楽曲のないトラックはトラック名をリンクなしで出す', () => {
    expect(trackSongListItem({ trackNo: 1, title: 'インスト', songs: [] }).songs).toEqual([
      { title: 'インスト', href: null, note: null },
    ]);
  });

  it('メドレーはトラック名を見出しにして楽曲を収録順に並べる', () => {
    expect(trackSongListItem({ trackNo: 2, title: 'メドレー', songs: [other, { ...song, isDisplay: false }] })).toEqual(
      {
        index: '02',
        label: 'メドレー',
        songs: [
          { title: '別の曲', href: '/songs/song-2', note: null },
          { title: '曲名', href: null, note: null },
        ],
      },
    );
  });
});

describe('トラックの検索名', () => {
  it('トラック名と楽曲名の両方を返す', () => {
    expect(
      trackSearchTitles({
        trackNo: 1,
        title: 'メドレー',
        songs: [{ songId: 'song-1', title: '曲名', isDisplay: true }],
      }),
    ).toEqual(['メドレー', '曲名']);
  });
});
