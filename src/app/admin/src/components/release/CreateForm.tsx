import { Match, Show, Switch, createResource, createSignal } from 'solid-js';
import type { ReleaseFormatValue } from '../../generated';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import { ColorField } from './ColorField';
import { FormatCheckboxes } from './FormatCheckboxes';
import { MediaEditor, toMediaPayload, toMediumForms } from './MediaEditor';
import type { MediumForm } from './MediaEditor';

interface CreateParams {
  releaseGroupId: string;
  sourceReleaseId: string;
}

/** フォームの初期値。コピー元があればその内容、なければ空 */
interface InitialValues {
  name: string;
  releasedOn: string;
  description: string;
  color: string;
  isDisplay: boolean;
  orderNo: number;
  formatValues: ReleaseFormatValue[];
  media: MediumForm[];
}

const EMPTY_INITIAL_VALUES: InitialValues = {
  name: '',
  releasedOn: '',
  description: '',
  color: '#989899',
  isDisplay: true,
  orderNo: 1,
  formatValues: [1],
  media: [{ name: '', tracks: [] }],
};

const getCreateParams = (): CreateParams => {
  if (typeof window === 'undefined') {
    return { releaseGroupId: '', sourceReleaseId: '' };
  }

  const params = new URLSearchParams(window.location.search);

  return {
    releaseGroupId: params.get('releaseGroupId') ?? '',
    sourceReleaseId: params.get('sourceReleaseId') ?? '',
  };
};

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

interface FetchOkState {
  status: 'ok';
  initialValues: InitialValues;
}

interface FetchErrorState {
  status: 'error';
}

type FetchState = FetchOkState | FetchErrorState;

export const CreateForm = () => {
  const params = getCreateParams();

  const [resource, { refetch }] = createResource(async (): Promise<FetchState> => {
    if (params.sourceReleaseId === '') {
      return { status: 'ok', initialValues: EMPTY_INITIAL_VALUES };
    }

    const { data, status } = await client.api.releases({ releaseId: params.sourceReleaseId }).get();

    if (status === 401) {
      window.location.href = '/auth/login';
      return { status: 'error' };
    }

    if (!data) {
      return { status: 'error' };
    }

    return {
      status: 'ok',
      initialValues: {
        name: data.release.name,
        releasedOn: normalizeDateValue(data.release.releasedOn),
        description: data.release.description,
        color: data.release.color,
        isDisplay: data.release.isDisplay,
        orderNo: data.release.orderNo,
        formatValues: [...data.release.formatValues],
        media: toMediumForms(data),
      },
    };
  });

  const loadedInitialValues = () => {
    const state = resource();
    return state?.status === 'ok' ? state.initialValues : undefined;
  };

  return (
    <Show
      when={params.releaseGroupId !== ''}
      fallback={(
        <div class="flex flex-col items-start gap-3">
          <p class="text-error">リリースグループが指定されていません。グループ詳細から追加してください。</p>
          <a href="/release-groups" class="btn btn-outline btn-sm">
            リリースグループ一覧へ
          </a>
        </div>
      )}
    >
      <Switch>
        <Match when={resource.loading}>
          <div
            class="flex items-center justify-center gap-3 py-10 text-base-content/70"
            role="status"
            aria-live="polite"
          >
            <span class="loading loading-spinner loading-md" aria-hidden="true" />
            <span>読み込み中...</span>
          </div>
        </Match>
        <Match when={resource.error || resource()?.status === 'error'}>
          <div class="flex flex-col items-start gap-3">
            <p class="text-error">コピー元リリースの取得に失敗しました。</p>
            <button type="button" class="btn btn-outline btn-sm" onClick={() => refetch()}>
              再試行
            </button>
          </div>
        </Match>
        <Match when={loadedInitialValues()}>
          {initialValues => <ReleaseCreateForm releaseGroupId={params.releaseGroupId} initialValues={initialValues()} />}
        </Match>
      </Switch>
    </Show>
  );
};

interface ReleaseCreateFormProps {
  releaseGroupId: string;
  initialValues: InitialValues;
}

const ReleaseCreateForm = (props: ReleaseCreateFormProps) => {
  const [name, setName] = createSignal(props.initialValues.name);
  const [releasedOn, setReleasedOn] = createSignal(props.initialValues.releasedOn);
  const [description, setDescription] = createSignal(props.initialValues.description);
  const [color, setColor] = createSignal(props.initialValues.color);
  const [isDisplay, setIsDisplay] = createSignal(props.initialValues.isDisplay);
  const [orderNo, setOrderNo] = createSignal(props.initialValues.orderNo);
  const [formatValues, setFormatValues] = createSignal<ReleaseFormatValue[]>(props.initialValues.formatValues);
  const [media, setMedia] = createSignal<MediumForm[]>(props.initialValues.media);

  const { formError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  const groupUrl = `/release-groups/${props.releaseGroupId}`;

  const handleSubmit = withSubmitting(async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const { data, error, status } = await client.api.releases.post({
      releaseGroupId: props.releaseGroupId,
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
      setFlash('作成しました');
      window.location.href = groupUrl;
      return;
    }

    handleError(status, error);
  });

  return (
    <form onSubmit={handleSubmit}>
      <a href={groupUrl} class="btn btn-ghost btn-sm mb-4">
        ← グループ詳細に戻る
      </a>
      <FormError message={formError()} onClose={clearErrors} />
      <div class="max-w-5xl space-y-6">
        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
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
                required
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
                required
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
        </fieldset>

        <MediaEditor media={media()} onChange={setMedia} fieldError={getFieldError('media')} />

        <div class="flex justify-end">
          <button class="btn btn-primary" disabled={isSubmitting()}>
            {isSubmitting() ? '作成中...' : '作成'}
          </button>
        </div>
      </div>
    </form>
  );
};
