import { describe, expect, it } from 'bun:test';
import {
  addCoVocalist,
  addPerformance,
  removeCoVocalist,
  setCreditName,
  toSetlistPayload,
  type PerformanceForm,
} from './performance-form';

const performance = (performanceId: string, coVocalists: PerformanceForm['coVocalists'] = []): PerformanceForm => ({
  performanceId,
  songId: `song-${performanceId}`,
  songTitle: `曲${performanceId}`,
  coVocalists,
});

const person = { personId: 'person-1', name: '人物1', creditName: '' };

describe('イベントの楽曲披露フォーム', () => {
  it('楽曲を末尾に披露として追加する', () => {
    const result = addPerformance([performance('p1')], { songId: 'song-2', title: '曲2' });

    expect(result).toHaveLength(2);
    expect(result[1]).toMatchObject({ songId: 'song-2', songTitle: '曲2', coVocalists: [] });
  });

  it('指定した披露だけに共演者を追加する', () => {
    const result = addCoVocalist([performance('p1'), performance('p2')], 1, person);

    expect(result.map((item) => item.coVocalists)).toEqual([[], [person]]);
  });

  it('同じ披露には同一人物を重複して追加しない', () => {
    const performances = [performance('p1', [person])];

    expect(addCoVocalist(performances, 0, person)).toEqual(performances);
  });

  it('指定した共演者のクレジット名だけを変更する', () => {
    const other = { personId: 'person-2', name: '人物2', creditName: '' };
    const result = setCreditName([performance('p1', [person, other])], 0, 1, 'ゲスト');

    expect(result.map((item) => item.coVocalists)).toEqual([[person, { ...other, creditName: 'ゲスト' }]]);
  });

  it('指定した共演者を外す', () => {
    const result = removeCoVocalist([performance('p1', [person])], 0, 0);

    expect(result.map((item) => item.coVocalists)).toEqual([[]]);
  });

  it('存在しない楽曲披露への参照をセットリストの送信値から除く', () => {
    const result = toSetlistPayload(
      [{ setlistItemId: 's1', label: ' 本編 ', performanceIds: ['p1', 'removed'] }],
      [performance('p1')],
    );

    expect(result).toEqual([{ setlistItemId: 's1', orderNo: 1, label: '本編', performanceIds: ['p1'] }]);
  });
});
