import { For, Match, Show, Switch, createResource, createSignal } from 'solid-js';
import type {
  ReleaseFormatValue,
  ReleaseGroupGetResponse,
  ReleaseGroupReferencedRelease,
  ReleaseGroupTypeValue,
} from '../../generated';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';

const RELEASE_GROUP_TYPE_OPTIONS: Array<{ value: ReleaseGroupTypeValue; label: string }> = [
  { value: 1, label: 'シングル' },
  { value: 2, label: 'アルバム' },
  { value: 3, label: 'EP' },
  { value: 99, label: 'その他' },
];

const RELEASE_FORMAT_LABELS: Record<ReleaseFormatValue, string> = {
  1: '配信',
  2: 'CD',
  3: 'DVD',
  4: 'Blu-ray',
  99: 'その他',
};

/** 版名は空になりうるため、リンクのラベルとして意味を持つ代替文言を出す */
const ReleaseNameLink = (props: { release: ReleaseGroupReferencedRelease }) => (
  <a href={`/releases/${props.release.releaseId}`} class="link link-hover font-medium">
    <Show when={props.release.name !== ''} fallback={<span class="text-base-content/60">版名なし</span>}>
      {props.release.name}
    </Show>
  </a>
);

const ReleaseFormatBadges = (props: { release: ReleaseGroupReferencedRelease }) => (
  <div class="flex flex-wrap gap-1">
    <Show
      when={props.release.formatValues.length > 0}
      fallback={<span class="text-sm text-base-content/60">—</span>}
    >
      <For each={props.release.formatValues}>
        {formatValue => (
          <span class="badge badge-outline badge-sm">{RELEASE_FORMAT_LABELS[formatValue] ?? '不明'}</span>
        )}
      </For>
    </Show>
  </div>
);

const ReleaseDisplayBadge = (props: { release: ReleaseGroupReferencedRelease }) => (
  <span class={`badge badge-sm ${props.release.isDisplay ? 'badge-success badge-soft' : 'badge-ghost'}`}>
    {props.release.isDisplay ? '表示する' : '表示しない'}
  </span>
);

const CopyReleaseLink = (props: { releaseGroupId: string; releaseId: string }) => (
  <a
    href={`/releases/create?releaseGroupId=${props.releaseGroupId}&sourceReleaseId=${props.releaseId}`}
    class="btn btn-ghost btn-xs whitespace-nowrap"
  >
    コピーして追加
  </a>
);

interface DetailViewProps {
  releaseGroupId: string;
}

interface ReleaseGroupFormProps {
  data: ReleaseGroupGetResponse;
}

interface FetchOkState {
  status: 'ok';
  data: ReleaseGroupGetResponse;
}

interface FetchErrorState {
  status: 'error';
}

type FetchState = FetchOkState | FetchErrorState;

const normalizeDateValue = (value: unknown): string => {
  if (value instanceof Date) {
    return Number.isNaN(value.getTime()) ? '' : value.toISOString().slice(0, 10);
  }

  if (typeof value !== 'string') {
    return '';
  }

  if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
    return value;
  }

  const parsed = new Date(value);

  return Number.isNaN(parsed.getTime()) ? '' : parsed.toISOString().slice(0, 10);
};

const getListUrl = () => {
  if (typeof window === 'undefined') {
    return '/release-groups';
  }

  const back = new URLSearchParams(window.location.search).get('back') ?? '';

  if (!back.startsWith('?')) {
    return '/release-groups';
  }

  try {
    const query = new URLSearchParams(back.slice(1)).toString();
    return query ? `/release-groups?${query}` : '/release-groups';
  } catch {
    return '/release-groups';
  }
};

export const DetailView = (props: DetailViewProps) => {
  const listUrl = getListUrl();

  const [resource, { refetch }] = createResource(async (): Promise<FetchState> => {
    const { data, status } = await client.api['release-groups']({ releaseGroupId: props.releaseGroupId }).get();

    if (status === 401) {
      window.location.href = '/auth/login';
      return { status: 'error' };
    }

    if (status === 404) {
      setFlash('データがありません', 'error');
      window.location.href = listUrl;
      return { status: 'error' };
    }

    if (status === 422) {
      setFlash('不正なリクエストです', 'error');
      window.location.href = listUrl;
      return { status: 'error' };
    }

    if (!data) {
      return { status: 'error' };
    }

    return { status: 'ok', data };
  });

  const loadedData = () => {
    const state = resource();
    return state?.status === 'ok' ? state.data : undefined;
  };

  return (
    <Switch>
      <Match when={resource.loading}>
        <div class="flex items-center justify-center gap-3 py-10 text-base-content/70" role="status" aria-live="polite">
          <span class="loading loading-spinner loading-md" aria-hidden="true" />
          <span>読み込み中...</span>
        </div>
      </Match>
      <Match when={resource.error || resource()?.status === 'error'}>
        <div class="flex flex-col items-start gap-3">
          <p class="text-error">データの取得に失敗しました。</p>
          <button type="button" class="btn btn-outline btn-sm" onClick={() => refetch()}>
            再試行
          </button>
        </div>
      </Match>
      <Match when={loadedData()}>{data => <ReleaseGroupForm data={data()} />}</Match>
    </Switch>
  );
};

const ReleaseGroupForm = (props: ReleaseGroupFormProps) => {
  const listUrl = getListUrl();
  const releaseGroupId = props.data.releaseGroup.releaseGroupId;

  const [title, setTitle] = createSignal(props.data.releaseGroup.title);
  const [typeValue, setTypeValue] = createSignal<ReleaseGroupTypeValue>(props.data.releaseGroup.typeValue);
  const [description, setDescription] = createSignal(props.data.releaseGroup.description);
  const [isDisplay, setIsDisplay] = createSignal(props.data.releaseGroup.isDisplay);
  const [orderNo, setOrderNo] = createSignal(props.data.releaseGroup.orderNo);

  const { formError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting: isUpdating, withSubmitting: withUpdating } = createSubmitting();
  const { isSubmitting: isDeleting, withSubmitting: withDeleting } = createSubmitting();

  const handleSubmit = withUpdating(async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const { data, error, status } = await client.api['release-groups']({ releaseGroupId }).put({
      title: title(),
      typeValue: typeValue(),
      description: description(),
      isDisplay: isDisplay(),
      orderNo: orderNo(),
    });

    if (data) {
      setFlash('更新しました');
      window.location.href = listUrl;
      return;
    }

    if (status === 404) {
      setFlash('データがありません', 'error');
      window.location.href = listUrl;
      return;
    }

    handleError(status, error);
  });

  const handleDelete = withDeleting(async (e: Event) => {
    e.preventDefault();

    if (!window.confirm('削除します。よろしいですか？')) {
      return;
    }

    clearErrors();

    const { error, status } = await client.api['release-groups']({ releaseGroupId }).delete();

    if (error) {
      handleError(status, error);
      return;
    }

    setFlash('削除しました');
    window.location.href = listUrl;
  });

  return (
    <>
      <a href={listUrl} class="btn btn-ghost btn-sm mb-4">
        ← 一覧に戻る
      </a>
      <FormError message={formError()} onClose={clearErrors} />
      <div class="max-w-5xl space-y-6">
        <form onSubmit={handleSubmit}>
          <fieldset class="fieldset rounded-box border border-base-300 bg-base-200 p-6">
            <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
            <div class="grid gap-5 md:grid-cols-2">
              <div>
                <label class="label">タイトル</label>
                <input
                  type="text"
                  class="input w-full"
                  value={title()}
                  onInput={e => setTitle(e.currentTarget.value)}
                  classList={{ 'input-error': !!getFieldError('title') }}
                />
                <Show when={getFieldError('title')}>
                  {message => <p class="mt-1 text-xs text-error">{message()}</p>}
                </Show>
              </div>

              <div>
                <label class="label">種別</label>
                <select
                  class="select select-bordered w-full"
                  value={String(typeValue())}
                  onChange={e => setTypeValue(Number(e.currentTarget.value) as ReleaseGroupTypeValue)}
                >
                  <For each={RELEASE_GROUP_TYPE_OPTIONS}>
                    {option => <option value={option.value}>{option.label}</option>}
                  </For>
                </select>
                <Show when={getFieldError('typeValue')}>
                  {message => <p class="mt-1 text-xs text-error">{message()}</p>}
                </Show>
              </div>

              <div class="md:col-span-2">
                <label class="label">説明</label>
                <textarea
                  class="textarea textarea-bordered min-h-32 w-full"
                  value={description()}
                  onInput={e => setDescription(e.currentTarget.value)}
                  classList={{ 'textarea-error': !!getFieldError('description') }}
                />
                <Show when={getFieldError('description')}>
                  {message => <p class="mt-1 text-xs text-error">{message()}</p>}
                </Show>
              </div>

              <div>
                <label class="label">表示順</label>
                <input
                  type="number"
                  min="1"
                  step="1"
                  class="input w-full"
                  value={orderNo()}
                  onInput={e => setOrderNo(Number(e.currentTarget.value))}
                  classList={{ 'input-error': !!getFieldError('orderNo') }}
                />
                <Show when={getFieldError('orderNo')}>
                  {message => <p class="mt-1 text-xs text-error">{message()}</p>}
                </Show>
              </div>

              <div class="md:col-span-2">
                <label class="label">表示設定</label>
                <select
                  class="select select-bordered w-full"
                  value={String(isDisplay())}
                  onChange={e => setIsDisplay(e.currentTarget.value === 'true')}
                  classList={{ 'select-error': !!getFieldError('isDisplay') }}
                >
                  <option value="true">表示する</option>
                  <option value="false">表示しない</option>
                </select>
                <Show when={getFieldError('isDisplay')}>
                  {message => <p class="mt-1 text-xs text-error">{message()}</p>}
                </Show>
              </div>
            </div>

            <div class="mt-6 flex justify-end">
              <button class="btn btn-primary" disabled={isUpdating() || isDeleting()}>
                {isUpdating() ? '更新中...' : '更新'}
              </button>
            </div>
          </fieldset>
        </form>

        <fieldset class="rounded-box border border-base-300 bg-base-200 p-6">
          <legend class="px-2 text-sm font-semibold text-base-content/70">リリース(版)</legend>
          <div class="mb-4 flex justify-end">
            <a href={`/releases/create?releaseGroupId=${releaseGroupId}`} class="btn btn-primary btn-sm">
              リリースを追加
            </a>
          </div>
          <Show
            when={props.data.releases.length > 0}
            fallback={<p class="text-sm text-base-content/60">リリースはまだ登録されていません。</p>}
          >
            <ul class="flex flex-col gap-2 md:hidden">
              <For each={props.data.releases}>
                {release => (
                  <li
                    class="rounded-box border-y border-r border-l-4 border-base-300 bg-base-100 p-3"
                    style={{ 'border-left-color': release.color }}
                  >
                    <ReleaseNameLink release={release} />
                    <div class="mt-1 flex flex-wrap gap-x-3 text-sm text-base-content/60">
                      <span>{normalizeDateValue(release.releasedOn)}</span>
                      <span>表示順 {release.orderNo}</span>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1">
                      <ReleaseFormatBadges release={release} />
                      <ReleaseDisplayBadge release={release} />
                    </div>
                    <div class="mt-2 flex justify-end">
                      <CopyReleaseLink releaseGroupId={releaseGroupId} releaseId={release.releaseId} />
                    </div>
                  </li>
                )}
              </For>
            </ul>
            <div class="hidden overflow-x-auto rounded-box border border-base-300 bg-base-100 md:block">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>版名</th>
                    <th>発売日</th>
                    <th>表示順</th>
                    <th>媒体</th>
                    <th>表示設定</th>
                    <th class="text-right">操作</th>
                  </tr>
                </thead>
                <tbody>
                  <For each={props.data.releases}>
                    {release => (
                      <tr>
                        <td
                          class="min-w-40 border-l-4 font-medium"
                          style={{ 'border-left-color': release.color }}
                          title={`代表色 ${release.color}`}
                        >
                          <ReleaseNameLink release={release} />
                        </td>
                        <td class="whitespace-nowrap text-sm">{normalizeDateValue(release.releasedOn)}</td>
                        <td class="text-sm">{release.orderNo}</td>
                        <td>
                          <ReleaseFormatBadges release={release} />
                        </td>
                        <td class="whitespace-nowrap">
                          <ReleaseDisplayBadge release={release} />
                        </td>
                        <td class="text-right">
                          <div class="flex justify-end">
                            <CopyReleaseLink releaseGroupId={releaseGroupId} releaseId={release.releaseId} />
                          </div>
                        </td>
                      </tr>
                    )}
                  </For>
                </tbody>
              </table>
            </div>
          </Show>
        </fieldset>

        <fieldset class="rounded-box border border-error/20 bg-error/5 p-6">
          <legend class="px-2 text-sm font-semibold text-error">危険な操作</legend>
          <p class="mt-1 text-sm text-base-content/60">
            この操作は取り消せません。リリースが登録されている場合は削除できません。
          </p>
          <div class="mt-4">
            <button
              onClick={handleDelete}
              class="btn btn-outline btn-error btn-sm"
              disabled={isDeleting() || isUpdating()}
            >
              {isDeleting() ? '削除中...' : 'このリリースグループを削除する'}
            </button>
          </div>
        </fieldset>
      </div>
    </>
  );
};
