import { describe, expect, it } from 'bun:test';
import type { EventRelease, ReleaseGroupReferencedRelease, Venue } from '../../generated';
import { addRelease, addVenue, releaseLabel, toEventReleases, toSourcesPayload, validateSources } from './event-links';

const venue = (venueId: string): Venue => ({ venueId, name: `会場${venueId}`, kind: 1 });
const release = (releaseId: string): EventRelease => ({
  releaseId,
  releaseGroupId: 'group',
  releaseGroupTitle: 'ライブ映像',
  name: `版${releaseId}`,
  releasedOn: '2026-03-01',
  isDisplay: true,
  formats: [4],
});

describe('イベントの開催先と出典', () => {
  it('開催先を末尾に追加し、追加済みの開催先は重複させない', () => {
    const entries = addVenue([], venue('1'));

    expect(addVenue(entries, venue('2')).map((entry) => entry.venueId)).toEqual(['1', '2']);
    expect(addVenue(entries, venue('1'))).toBe(entries);
  });

  it('リリースを末尾に追加し、追加済みのリリースは重複させない', () => {
    const entries = addRelease([], release('1'));

    expect(addRelease(entries, release('2')).map((entry) => entry.releaseId)).toEqual(['1', '2']);
    expect(addRelease(entries, release('1'))).toBe(entries);
  });

  it('リリースグループの版を候補にし、グループと版がともに公開のときだけ公開扱いにする', () => {
    const releases: ReleaseGroupReferencedRelease[] = [
      {
        releaseId: 'a',
        name: '通常盤',
        releasedOn: '2026-03-01',
        color: '#000000',
        isDisplay: true,
        orderNo: 1,
        formats: [4],
      },
      {
        releaseId: 'b',
        name: '限定盤',
        releasedOn: '2026-03-01',
        color: '#000000',
        isDisplay: false,
        orderNo: 2,
        formats: [3],
      },
    ];

    expect(toEventReleases({ releaseGroupId: 'g', title: 'ライブ', isDisplay: true }, releases)).toEqual([
      {
        releaseId: 'a',
        releaseGroupId: 'g',
        releaseGroupTitle: 'ライブ',
        name: '通常盤',
        releasedOn: '2026-03-01',
        isDisplay: true,
        formats: [4],
      },
      {
        releaseId: 'b',
        releaseGroupId: 'g',
        releaseGroupTitle: 'ライブ',
        name: '限定盤',
        releasedOn: '2026-03-01',
        isDisplay: false,
        formats: [3],
      },
    ]);
    expect(
      toEventReleases({ releaseGroupId: 'g', title: 'ライブ', isDisplay: false }, releases).map(
        (entry) => entry.isDisplay,
      ),
    ).toEqual([false, false]);
  });

  it('リリースをグループ名・版名・発売日・提供形態で表示する', () => {
    expect(releaseLabel({ ...release('1'), name: '通常盤', formats: [3, 4] })).toBe(
      'ライブ映像 通常盤 (2026-03-01 / DVD・Blu-ray)',
    );
    expect(releaseLabel({ ...release('1'), name: '' })).toBe('ライブ映像 (2026-03-01 / Blu-ray)');
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
