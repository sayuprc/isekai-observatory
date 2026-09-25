import { describe, expect, it } from 'bun:test';
import type { Venue } from '../../generated';
import { addVenue, toSourcesPayload, validateSources } from './event-links';

const venue = (venueId: string): Venue => ({ venueId, name: `会場${venueId}`, kind: { name: '現地', value: 1 } });

describe('イベントの開催先と出典', () => {
  it('開催先を末尾に追加し、追加済みの開催先は重複させない', () => {
    const entries = addVenue([], venue('1'));

    expect(addVenue(entries, venue('2')).map(entry => entry.venueId)).toEqual(['1', '2']);
    expect(addVenue(entries, venue('1'))).toBe(entries);
  });

  it('出典を画面の順序で送信値にする', () => {
    expect(toSourcesPayload([{ displayName: ' 公式 ', url: 'https://example.com/a ' }])).toEqual([
      { displayName: '公式', url: 'https://example.com/a', orderNo: 1 },
    ]);
  });

  it('表示名か URL が空の出典を拒否する', () => {
    expect(validateSources([{ displayName: '公式', url: '' }])).toBe('出典 1 行目: 表示名と URL の両方が必要です');
  });

  it('URL が重複する出典を拒否する', () => {
    const sources = [
      { displayName: 'A', url: 'https://example.com' },
      { displayName: 'B', url: 'https://example.com' },
    ];

    expect(validateSources(sources)).toBe('出典の URL が重複しています');
  });
});
