import { describe, expect, it } from 'bun:test';
import type { ReleaseGetResponse } from '../../generated';
import { canMergeWithPrevious, mergeWithPrevious, splitFromMedley, toMediaPayload, toMediumForms } from './media-form';

const song1 = { songId: 'song-1', title: '曲1' };
const song2 = { songId: 'song-2', title: '曲2' };
const song3 = { songId: 'song-3', title: '曲3' };

describe('収録曲フォームの変換', () => {
  it('レスポンスの楽曲 ID を収録曲 read model の楽曲名と組にする', () => {
    const data = {
      release: {
        media: [
          {
            position: 1,
            name: null,
            tracks: [
              { songIds: ['song-2', 'song-1'], title: 'メドレー', trackNo: 1 },
              { songIds: [], title: 'インスト', trackNo: 2 },
            ],
          },
        ],
      },
      songs: [
        { mediumPosition: 1, trackNo: 1, songId: 'song-2', title: '曲2' },
        { mediumPosition: 1, trackNo: 1, songId: 'song-1', title: '曲1' },
        { mediumPosition: 1, trackNo: 2, songId: null, title: 'インスト' },
      ],
    } as unknown as ReleaseGetResponse;

    expect(toMediumForms(data)).toEqual([
      {
        name: '',
        tracks: [
          { songs: [song2, song1], title: 'メドレー' },
          { songs: [], title: 'インスト' },
        ],
      },
    ]);
  });

  it('リクエストでは楽曲 ID を並び順のまま送り、空のトラック名は null にする', () => {
    const payload = toMediaPayload([
      {
        name: ' CD ',
        tracks: [
          { songs: [song2, song1], title: ' ' },
          { songs: [song3], title: '曲3 -instrumental-' },
        ],
      },
    ]);

    expect(payload).toEqual([
      {
        position: 1,
        name: 'CD',
        tracks: [
          { songIds: ['song-2', 'song-1'], title: null, trackNo: 1 },
          { songIds: ['song-3'], title: '曲3 -instrumental-', trackNo: 2 },
        ],
      },
    ]);
  });
});

describe('メドレーにまとめる', () => {
  it('直前のトラックの末尾へ楽曲を移し、1 曲の上書き名は引き継がない', () => {
    const tracks = [
      { songs: [song1], title: '曲1 -short-' },
      { songs: [song2], title: '' },
      { songs: [song3], title: '' },
    ];

    expect(mergeWithPrevious(tracks, 1)).toEqual([
      { songs: [song1, song2], title: '' },
      { songs: [song3], title: '' },
    ]);
  });

  it('メドレーへさらにまとめるときはメドレー名を保つ', () => {
    const tracks = [
      { songs: [song1, song2], title: 'メドレー' },
      { songs: [song3], title: '' },
    ];

    expect(mergeWithPrevious(tracks, 1)).toEqual([{ songs: [song1, song2, song3], title: 'メドレー' }]);
  });

  it('先頭のトラックとタイトルのみトラックはまとめられない', () => {
    const tracks = [
      { songs: [song1], title: '' },
      { songs: [], title: 'MC' },
      { songs: [song2], title: '' },
    ];

    expect([0, 1, 2].map((index) => canMergeWithPrevious(tracks, index))).toEqual([false, false, false]);
    expect(mergeWithPrevious(tracks, 2)).toBe(tracks);
  });
});

describe('メドレーから外す', () => {
  it('外した楽曲をメドレーの直後に 1 曲のトラックとして置く', () => {
    const tracks = [
      { songs: [song1, song2, song3], title: 'メドレー' },
      { songs: [], title: 'MC' },
    ];

    expect(splitFromMedley(tracks, 0, 1)).toEqual([
      { songs: [song1, song3], title: 'メドレー' },
      { songs: [song2], title: '' },
      { songs: [], title: 'MC' },
    ]);
  });

  it('1 曲だけ残ったトラックはメドレー名を上書き名にしない', () => {
    const tracks = [{ songs: [song1, song2], title: 'メドレー' }];

    expect(splitFromMedley(tracks, 0, 0)).toEqual([
      { songs: [song2], title: '' },
      { songs: [song1], title: '' },
    ]);
  });

  it('メドレーでないトラックは変えない', () => {
    const tracks = [{ songs: [song1], title: '' }];

    expect(splitFromMedley(tracks, 0, 0)).toBe(tracks);
  });
});
