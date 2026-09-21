import { Match, Show, Switch, createResource, createSignal } from 'solid-js';
import type { ReleaseFormatValue, ReleaseGetResponse } from '../../generated';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import { ColorField } from './ColorField';
import { FormatCheckboxes } from './FormatCheckboxes';
import { MediaEditor, toMediaPayload, toMediumForms } from './MediaEditor';
import type { MediumForm } from './MediaEditor';

interface DetailViewProps {
  releaseId: string;
}

interface ReleaseFormProps {
  data: ReleaseGetResponse;
}

interface FetchOkState {
  status: 'ok';
  data: ReleaseGetResponse;
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

export const DetailView = (props: DetailViewProps) => {
  const [resource, { refetch }] = createResource(async (): Promise<FetchState> => {
    const { data, status } = await client.api.releases({ releaseId: props.releaseId }).get();

    if (status === 401) {
      window.location.href = '/auth/login';
      return { status: 'error' };
    }

    if (status === 404) {
      setFlash('データがありません', 'error');
      window.location.href = '/release-groups';
      return { status: 'error' };
    }

    if (status === 422) {
      setFlash('不正なリクエストです', 'error');
      window.location.href = '/release-groups';
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
      <Match when={loadedData()}>{data => <ReleaseForm data={data()} />}</Match>
    </Switch>
  );
};

const ReleaseForm = (props: ReleaseFormProps) => {
  const groupUrl = `/release-groups/${props.data.release.releaseGroupId}`;

  const [name, setName] = createSignal(props.data.release.name);
  const [releasedOn, setReleasedOn] = createSignal(normalizeDateValue(props.data.release.releasedOn));
  const [description, setDescription] = createSignal(props.data.release.description);
  const [color, setColor] = createSignal(props.data.release.color);
  const [isDisplay, setIsDisplay] = createSignal(props.data.release.isDisplay);
  const [orderNo, setOrderNo] = createSignal(props.data.release.orderNo);
  const [formatValues, setFormatValues] = createSignal<ReleaseFormatValue[]>([...props.data.release.formatValues]);
  const [media, setMedia] = createSignal<MediumForm[]>(toMediumForms(props.data));

  const { formError, setFormError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting: isUpdating, withSubmitting: withUpdating } = createSubmitting();
  const { isSubmitting: isDeleting, withSubmitting: withDeleting } = createSubmitting();

  const handleSubmit = withUpdating(async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const releaseId = props.data.release.releaseId;

    if (!releaseId) {
      setFormError('更新対象のリリースIDを取得できませんでした');
      return;
    }

    const { data, error, status } = await client.api.releases({ releaseId }).put({
      name: name().trim(),
      releasedOn: releasedOn(),
      description: description(),
      color: color(),
      isDisplay: isDisplay(),
      orderNo: orderNo(),
      formatValues: formatValues(),
      media: toMediaPayload(media()),
    });

    if (data) {
      setFlash('更新しました');
      window.location.href = groupUrl;
      return;
    }

    if (status === 404) {
      setFlash('データがありません', 'error');
      window.location.href = groupUrl;
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

    const releaseId = props.data.release.releaseId;

    if (!releaseId) {
      setFormError('削除対象のリリースIDを取得できませんでした');
      return;
    }

    const { error, status } = await client.api.releases({ releaseId }).delete();

    if (error) {
      handleError(status, error);
      return;
    }

    setFlash('削除しました');
    window.location.href = groupUrl;
  });

  return (
    <>
      <a href={groupUrl} class="btn btn-ghost btn-sm mb-4">
        ← グループ詳細に戻る
      </a>
      <p class="mb-4 text-sm text-base-content/70">
        リリースグループ:&nbsp;
        <a href={groupUrl} class="link link-hover font-medium text-base-content">
          {props.data.releaseGroupTitle}
        </a>
      </p>
      <FormError message={formError()} onClose={clearErrors} />
      <div class="max-w-5xl space-y-6">
        <form onSubmit={handleSubmit}>
          <fieldset class="fieldset rounded-box border border-base-300 bg-base-200 p-6">
            <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
            <div class="grid gap-5 md:grid-cols-2">
              <div>
                <label class="label">版名(任意)</label>
                <input
                  type="text"
                  class="input w-full"
                  value={name()}
                  onInput={e => setName(e.currentTarget.value)}
                  placeholder="通常盤 / 初回限定盤 / 配信 など"
                  classList={{ 'input-error': !!getFieldError('name') }}
                />
                <Show when={getFieldError('name')}>
                  {message => <p class="mt-1 text-xs text-error">{message()}</p>}
                </Show>
              </div>

              <div>
                <label class="label">発売日</label>
                <input
                  type="date"
                  class="input w-full"
                  value={releasedOn()}
                  onInput={e => setReleasedOn(e.currentTarget.value)}
                  classList={{ 'input-error': !!getFieldError('releasedOn') }}
                />
                <Show when={getFieldError('releasedOn')}>
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

              <ColorField value={color()} onChange={setColor} fieldError={getFieldError('color')} />

              <div class="md:col-span-2">
                <FormatCheckboxes
                  formatValues={formatValues()}
                  onChange={setFormatValues}
                  fieldError={getFieldError('formatValues')}
                />
              </div>

              <div class="md:col-span-2">
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

        <MediaEditor media={media()} onChange={setMedia} fieldError={getFieldError('media')} />

        <fieldset class="rounded-box border border-error/20 bg-error/5 p-6">
          <legend class="px-2 text-sm font-semibold text-error">危険な操作</legend>
          <p class="mt-1 text-sm text-base-content/60">この操作は取り消せません。</p>
          <div class="mt-4">
            <button
              onClick={handleDelete}
              class="btn btn-outline btn-error btn-sm"
              disabled={isDeleting() || isUpdating()}
            >
              {isDeleting() ? '削除中...' : 'このリリースを削除する'}
            </button>
          </div>
        </fieldset>
      </div>
    </>
  );
};
