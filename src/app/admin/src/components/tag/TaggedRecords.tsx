import { createResource, createSignal, For, Match, Show, Switch, type JSX } from 'solid-js';
import type { EventSummary, MediaSummary, ReleaseGroupSummary, SongSummary } from '../../generated';
import { redirectToLogin } from '../../utils/auth-redirect';
import { client } from '../../utils/client';
import { normalizeDateTimeDisplayValue } from '../../utils/date';
import { EVENT_TYPE_NAMES, MEDIA_TYPE_NAMES, RELEASE_GROUP_TYPE_NAMES, SONG_TYPE_NAMES } from '../../utils/enum-names';
import { formatSchedule } from '../event/event-options';
import { Pagination } from '../Pagination';

const PER_PAGE = 25;

type FetchState<T> = { status: 'ok'; items: T[]; maxPage: number } | { status: 'forbidden' } | { status: 'error' };

// 各対象の検索 API の結果を、一覧の表示に必要な形へそろえる
const toFetchState = <T,>(status: number, items: T[] | undefined, maxPage: number | undefined): FetchState<T> => {
  if (status === 401) {
    redirectToLogin();
    return { status: 'error' };
  }
  if (status === 403) return { status: 'forbidden' };
  return items && maxPage !== undefined ? { status: 'ok', items, maxPage } : { status: 'error' };
};

interface TaggedRecordListProps<T> {
  // 何の一覧か。見出しと空・エラー時の文言に使う
  recordName: string;
  // タブを初めて開いたときに取得する
  active: boolean;
  fetchPage: (page: number) => Promise<FetchState<T>>;
  columns: string[];
  renderRow: (item: T) => JSX.Element;
}

// タグが付いた記録の一覧。非公開の記録も含め、各対象の検索 API の既定の並びで出す
const TaggedRecordList = <T,>(props: TaggedRecordListProps<T>) => {
  const [page, setPage] = createSignal(1);
  const [resource, { refetch }] = createResource(
    () => (props.active ? page() : undefined),
    (current) => props.fetchPage(current),
  );

  const result = () => {
    const state = resource();
    return state?.status === 'ok' ? state : undefined;
  };

  return (
    <section class="rounded-box border border-base-300 bg-base-200">
      <div class="flex items-center gap-3 border-b border-base-300 px-6 py-3">
        <h3 class="text-sm font-semibold">このタグが付いた{props.recordName}</h3>
      </div>
      <Switch>
        <Match when={resource.loading}>
          <div class="flex items-center gap-3 px-6 py-6 text-sm" role="status">
            <span class="loading loading-spinner loading-sm" />
            読み込み中...
          </div>
        </Match>
        <Match when={resource()?.status === 'forbidden'}>
          <p class="px-6 py-6 text-sm text-base-content/60">{props.recordName}の閲覧権限がありません</p>
        </Match>
        <Match when={resource.error || resource()?.status === 'error'}>
          <div class="flex items-center gap-3 px-6 py-6">
            <p class="text-sm text-error">{props.recordName}の取得に失敗しました</p>
            <button type="button" class="btn btn-outline btn-sm" onClick={() => refetch()}>
              再試行
            </button>
          </div>
        </Match>
        <Match when={result()}>
          {(result) => (
            <Switch>
              <Match when={result().items.length === 0}>
                <p class="px-6 py-6 text-sm text-base-content/60">このタグが付いた{props.recordName}はありません</p>
              </Match>
              <Match when={result().items.length > 0}>
                <div class="overflow-x-auto">
                  <table class="table table-sm">
                    <thead>
                      <tr>
                        <For each={props.columns}>{(column) => <th>{column}</th>}</For>
                      </tr>
                    </thead>
                    <tbody>
                      <For each={result().items}>{(item) => <tr>{props.renderRow(item)}</tr>}</For>
                    </tbody>
                  </table>
                </div>
                <Show when={result().maxPage > 1}>
                  <div class="pb-4">
                    <Pagination page={page()} maxPage={result().maxPage} onChange={setPage} />
                  </div>
                </Show>
              </Match>
            </Switch>
          )}
        </Match>
      </Switch>
    </section>
  );
};

const DisplayBadge = (props: { isDisplay: boolean }) => (
  <span class={`badge badge-sm ${props.isDisplay ? 'badge-success badge-soft' : 'badge-ghost'}`}>
    {props.isDisplay ? '公開' : '非公開'}
  </span>
);

interface TaggedProps {
  tagId: string;
  active: boolean;
}

export const TaggedSongs = (props: TaggedProps) => (
  <TaggedRecordList<SongSummary>
    recordName="楽曲"
    active={props.active}
    fetchPage={async (page) => {
      const { data, status } = await client.api.songs.search.get({
        query: { title: '', tag_id: props.tagId, page, per_page: PER_PAGE },
      });
      return toFetchState(status, data?.songs, data?.maxPage);
    }}
    columns={['楽曲名', '楽曲種別', '公開']}
    renderRow={(song) => (
      <>
        <td>
          <a href={`/songs/${song.songId}`} class="link link-hover font-medium">
            {song.title}
          </a>
        </td>
        <td class="whitespace-nowrap">{SONG_TYPE_NAMES[song.type]}</td>
        <td class="whitespace-nowrap">
          <DisplayBadge isDisplay={song.isDisplay} />
        </td>
      </>
    )}
  />
);

export const TaggedEvents = (props: TaggedProps) => (
  <TaggedRecordList<EventSummary>
    recordName="イベント"
    active={props.active}
    fetchPage={async (page) => {
      const { data, status } = await client.api.events.search.get({
        query: { tag_id: props.tagId, page, per_page: PER_PAGE },
      });
      return toFetchState(status, data?.events, data?.maxPage);
    }}
    columns={['タイトル', '活動種別', '開催時期', '公開']}
    renderRow={(event) => (
      <>
        <td>
          <a href={`/events/${event.eventId}`} class="link link-hover font-medium">
            {event.title}
          </a>
        </td>
        <td class="whitespace-nowrap">{EVENT_TYPE_NAMES[event.type]}</td>
        <td class="whitespace-nowrap">{formatSchedule(event.schedule)}</td>
        <td class="whitespace-nowrap">
          <DisplayBadge isDisplay={event.isDisplay} />
        </td>
      </>
    )}
  />
);

export const TaggedReleaseGroups = (props: TaggedProps) => (
  <TaggedRecordList<ReleaseGroupSummary>
    recordName="リリースグループ"
    active={props.active}
    fetchPage={async (page) => {
      const { data, status } = await client.api['release-groups'].search.get({
        query: { tag_id: props.tagId, page, per_page: PER_PAGE },
      });
      return toFetchState(status, data?.releaseGroups, data?.maxPage);
    }}
    columns={['タイトル', 'リリース種別', '公開']}
    renderRow={(releaseGroup) => (
      <>
        <td>
          <a href={`/release-groups/${releaseGroup.releaseGroupId}`} class="link link-hover font-medium">
            {releaseGroup.title}
          </a>
        </td>
        <td class="whitespace-nowrap">{RELEASE_GROUP_TYPE_NAMES[releaseGroup.type]}</td>
        <td class="whitespace-nowrap">
          <DisplayBadge isDisplay={releaseGroup.isDisplay} />
        </td>
      </>
    )}
  />
);

export const TaggedMedia = (props: TaggedProps) => (
  <TaggedRecordList<MediaSummary>
    recordName="メディア"
    active={props.active}
    fetchPage={async (page) => {
      const { data, status } = await client.api.media.search.get({
        query: { tag_id: props.tagId, page, per_page: PER_PAGE },
      });
      return toFetchState(status, data?.media, data?.maxPage);
    }}
    columns={['タイトル', 'メディア種別', '公開日時', '公開']}
    renderRow={(media) => (
      <>
        <td class="max-w-72 min-w-44">
          <a href={`/media/${media.mediaId}`} class="link link-hover block truncate font-medium">
            {media.title}
          </a>
        </td>
        <td class="whitespace-nowrap">{MEDIA_TYPE_NAMES[media.type]}</td>
        <td class="whitespace-nowrap text-sm">{normalizeDateTimeDisplayValue(media.publishedAt)}</td>
        <td class="whitespace-nowrap">
          <DisplayBadge isDisplay={media.isDisplay} />
        </td>
      </>
    )}
  />
);
