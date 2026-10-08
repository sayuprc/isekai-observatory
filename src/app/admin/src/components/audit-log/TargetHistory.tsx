import { createResource, For, Match, Switch } from 'solid-js';
import type { AuditLogSummary, AuditTargetType } from '../../generated';
import { AUDIT_ACTION_NAMES } from '../../generated/enum-names.gen';
import { redirectToLogin } from '../../utils/auth-redirect';
import { client } from '../../utils/client';
import { formatter } from '../../utils/date';

interface TargetHistoryProps {
  targetType: AuditTargetType;
  targetId: string;
  // 履歴タブを初めて開いたときに取得する
  active: boolean;
}

type FetchState = { status: 'ok'; logs: AuditLogSummary[] } | { status: 'forbidden' } | { status: 'error' };

// 1 件の記録に対する監査ログ。新しい順に最大 50 件を出し、それ以上は監査ログ画面で絞り込んで見る
export const TargetHistory = (props: TargetHistoryProps) => {
  const [resource, { refetch }] = createResource(
    () => props.active || undefined,
    async (): Promise<FetchState> => {
      const { data, status } = await client.api['audit-logs'].search.get({
        query: { target_type: props.targetType, target_id: props.targetId, page: 1, per_page: 50 },
      });
      if (status === 401) {
        redirectToLogin();
        return { status: 'error' };
      }
      if (status === 403) return { status: 'forbidden' };
      return data ? { status: 'ok', logs: data.auditLogs } : { status: 'error' };
    },
  );

  const logs = () => {
    const state = resource();
    return state?.status === 'ok' ? state.logs : undefined;
  };

  const searchUrl = () => `/audit-logs?target_type=${props.targetType}&target_id=${props.targetId}`;

  return (
    <section class="rounded-box border border-base-300 bg-base-200">
      <div class="flex items-center gap-3 border-b border-base-300 px-6 py-3">
        <h3 class="text-sm font-semibold">変更履歴</h3>
        <a href={searchUrl()} class="link link-hover ml-auto text-xs">
          監査ログで開く
        </a>
      </div>
      <Switch>
        <Match when={resource.loading}>
          <div class="flex items-center gap-3 px-6 py-6 text-sm" role="status">
            <span class="loading loading-spinner loading-sm" />
            読み込み中...
          </div>
        </Match>
        <Match when={resource()?.status === 'forbidden'}>
          <p class="px-6 py-6 text-sm text-base-content/60">監査ログの閲覧権限がありません</p>
        </Match>
        <Match when={resource.error || resource()?.status === 'error'}>
          <div class="flex items-center gap-3 px-6 py-6">
            <p class="text-sm text-error">履歴の取得に失敗しました</p>
            <button type="button" class="btn btn-outline btn-sm" onClick={() => refetch()}>
              再試行
            </button>
          </div>
        </Match>
        <Match when={logs()}>
          {(logs) => (
            <Switch>
              <Match when={logs().length === 0}>
                <p class="px-6 py-6 text-sm text-base-content/60">履歴はまだありません</p>
              </Match>
              <Match when={logs().length > 0}>
                <div class="overflow-x-auto">
                  <table class="table table-sm">
                    <thead>
                      <tr>
                        <th>日時</th>
                        <th>操作</th>
                        <th>管理ユーザー</th>
                        <th />
                      </tr>
                    </thead>
                    <tbody>
                      <For each={logs()}>
                        {(log) => (
                          <tr>
                            <td class="font-mono text-xs">{formatter.format(new Date(log.createdAt))}</td>
                            <td>{AUDIT_ACTION_NAMES[log.action] ?? log.action}</td>
                            <td>{log.adminUserName}</td>
                            <td class="text-right">
                              <a href={`/audit-logs/${log.auditLogId}`} class="btn btn-ghost btn-xs">
                                詳細
                              </a>
                            </td>
                          </tr>
                        )}
                      </For>
                    </tbody>
                  </table>
                </div>
              </Match>
            </Switch>
          )}
        </Match>
      </Switch>
    </section>
  );
};
