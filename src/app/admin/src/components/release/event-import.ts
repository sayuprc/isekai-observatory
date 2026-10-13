import type { Event, EventSummary } from '../../generated';
import { normalizeDateValue } from '../../utils/date';
import type { SongCandidate } from '../song-import';
import type { TrackForm } from './media-form';

export const eventLabel = (event: Pick<EventSummary, 'title' | 'schedule'>): string => {
  const startOn = normalizeDateValue(event.schedule.startOn);

  return startOn === '' ? `${event.title} (日付未定)` : `${event.title} (${startOn})`;
};

/**
 * イベントのセットリスト順に、収録曲の候補を 1 項目 1 候補で並べる。メドレーは 1 つの候補のまま保つ
 * 表示名だけの項目は MC などのことが多いので、タイトルのみトラックの候補として初期状態では選ばない
 * セットリストに属さない楽曲披露は披露順で末尾に足す。セットリストがなければ楽曲披露の順になる
 */
export const toEventTrackCandidates = (event: Pick<Event, 'performances' | 'setlist'>): SongCandidate[] => {
  const fromSetlist = event.setlist.map((item, index): SongCandidate => {
    const position = `セットリスト ${index + 1}`;
    const label = item.label ?? '';

    if (item.performances.length === 0) {
      return {
        key: item.setlistItemId,
        songs: [],
        label,
        note: position,
        selectable: true,
        defaultSelected: false,
      };
    }

    return {
      key: item.setlistItemId,
      songs: item.performances.map((performance) => ({ songId: performance.songId, title: performance.songTitle })),
      label,
      note: label === '' ? position : `${position} (${label})`,
      selectable: true,
      defaultSelected: true,
    };
  });

  const assignedIds = new Set(event.setlist.flatMap((item) => item.performances.map((p) => p.performanceId)));
  const unassigned = event.performances
    .filter((performance) => !assignedIds.has(performance.performanceId))
    .map((performance) => ({
      key: performance.performanceId,
      songs: [{ songId: performance.songId, title: performance.songTitle }],
      label: '',
      note: event.setlist.length === 0 ? `楽曲披露 ${performance.orderNo}` : 'セットリスト外の楽曲披露',
      selectable: true,
      defaultSelected: true,
    }));

  return [...fromSetlist, ...unassigned];
};

// 1 曲の項目の表示名は楽曲名の上書きではないので、トラック名にするのはメドレーと楽曲なしの項目だけにする
export const toTrackForms = (candidates: SongCandidate[]): TrackForm[] =>
  candidates.map((candidate) => ({
    songs: candidate.songs,
    title: candidate.songs.length === 1 ? '' : candidate.label,
  }));
