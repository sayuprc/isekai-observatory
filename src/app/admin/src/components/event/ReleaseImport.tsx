import type { EventRelease, ReleaseGetResponse } from '../../generated';
import { client } from '../../utils/client';
import { createKeywordSearch } from '../keyword-search';
import type { SongCandidate } from '../song-import';
import { SongImportPanel } from '../SongImportPanel';
import { releaseLabel } from './event-links';
import { searchEventReleases } from './release-search';

interface ReleaseImportProps {
  title: string;
  description: string;
  importLabel: string;
  // 取り込み先によって選べる候補が違うので、候補の作り方は呼び出し側が決める
  toCandidates: (data: Pick<ReleaseGetResponse, 'release' | 'songs'>) => SongCandidate[];
  // イベントの関連リリース. 検索せずに取り込み元として選べる
  relatedReleases: EventRelease[];
  onImport: (candidates: SongCandidate[]) => void;
  disabled?: boolean;
}

export const ReleaseImport = (props: ReleaseImportProps) => {
  const search = createKeywordSearch<EventRelease>({
    emptyKeywordMessage: 'リリースグループのタイトルを入力してください',
    fetch: searchEventReleases,
  });

  const loadCandidates = async (release: EventRelease) => {
    const { data, status } = await client.api.releases({ releaseId: release.releaseId }).get();
    return { items: data ? props.toCandidates(data) : undefined, status };
  };

  return (
    <SongImportPanel
      title={props.title}
      description={props.description}
      placeholder="リリースグループのタイトルで検索"
      search={search}
      sourceLabel={releaseLabel}
      quickSources={props.relatedReleases}
      quickSourcesTitle="関連リリースから選ぶ"
      load={loadCandidates}
      importLabel={props.importLabel}
      onImport={props.onImport}
      disabled={props.disabled}
    />
  );
};
