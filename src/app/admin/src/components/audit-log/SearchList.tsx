import { For, Match, Show, Switch } from 'solid-js';
import type { AuditAction, AuditTargetType } from '../../generated';
import { client } from '../../utils/client';
import { formatter } from '../../utils/date';
import {
  PER_PAGE_OPTIONS,
  createSearchResource,
  createSearchState,
  parsePage,
  pickParam,
} from '../../utils/search-list';
import type { PerPageOption as PerPage } from '../../utils/search-list';
import { ListState } from '../ListState';
import { Pagination } from '../Pagination';

type Action = AuditAction;
type TargetType = AuditTargetType;

// 生成された型 (AuditAction / AuditTargetType) を網羅する const タプル
// 型注釈で完全性を担保し、生成型に値が増減したらコンパイルエラーで気付ける形にする
const ACTION_OPTIONS = [
  'create',
  'update',
  'delete',
  'register',
  'login',
  'refresh',
  'recovery_code_issue',
  'recovery_code_use',
] as const satisfies readonly Action[];

const TARGET_TYPE_OPTIONS = [
  'AdminUser',
  'Media',
  'Person',
  'Release',
  'ReleaseGroup',
  'Song',
  'SongTag',
  'Venue',
  'Event',
] as const satisfies readonly TargetType[];

const ACTION_LABEL: Record<Action, string> = {
  create: '作成',
  update: '更新',
  delete: '削除',
  register: '登録',
  login: 'ログイン',
  refresh: 'リフレッシュ',
  recovery_code_issue: 'リカバリーコード発行',
  recovery_code_use: 'リカバリーコード使用',
};

const TARGET_TYPE_LABEL: Record<TargetType, string> = {
  AdminUser: '管理ユーザー',
  Media: 'メディア',
  Person: '人物',
  Release: 'リリース',
  ReleaseGroup: 'リリースグループ',
  Song: '楽曲',
  SongTag: '楽曲タグ',
  Venue: '開催先',
  Event: 'イベント',
};

interface Params {
  from: string;
  to: string;
  action?: Action;
  targetType?: TargetType;
  targetId: string;
  adminUserName: string;
  page: number;
  perPage: PerPage;
}

const DEFAULT_PARAMS: Params = {
  from: '',
  to: '',
  action: undefined,
  targetType: undefined,
  targetId: '',
  adminUserName: '',
  page: 1,
  perPage: 50,
};

const parseParams = (query: URLSearchParams): Params => ({
  from: query.get('from') ?? '',
  to: query.get('to') ?? '',
  action: pickParam<Action | ''>(query.get('action'), ACTION_OPTIONS, '') || undefined,
  targetType: pickParam<TargetType | ''>(query.get('target_type'), TARGET_TYPE_OPTIONS, '') || undefined,
  targetId: query.get('target_id') ?? '',
  adminUserName: query.get('admin_user_name') ?? '',
  page: parsePage(query.get('page')),
  perPage: pickParam(query.get('per_page'), PER_PAGE_OPTIONS, DEFAULT_PARAMS.perPage),
});

const toQuery = (params: Params) => ({
  from: params.from,
  to: params.to,
  action: params.action,
  target_type: params.targetType,
  target_id: params.targetId,
  admin_user_name: params.adminUserName,
  page: params.page,
  per_page: params.perPage,
});

const toIsoOrEmpty = (localDateTime: string): string => {
  if (!localDateTime) {
    return '';
  }

  const date = new Date(localDateTime);

  if (Number.isNaN(date.getTime())) {
    return '';
  }

  return date.toISOString();
};

export const SearchList = () => {
  const { params, input, updateInput, handleSearch, handleReset, handlePageChange } = createSearchState({
    defaults: DEFAULT_PARAMS,
    parse: parseParams,
    toQuery,
  });

  const { data, refetch, fetchError } = createSearchResource(
    params,
    current =>
      client.api['audit-logs'].search.get({
        query: {
          from: toIsoOrEmpty(current.from) || undefined,
          to: toIsoOrEmpty(current.to) || undefined,
          action: current.action,
          target_type: current.targetType,
          target_id: current.targetId || undefined,
          admin_user_name: current.adminUserName || undefined,
          page: current.page,
          per_page: current.perPage,
        },
      }),
    { forbiddenMessage: '監査ログの閲覧権限がありません。' },
  );

  return (
    <>
      <form onSubmit={handleSearch} class="mb-4 flex flex-wrap items-end gap-4">
        <fieldset class="fieldset">
          <label class="fieldset-label" for="from">
            期間 From
          </label>
          <input
            type="datetime-local"
            id="from"
            name="from"
            value={input().from}
            onInput={e => updateInput({ from: e.currentTarget.value })}
            class="input input-bordered input-sm"
          />
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="to">
            期間 To
          </label>
          <input
            type="datetime-local"
            id="to"
            name="to"
            value={input().to}
            onInput={e => updateInput({ to: e.currentTarget.value })}
            class="input input-bordered input-sm"
          />
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="action">
            操作
          </label>
          <select
            id="action"
            name="action"
            class="select select-bordered select-sm"
            onChange={e =>
              updateInput({ action: e.currentTarget.value === '' ? undefined : (e.currentTarget.value as Action) })}
          >
            <option value="" selected={input().action === undefined}>
              すべて
            </option>
            <For each={ACTION_OPTIONS}>
              {a => (
                <option value={a} selected={input().action === a}>
                  {ACTION_LABEL[a]}
                </option>
              )}
            </For>
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="target_type">
            対象種別
          </label>
          <select
            id="target_type"
            name="target_type"
            class="select select-bordered select-sm"
            onChange={e =>
              updateInput({
                targetType: e.currentTarget.value === '' ? undefined : (e.currentTarget.value as TargetType),
              })}
          >
            <option value="" selected={input().targetType === undefined}>
              すべて
            </option>
            <For each={TARGET_TYPE_OPTIONS}>
              {t => (
                <option value={t} selected={input().targetType === t}>
                  {TARGET_TYPE_LABEL[t]}
                </option>
              )}
            </For>
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="target_id">
            対象ID
          </label>
          <input
            type="text"
            id="target_id"
            name="target_id"
            value={input().targetId}
            onInput={e => updateInput({ targetId: e.currentTarget.value })}
            class="input input-bordered input-sm"
            placeholder="UUID"
          />
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="admin_user_name">
            実行者名
          </label>
          <input
            type="text"
            id="admin_user_name"
            name="admin_user_name"
            value={input().adminUserName}
            onInput={e => updateInput({ adminUserName: e.currentTarget.value })}
            class="input input-bordered input-sm"
            placeholder="部分一致"
          />
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="per_page">
            表示件数
          </label>
          <select
            id="per_page"
            name="per_page"
            class="select select-bordered select-sm"
            onChange={e => updateInput({ perPage: Number(e.currentTarget.value) as PerPage })}
          >
            <For each={PER_PAGE_OPTIONS}>
              {n => (
                <option value={n} selected={input().perPage === n}>
                  {n}件
                </option>
              )}
            </For>
          </select>
        </fieldset>
        <button type="submit" class="btn btn-primary btn-sm mb-1">
          適用
        </button>
        <button type="button" class="btn btn-ghost btn-sm mb-1" onClick={handleReset}>
          リセット
        </button>
      </form>
      <div class="rounded-box border border-base-300 bg-base-100 overflow-x-auto">
        <table class="table table-sm table-zebra md:table-md">
          <thead>
            <tr>
              <th>日時</th>
              <th>実行者</th>
              <th>操作</th>
              <th>対象種別</th>
              <th>対象ID</th>
              <th>詳細</th>
            </tr>
          </thead>
          <tbody>
            <Switch>
              <Match when={data.loading}>
                <ListState state="loading" colSpan={6} />
              </Match>
              <Match when={fetchError()}>
                {message => <ListState state="error" colSpan={6} message={message()} onRetry={() => refetch()} />}
              </Match>
              <Match when={data() && data()!.auditLogs.length === 0}>
                <ListState state="empty" colSpan={6} />
              </Match>
              <Match when={data()}>
                {result => (
                  <For each={result().auditLogs}>
                    {log => (
                      <tr class="hover:bg-primary/30 focus-within:bg-primary/30 transition-colors">
                        <td>{formatter.format(new Date(log.createdAt))}</td>
                        <td>{log.adminUserName}</td>
                        <td>{ACTION_LABEL[log.action as Action] ?? log.action}</td>
                        <td>{TARGET_TYPE_LABEL[log.targetType as TargetType] ?? log.targetType}</td>
                        <td class="font-mono text-xs">{log.targetId}</td>
                        <td>
                          <a
                            href={`/audit-logs/${log.auditLogId}?back=${encodeURIComponent(window.location.search)}`}
                            class="btn btn-ghost btn-xs"
                          >
                            詳細
                          </a>
                        </td>
                      </tr>
                    )}
                  </For>
                )}
              </Match>
            </Switch>
          </tbody>
        </table>
      </div>
      <Show when={!data.loading && !fetchError() && (data()?.maxPage ?? 0) > 1}>
        <Pagination page={params().page} maxPage={data()!.maxPage} onChange={handlePageChange} />
      </Show>
    </>
  );
};
