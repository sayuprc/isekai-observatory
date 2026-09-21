import { Show, createResource, createSignal, For, Match, Switch } from 'solid-js';
import type { AuditAction, AuditTargetType } from '../../generated';
import { client } from '../../utils/client';
import { formatter } from '../../utils/date';
import { ListState } from '../ListState';
import { Pagination } from '../Pagination';

const PER_PAGE_OPTIONS = [25, 50, 100] as const;
type PerPage = (typeof PER_PAGE_OPTIONS)[number];

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
  'Song',
  'SongTag',
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
  Song: '楽曲',
  SongTag: '楽曲タグ',
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

const isAction = (value: string | null): value is Action => {
  return value !== null && (ACTION_OPTIONS as readonly string[]).includes(value);
};

const isTargetType = (value: string | null): value is TargetType => {
  return value !== null && (TARGET_TYPE_OPTIONS as readonly string[]).includes(value);
};

const getInitialParams = (): Params => {
  const params = new URLSearchParams(window.location.search);
  const perPageRaw = Number(params.get('per_page'));
  const action = params.get('action');
  const targetType = params.get('target_type');
  return {
    from: params.get('from') ?? DEFAULT_PARAMS.from,
    to: params.get('to') ?? DEFAULT_PARAMS.to,
    action: isAction(action) ? action : undefined,
    targetType: isTargetType(targetType) ? targetType : undefined,
    targetId: params.get('target_id') ?? DEFAULT_PARAMS.targetId,
    adminUserName: params.get('admin_user_name') ?? DEFAULT_PARAMS.adminUserName,
    page: Number(params.get('page') ?? String(DEFAULT_PARAMS.page)) || DEFAULT_PARAMS.page,
    perPage: (PER_PAGE_OPTIONS.includes(perPageRaw as PerPage) ? perPageRaw : DEFAULT_PARAMS.perPage) as PerPage,
  };
};

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
  const initial = getInitialParams();

  const [from, setFrom] = createSignal(initial.from);
  const [to, setTo] = createSignal(initial.to);
  const [action, setAction] = createSignal<Action | undefined>(initial.action);
  const [targetType, setTargetType] = createSignal<TargetType | undefined>(initial.targetType);
  const [targetId, setTargetId] = createSignal(initial.targetId);
  const [adminUserName, setAdminUserName] = createSignal(initial.adminUserName);
  const [page, setPage] = createSignal(initial.page);
  const [perPage, setPerPage] = createSignal<PerPage>(initial.perPage);

  const [inputFrom, setInputFrom] = createSignal(initial.from);
  const [inputTo, setInputTo] = createSignal(initial.to);
  const [inputAction, setInputAction] = createSignal<Action | undefined>(initial.action);
  const [inputTargetType, setInputTargetType] = createSignal<TargetType | undefined>(initial.targetType);
  const [inputTargetId, setInputTargetId] = createSignal(initial.targetId);
  const [inputAdminUserName, setInputAdminUserName] = createSignal(initial.adminUserName);
  const [inputPerPage, setInputPerPage] = createSignal<PerPage>(initial.perPage);

  const updateUrl = (params: Params) => {
    const searchParams = new URLSearchParams();
    if (params.from) searchParams.set('from', params.from);
    if (params.to) searchParams.set('to', params.to);
    if (params.action) searchParams.set('action', params.action);
    if (params.targetType) searchParams.set('target_type', params.targetType);
    if (params.targetId) searchParams.set('target_id', params.targetId);
    if (params.adminUserName) searchParams.set('admin_user_name', params.adminUserName);
    searchParams.set('page', String(params.page));
    searchParams.set('per_page', String(params.perPage));
    history.pushState(null, '', `?${searchParams.toString()}`);
  };

  const [fetchError, setFetchError] = createSignal<string | null>(null);

  const [data, { refetch }] = createResource(
    () => ({
      from: from(),
      to: to(),
      action: action(),
      targetType: targetType(),
      targetId: targetId(),
      adminUserName: adminUserName(),
      page: page(),
      perPage: perPage(),
    }),
    async (params) => {
      setFetchError(null);

      const { data, status } = await client.api['audit-logs'].search.get({
        query: {
          from: toIsoOrEmpty(params.from) || undefined,
          to: toIsoOrEmpty(params.to) || undefined,
          action: params.action,
          target_type: params.targetType,
          target_id: params.targetId || undefined,
          admin_user_name: params.adminUserName || undefined,
          page: params.page,
          per_page: params.perPage,
        },
      });

      if (status === 401) {
        window.location.href = '/auth/login';
        return;
      }

      if (status === 403) {
        setFetchError('監査ログの閲覧権限がありません。');
        return;
      }

      if (!data) {
        setFetchError('データの取得に失敗しました。再度お試しください。');
        return;
      }

      return data;
    },
  );

  const handleSearch = (e: Event) => {
    e.preventDefault();

    const newPage = 1;

    setFrom(inputFrom());
    setTo(inputTo());
    setAction(inputAction());
    setTargetType(inputTargetType());
    setTargetId(inputTargetId());
    setAdminUserName(inputAdminUserName());
    setPerPage(inputPerPage());
    setPage(newPage);
    updateUrl({
      from: inputFrom(),
      to: inputTo(),
      action: inputAction(),
      targetType: inputTargetType(),
      targetId: inputTargetId(),
      adminUserName: inputAdminUserName(),
      page: newPage,
      perPage: inputPerPage(),
    });
  };

  const handlePageChange = (next: number) => {
    setPage(next);
    updateUrl({
      from: from(),
      to: to(),
      action: action(),
      targetType: targetType(),
      targetId: targetId(),
      adminUserName: adminUserName(),
      page: next,
      perPage: perPage(),
    });
  };

  const handleReset = () => {
    setInputFrom(DEFAULT_PARAMS.from);
    setInputTo(DEFAULT_PARAMS.to);
    setInputAction(DEFAULT_PARAMS.action);
    setInputTargetType(DEFAULT_PARAMS.targetType);
    setInputTargetId(DEFAULT_PARAMS.targetId);
    setInputAdminUserName(DEFAULT_PARAMS.adminUserName);
    setInputPerPage(DEFAULT_PARAMS.perPage);

    setFrom(DEFAULT_PARAMS.from);
    setTo(DEFAULT_PARAMS.to);
    setAction(DEFAULT_PARAMS.action);
    setTargetType(DEFAULT_PARAMS.targetType);
    setTargetId(DEFAULT_PARAMS.targetId);
    setAdminUserName(DEFAULT_PARAMS.adminUserName);
    setPerPage(DEFAULT_PARAMS.perPage);
    setPage(DEFAULT_PARAMS.page);

    updateUrl(DEFAULT_PARAMS);
  };

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
            value={inputFrom()}
            onInput={e => setInputFrom(e.currentTarget.value)}
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
            value={inputTo()}
            onInput={e => setInputTo(e.currentTarget.value)}
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
            onChange={e => setInputAction(e.currentTarget.value === '' ? undefined : (e.currentTarget.value as Action))}
          >
            <option value="" selected={inputAction() === undefined}>
              すべて
            </option>
            <For each={ACTION_OPTIONS}>
              {a => (
                <option value={a} selected={inputAction() === a}>
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
              setInputTargetType(e.currentTarget.value === '' ? undefined : (e.currentTarget.value as TargetType))}
          >
            <option value="" selected={inputTargetType() === undefined}>
              すべて
            </option>
            <For each={TARGET_TYPE_OPTIONS}>
              {t => (
                <option value={t} selected={inputTargetType() === t}>
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
            value={inputTargetId()}
            onInput={e => setInputTargetId(e.currentTarget.value)}
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
            value={inputAdminUserName()}
            onInput={e => setInputAdminUserName(e.currentTarget.value)}
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
            onChange={e => setInputPerPage(Number(e.currentTarget.value) as PerPage)}
          >
            <For each={PER_PAGE_OPTIONS}>
              {n => (
                <option value={n} selected={inputPerPage() === n}>
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
        <Pagination page={page()} maxPage={data()!.maxPage} onChange={handlePageChange} />
      </Show>
    </>
  );
};
