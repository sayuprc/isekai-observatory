import { createResource, Match, Show, Switch } from 'solid-js';
import { client } from '../../utils/client';
import { formatter } from '../../utils/date';

const ACTION_LABEL: Record<string, string> = {
  create: '作成',
  update: '更新',
  delete: '削除',
  login: 'ログイン',
  refresh: 'リフレッシュ',
  recovery_code_issue: 'リカバリーコード発行',
  recovery_code_use: 'リカバリーコード使用',
};

const TARGET_TYPE_LABEL: Record<string, string> = {
  AdminUser: '管理ユーザー',
  Media: 'メディア',
  Person: '人物',
  Release: 'リリース',
  Song: '楽曲',
  SongTag: '楽曲タグ',
};

interface Props {
  auditLogId: string;
}

interface FetchState {
  status: 'ok' | 'forbidden' | 'notFound' | 'error';
  data?: {
    auditLogId: string;
    adminUserId: string;
    adminUserName: string;
    action: string;
    targetType: string;
    targetId: string;
    snapshot: Record<string, unknown>;
    createdAt: string;
  };
}

export const DetailView = (props: Props) => {
  const listUrl = () => {
    if (typeof window === 'undefined') return '/audit-logs';
    const back = new URLSearchParams(window.location.search).get('back') ?? '';
    if (!back.startsWith('?')) return '/audit-logs';
    try {
      const q = new URLSearchParams(back.slice(1)).toString();
      return q ? `/audit-logs?${q}` : '/audit-logs';
    } catch {
      return '/audit-logs';
    }
  };

  const [resource, { refetch }] = createResource<FetchState, string>(
    () => props.auditLogId,
    async (id) => {
      const { data, status } = await client.api['audit-logs']({ auditLogId: id }).get();

      if (status === 401) {
        window.location.href = '/auth/login';
        return { status: 'error' as const };
      }

      if (status === 403) {
        return { status: 'forbidden' as const };
      }

      if (status === 404) {
        return { status: 'notFound' as const };
      }

      if (!data) {
        return { status: 'error' as const };
      }

      return { status: 'ok' as const, data: data.auditLog };
    },
  );

  return (
    <Switch>
      <Match when={resource.loading}>
        <div class="flex items-center justify-center gap-3 py-10 text-base-content/70" role="status" aria-live="polite">
          <span class="loading loading-spinner loading-md" aria-hidden="true" />
          <span>読み込み中...</span>
        </div>
      </Match>
      <Match when={resource()?.status === 'forbidden'}>
        <div class="alert alert-error">
          <span>監査ログの閲覧権限がありません。</span>
        </div>
      </Match>
      <Match when={resource()?.status === 'notFound'}>
        <div class="alert alert-warning">
          <span>監査ログが見つかりません。</span>
        </div>
      </Match>
      <Match when={resource()?.status === 'error'}>
        <div class="flex flex-col items-start gap-3">
          <p class="text-error">データの取得に失敗しました。</p>
          <button type="button" class="btn btn-outline btn-sm" onClick={() => refetch()}>
            再試行
          </button>
        </div>
      </Match>
      <Match when={resource()?.status === 'ok' && resource()!.data}>
        {data => (
          <div class="flex flex-col gap-4">
            <Show when={typeof window !== 'undefined'}>
              <div>
                <a href={listUrl()} class="btn btn-ghost btn-sm">
                  ← 一覧に戻る
                </a>
              </div>
            </Show>
            <div class="rounded-box border border-base-300 bg-base-100 p-4">
              <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-[8rem_1fr]">
                <dt class="font-semibold">日時</dt>
                <dd>{formatter.format(new Date(data().createdAt))}</dd>
                <dt class="font-semibold">実行者</dt>
                <dd>{data().adminUserName}</dd>
                <dt class="font-semibold">操作</dt>
                <dd>{ACTION_LABEL[data().action] ?? data().action}</dd>
                <dt class="font-semibold">対象種別</dt>
                <dd>{TARGET_TYPE_LABEL[data().targetType] ?? data().targetType}</dd>
                <dt class="font-semibold">対象ID</dt>
                <dd class="font-mono text-sm break-all">{data().targetId}</dd>
                <dt class="font-semibold">監査ログID</dt>
                <dd class="font-mono text-sm break-all">{data().auditLogId}</dd>
              </dl>
            </div>
            <div class="rounded-box border border-base-300 bg-base-100 p-4">
              <h2 class="mb-2 font-semibold">スナップショット</h2>
              <pre class="overflow-x-auto rounded bg-base-200 p-3 text-xs">
                {JSON.stringify(data().snapshot, null, 2)}
              </pre>
            </div>
          </div>
        )}
      </Match>
    </Switch>
  );
};
