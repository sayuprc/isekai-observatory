import { createResource, createSignal, For, Match, Show, Switch } from 'solid-js';
import type { SongSearchResponse } from '../../generated';
import { redirectToLogin } from '../../utils/auth-redirect';
import { client } from '../../utils/client';
import { SONG_TYPE_NAMES } from '../../utils/enum-names';
import { Pagination } from '../Pagination';

interface TaggedSongsProps {
  tagId: string;
  // 楽曲タブを初めて開いたときに取得する
  active: boolean;
}

type FetchState = { status: 'ok'; result: SongSearchResponse } | { status: 'forbidden' } | { status: 'error' };

const PER_PAGE = 25;

// タグが付いた楽曲。非公開の楽曲も含めて表示順に並べる
export const TaggedSongs = (props: TaggedSongsProps) => {
  const [page, setPage] = createSignal(1);

  const [resource, { refetch }] = createResource(
    () => (props.active ? page() : undefined),
    async (current): Promise<FetchState> => {
      const { data, status } = await client.api.songs.search.get({
        query: { title: '', tag_id: props.tagId, page: current, per_page: PER_PAGE },
      });
      if (status === 401) {
        redirectToLogin();
        return { status: 'error' };
      }
      if (status === 403) return { status: 'forbidden' };
      return data ? { status: 'ok', result: data } : { status: 'error' };
    },
  );

  const result = () => {
    const state = resource();
    return state?.status === 'ok' ? state.result : undefined;
  };

  return (
    <section class="rounded-box border border-base-300 bg-base-200">
      <div class="flex items-center gap-3 border-b border-base-300 px-6 py-3">
        <h3 class="text-sm font-semibold">このタグが付いた楽曲</h3>
      </div>
      <Switch>
        <Match when={resource.loading}>
          <div class="flex items-center gap-3 px-6 py-6 text-sm" role="status">
            <span class="loading loading-spinner loading-sm" />
            読み込み中...
          </div>
        </Match>
        <Match when={resource()?.status === 'forbidden'}>
          <p class="px-6 py-6 text-sm text-base-content/60">楽曲の閲覧権限がありません</p>
        </Match>
        <Match when={resource.error || resource()?.status === 'error'}>
          <div class="flex items-center gap-3 px-6 py-6">
            <p class="text-sm text-error">楽曲の取得に失敗しました</p>
            <button type="button" class="btn btn-outline btn-sm" onClick={() => refetch()}>
              再試行
            </button>
          </div>
        </Match>
        <Match when={result()}>
          {(result) => (
            <Switch>
              <Match when={result().songs.length === 0}>
                <p class="px-6 py-6 text-sm text-base-content/60">このタグが付いた楽曲はありません</p>
              </Match>
              <Match when={result().songs.length > 0}>
                <div class="overflow-x-auto">
                  <table class="table table-sm">
                    <thead>
                      <tr>
                        <th>楽曲名</th>
                        <th>楽曲種別</th>
                        <th>公開</th>
                      </tr>
                    </thead>
                    <tbody>
                      <For each={result().songs}>
                        {(song) => (
                          <tr>
                            <td>
                              <a href={`/songs/${song.songId}`} class="link link-hover font-medium">
                                {song.title}
                              </a>
                            </td>
                            <td class="whitespace-nowrap">{SONG_TYPE_NAMES[song.type]}</td>
                            <td class="whitespace-nowrap">
                              <span
                                class={`badge badge-sm ${song.isDisplay ? 'badge-success badge-soft' : 'badge-ghost'}`}
                              >
                                {song.isDisplay ? '公開' : '非公開'}
                              </span>
                            </td>
                          </tr>
                        )}
                      </For>
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
