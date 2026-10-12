import { describe, expect, it } from 'bun:test';
import type { ReleaseGetResponse } from '../../generated';
import {
  addPerformancesFromCandidates,
  addSetlistFromCandidates,
  toReleasePerformanceCandidates,
  toReleaseSetlistCandidates,
} from './release-import';

const data: Pick<ReleaseGetResponse, 'release' | 'songs'> = {
  release: {
    releaseId: 'r1',
    releaseGroupId: 'g1',
    name: '',
    releasedOn: '2026-01-01',
    description: '',
    note: '',
    isDisplay: true,
    orderNo: 1,
    formats: [2],
    media: [
      {
        position: 1,
        name: null,
        tracks: [
          { songIds: ['song-1'], title: null, trackNo: 1 },
          { songIds: [], title: 'インスト', trackNo: 2 },
          { songIds: ['song-3', 'song-1'], title: 'メドレー', trackNo: 3 },
        ],
      },
      { position: 2, name: 'Blu-ray', tracks: [{ songIds: ['song-2'], title: '曲2 (Live)', trackNo: 1 }] },
    ],
  },
  songs: [
    { mediumPosition: 1, trackNo: 1, songId: 'song-1', title: '曲1' },
    { mediumPosition: 1, trackNo: 2, songId: null, title: 'インスト' },
    { mediumPosition: 1, trackNo: 3, songId: 'song-3', title: '曲3' },
    { mediumPosition: 1, trackNo: 3, songId: 'song-1', title: '曲1' },
    { mediumPosition: 2, trackNo: 1, songId: 'song-2', title: '曲2' },
  ],
};

describe('リリースの収録楽曲からの取り込み', () => {
  it('媒体と曲順どおりに並べ、楽曲名は収録曲 read model から引く', () => {
    const result = toReleasePerformanceCandidates(data);

    expect(result.map((candidate) => [candidate.songs.map((song) => song.title), candidate.note])).toEqual([
      [['曲1'], '媒体 1 - 1'],
      [[], '媒体 1 - 2 (管理対象外楽曲)'],
      [['曲3', '曲1'], '媒体 1 - 3 (メドレー)'],
      [['曲2'], 'Blu-ray - 1 (表記: 曲2 (Live))'],
    ]);
  });

  it('タイトルのみトラックは選べない候補にする', () => {
    const result = toReleasePerformanceCandidates(data);

    expect(result[1]).toMatchObject({ selectable: false, defaultSelected: false });
  });

  it('楽曲を参照する候補だけを楽曲披露として末尾に追加し、メドレーは楽曲ごとに披露にする', () => {
    const candidates = toReleasePerformanceCandidates(data);
    const existing = [{ performanceId: 'p1', songId: 'song-1', songTitle: '曲1', coVocalists: [] }];

    const result = addPerformancesFromCandidates(existing, candidates);

    expect(result.map((performance) => performance.songId)).toEqual(['song-1', 'song-1', 'song-3', 'song-1', 'song-2']);
    expect(result[4]).toMatchObject({ songTitle: '曲2', coVocalists: [] });
  });
});

describe('リリースの収録楽曲からのセットリスト取り込み', () => {
  it('タイトルのみトラックも初期状態で選んだ候補にする', () => {
    const result = toReleaseSetlistCandidates(data);

    expect(result.map((candidate) => [candidate.label, candidate.selectable, candidate.defaultSelected])).toEqual([
      ['', true, true],
      ['インスト', true, true],
      ['メドレー', true, true],
      ['曲2 (Live)', true, true],
    ]);
  });

  it('タイトルが空のトラックは選べないままにする', () => {
    const withEmptyTitle = {
      ...data,
      release: {
        ...data.release,
        media: [{ position: 1, name: null, tracks: [{ songIds: [], title: null, trackNo: 1 }] }],
      },
    };

    const result = toReleaseSetlistCandidates(withEmptyTitle);

    expect(result[0]).toMatchObject({ selectable: false, defaultSelected: false });
  });

  it('1 件 1 項目で末尾に足し、楽曲は楽曲披露を作って紐づけ、タイトルのみは表示名の項目にする', () => {
    const candidates = toReleaseSetlistCandidates(data);
    const current = {
      performances: [{ performanceId: 'p1', songId: 'song-1', songTitle: '曲1', coVocalists: [] }],
      setlist: [{ setlistItemId: 's1', label: '', performanceIds: ['p1'] }],
    };

    const result = addSetlistFromCandidates(current, candidates);

    expect(result.performances.map((performance) => performance.songId)).toEqual([
      'song-1',
      'song-1',
      'song-3',
      'song-1',
      'song-2',
    ]);
    const songIdOf = new Map(result.performances.map((performance) => [performance.performanceId, performance.songId]));
    expect(result.setlist.map((item) => [item.label, item.performanceIds.map((id) => songIdOf.get(id))])).toEqual([
      ['', ['song-1']],
      ['', ['song-1']],
      ['インスト', []],
      ['メドレー', ['song-3', 'song-1']],
      // 1 曲のトラック名は楽曲名の表記違いなので表示名にしない
      ['', ['song-2']],
    ]);
    // 既存の披露を使い回さず、取り込んだ楽曲ごとに新しい披露へ紐づける
    expect(new Set(result.setlist.flatMap((item) => item.performanceIds)).size).toBe(5);
  });
});
