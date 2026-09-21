import { createResource, For, Match, Switch } from 'solid-js';
import { client } from '../../utils/client';
import { formatter } from '../../utils/date';
import { ListState } from '../ListState';

export const AdminUserList = () => {
  const [data, { refetch }] = createResource(async () => {
    const { data, status } = await client.api['admin-users'].get();

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
            <th>名前</th>
            <th>メールアドレス</th>
            <th>役割</th>
            <th>作成日</th>
          </tr>
        </thead>
        <tbody>
          <Switch>
            <Match when={data.loading}>
              <ListState state="loading" colSpan={4} />
            </Match>
            <Match when={!data()}>
              <ListState
                state="error"
                colSpan={4}
                message="データの取得に失敗しました。再度お試しください。"
                onRetry={() => refetch()}
              />
            </Match>
            <Match when={data() && data()!.adminUsers.length === 0}>
              <ListState state="empty" colSpan={4} message="管理ユーザーはありません。" />
            </Match>
            <Match when={data()}>
              {result => (
                <For each={result().adminUsers}>
                  {adminUser => (
                    <tr>
                      <td>{adminUser.name}</td>
                      <td>{adminUser.email}</td>
                      <td>{adminUser.role.name}</td>
                      <td>{formatter.format(new Date(adminUser.createdAt))}</td>
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
