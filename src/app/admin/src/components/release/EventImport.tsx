import type { EventSummary } from '../../generated';
import { client } from '../../utils/client';
import { createKeywordSearch } from '../keyword-search';
import type { SongCandidate } from '../song-import';
import { SongImportPanel } from '../SongImportPanel';
import { eventLabel, toEventTrackCandidates } from './event-import';

interface EventImportProps {
  targetLabel: string;
  onImport: (candidates: SongCandidate[]) => void;
}

const searchEvents = async (title: string) => {
  const { data, status } = await client.api.events.search.get({
    query: { title, sort: 'schedule', order: 'desc', page: 1, per_page: 25 },
  });
  return { items: data?.events, status };
};

const loadCandidates = async (event: EventSummary) => {
  const { data, status } = await client.api.events({ eventId: event.eventId }).get();
  return { items: data ? toEventTrackCandidates(data.event) : undefined, status };
};

export const EventImport = (props: EventImportProps) => {
  const search = createKeywordSearch<EventSummary>({
    emptyKeywordMessage: 'イベントのタイトルを入力してください',
    fetch: searchEvents,
  });

  return (
    <SongImportPanel
      title="イベントのセットリストから追加"
      description="選んだイベントのセットリスト順に、楽曲を追加先媒体の末尾へ追加します。表示名だけの項目はタイトルのみトラックとして追加できます"
      placeholder="イベントのタイトルで検索"
      search={search}
      sourceLabel={eventLabel}
      load={loadCandidates}
      importLabel={`${props.targetLabel}に追加`}
      onImport={props.onImport}
    />
  );
};
