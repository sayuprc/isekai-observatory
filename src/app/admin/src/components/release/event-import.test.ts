import { describe, expect, it } from 'bun:test';
import type { SetlistItem, SongPerformance } from '../../generated';
import { eventLabel, toEventTrackCandidates, toTrackForms } from './event-import';

const performance = (performanceId: string, orderNo: number): SongPerformance => ({
  performanceId,
  songId: `song-${performanceId}`,
  songTitle: `曲${performanceId}`,
  orderNo,
  coVocalists: [],
});

const item = (setlistItemId: string, label: string | null, performances: SongPerformance[]): SetlistItem => ({
  setlistItemId,
  orderNo: 1,
  label,
  performances,
});

describe('イベントのセットリストからの取り込み', () => {
  it('セットリスト順に並べ、メドレーは項目内の順で展開する', () => {
    const p1 = performance('p1', 1);
    const p2 = performance('p2', 2);
    const p3 = performance('p3', 3);

    const result = toEventTrackCandidates({
      performances: [p1, p2, p3],
      setlist: [item('s1', null, [p3]), item('s2', 'メドレー', [p1, p2])],
    });

    expect(result.map((candidate) => candidate.songId)).toEqual(['song-p3', 'song-p1', 'song-p2']);
    expect(result[1]).toMatchObject({ note: 'セットリスト 2 (メドレー)', selectable: true, defaultSelected: true });
  });

  it('表示名だけの項目は楽曲なしの候補にし、初期状態では選ばない', () => {
    const result = toEventTrackCandidates({ performances: [], setlist: [item('s1', 'MC', [])] });

    expect(result).toEqual([
      { key: 's1', songId: null, title: 'MC', note: 'セットリスト 1', selectable: true, defaultSelected: false },
    ]);
  });

  it('セットリスト外の楽曲披露を末尾に足す', () => {
    const p1 = performance('p1', 1);
    const p2 = performance('p2', 2);

    const result = toEventTrackCandidates({ performances: [p1, p2], setlist: [item('s1', null, [p2])] });

    expect(result.map((candidate) => candidate.key)).toEqual(['p2', 'p1']);
    expect(result[1]?.note).toBe('セットリスト外の楽曲披露');
  });

  it('セットリストがなければ楽曲披露の順にする', () => {
    const result = toEventTrackCandidates({ performances: [performance('p1', 1), performance('p2', 2)], setlist: [] });

    expect(result.map((candidate) => candidate.note)).toEqual(['楽曲披露 1', '楽曲披露 2']);
  });

  it('楽曲の候補は参照トラック、楽曲なしの候補はタイトルのみトラックにする', () => {
    const result = toTrackForms([
      { key: 'a', songId: 'song-1', title: '曲1', note: '', selectable: true, defaultSelected: true },
      { key: 'b', songId: null, title: 'MC', note: '', selectable: true, defaultSelected: false },
    ]);

    expect(result).toEqual([
      { songId: 'song-1', songTitle: '曲1', title: '' },
      { songId: null, songTitle: null, title: 'MC' },
    ]);
  });

  it('日付未定のイベントはラベルで分かるようにする', () => {
    expect(eventLabel({ title: 'ライブ', schedule: { startOn: null, endOn: null } })).toBe('ライブ (日付未定)');
    expect(eventLabel({ title: 'ライブ', schedule: { startOn: '2026-01-02', endOn: null } })).toBe(
      'ライブ (2026-01-02)',
    );
  });
});
