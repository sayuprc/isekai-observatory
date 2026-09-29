import type { EventRelease } from '../../generated';
import { client } from '../../utils/client';
import { createKeywordSearch } from '../keyword-search';
import type { SongCandidate } from '../song-import';
import { SongImportPanel } from '../SongImportPanel';
import { releaseLabel } from './event-links';
import { toReleasePerformanceCandidates } from './release-import';
import { searchEventReleases } from './release-search';

interface ReleaseImportProps {
  // イベントの関連リリース. 検索せずに取り込み元として選べる
  relatedReleases: EventRelease[];
  onImport: (candidates: SongCandidate[]) => void;
  disabled?: boolean;
}

const loadCandidates = async (release: EventRelease) => {
  const { data, status } = await client.api.releases({ releaseId: release.releaseId }).get();
  return { items: data ? toReleasePerformanceCandidates(data) : undefined, status };
};

export const ReleaseImport = (props: ReleaseImportProps) => {
  const search = createKeywordSearch<EventRelease>({
    emptyKeywordMessage: 'リリースグループのタイトルを入力してください',
    fetch: searchEventReleases,
  });

  return (
    <SongImportPanel
      title="リリースの収録楽曲から追加"
      description="選んだリリースの収録楽曲を、曲順のまま楽曲披露の末尾に追加します。管理対象外楽曲は追加できません"
      placeholder="リリースグループのタイトルで検索"
      search={search}
      sourceLabel={releaseLabel}
      quickSources={props.relatedReleases}
      quickSourcesTitle="関連リリースから選ぶ"
      load={loadCandidates}
      importLabel="楽曲披露に追加"
      onImport={props.onImport}
      disabled={props.disabled}
    />
  );
};
