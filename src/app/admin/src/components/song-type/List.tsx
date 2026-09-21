import { createResource, For, Match, Switch } from 'solid-js';
import { client } from '../../utils/client';
import { ListState } from '../ListState';

export const SongTypeList = () => {
  const [data, { refetch }] = createResource(async () => {
    const { data, status } = await client.api['song-types'].get();

    if (status === 401) {
      window.location.href = '/auth/login';
      return;
    }

    return data;
  });

  return (
    <div class="rounded-box border border-base-300 bg-base-100 overflow-x-auto">
      <table class="table table-sm table-zebra md:table-md">
        <thead>
          <tr>
            <th>楽曲種別名</th>
          </tr>
        </thead>
        <tbody>
          <Switch>
            <Match when={data.loading}>
              <ListState state="loading" colSpan={1} />
            </Match>
            <Match when={!data()}>
              <ListState
                state="error"
                colSpan={1}
                message="データの取得に失敗しました。再度お試しください。"
                onRetry={() => refetch()}
              />
            </Match>
            <Match when={data() && data()!.types.length === 0}>
              <ListState state="empty" colSpan={1} message="楽曲種別はありません。" />
            </Match>
            <Match when={data()}>
              {result => (
                <For each={result().types}>
                  {type => (
                    <tr>
                      <td>{type.name}</td>
                    </tr>
                  )}
                </For>
              )}
            </Match>
          </Switch>
        </tbody>
      </table>
    </div>
  );
};
