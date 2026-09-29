import type { Event, EventSummary } from '../../generated';
import { normalizeDateValue } from '../../utils/date';
import type { SongCandidate } from '../song-import';
import type { TrackForm } from './MediaEditor';

export const eventLabel = (event: Pick<EventSummary, 'title' | 'schedule'>): string => {
  const startOn = normalizeDateValue(event.schedule.startOn);

  return startOn === '' ? `${event.title} (日付未定)` : `${event.title} (${startOn})`;
};

/**
 * イベントのセットリスト順に、収録楽曲の候補を並べる
 * 表示名だけの項目は MC などのことが多いので、タイトルのみトラックの候補として初期状態では選ばない
 * セットリストに属さない楽曲披露は披露順で末尾に足す。セットリストがなければ楽曲披露の順になる
 */
export const toEventTrackCandidates = (event: Pick<Event, 'performances' | 'setlist'>): SongCandidate[] => {
  const fromSetlist = event.setlist.flatMap((item, index): SongCandidate[] => {
    const position = `セットリスト ${index + 1}`;
    const label = item.label ?? '';

    if (item.performances.length === 0) {
      return [
        {
          key: item.setlistItemId,
          songId: null,
          title: label,
          note: position,
          selectable: true,
          defaultSelected: false,
        },
      ];
    }

    return item.performances.map((performance) => ({
      key: performance.performanceId,
      songId: performance.songId,
      title: performance.songTitle,
      note: label === '' ? position : `${position} (${label})`,
      selectable: true,
      defaultSelected: true,
    }));
  });

  const assignedIds = new Set(event.setlist.flatMap((item) => item.performances.map((p) => p.performanceId)));
  const unassigned = event.performances
    .filter((performance) => !assignedIds.has(performance.performanceId))
    .map((performance) => ({
      key: performance.performanceId,
      songId: performance.songId,
      title: performance.songTitle,
      note: event.setlist.length === 0 ? `楽曲披露 ${performance.orderNo}` : 'セットリスト外の楽曲披露',
      selectable: true,
      defaultSelected: true,
    }));

  return [...fromSetlist, ...unassigned];
};

export const toTrackForms = (candidates: SongCandidate[]): TrackForm[] =>
  candidates.map((candidate) =>
    candidate.songId === null
      ? { songId: null, songTitle: null, title: candidate.title }
      : { songId: candidate.songId, songTitle: candidate.title, title: '' },
  );
