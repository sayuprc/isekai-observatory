import { createResource, Match, Show, Switch } from 'solid-js';
import type { SongTag } from '../../generated';
import { redirectToLogin } from '../../utils/auth-redirect';
import { client } from '../../utils/client';
import { createFormDirtyTracker, discardChanges } from '../../utils/dirty';
import { createFormErrors } from '../../utils/form-error';
import { getListUrl } from '../../utils/list-url';
import { createSubmitting } from '../../utils/use-submitting';
import { ActionMenu } from '../ActionMenu';
import { EntityHeader } from '../EntityHeader';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import { FormRow } from '../FormRow';

interface DetailViewProps {
  songTagId: string;
}

interface EditableFormProps {
  data: { tag: SongTag };
}

interface FetchOkState {
  status: 'ok';
  data: { tag: SongTag };
}

interface FetchErrorState {
  status: 'error';
}

type FetchState = FetchOkState | FetchErrorState;

export const DetailView = (props: DetailViewProps) => {
  const listUrl = getListUrl('/song-tags');

  const [resource, { refetch }] = createResource(async (): Promise<FetchState> => {
    const { data, status } = await client.api['song-tags']({ songTagId: props.songTagId }).get();

    if (status === 401) {
      redirectToLogin();
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
      <Match when={loadedData()}>{(data) => <EditableForm data={data()} />}</Match>
    </Switch>
  );
};

export const EditableForm = (props: EditableFormProps) => {
  const listUrl = getListUrl('/song-tags');

  const { formError, setFormError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();
  const { isDirty, allowLeave, bindForm } = createFormDirtyTracker();

  const handleUpdate = withSubmitting(async (e: SubmitEvent) => {
    e.preventDefault();
    clearErrors();

    const formData = new FormData(e.currentTarget as HTMLFormElement);

    const songTagId = props.data.tag.songTagId;

    if (!songTagId) {
      setFormError('更新対象の楽曲タグIDを取得できませんでした');
      return;
    }

    const { data, error, status } = await client.api['song-tags']({ songTagId }).put({
      name: formData.get('name')?.toString() ?? '',
      orderNo: Number(formData.get('orderNo')),
    });

    if (data) {
      setFlash('更新しました');
      allowLeave();
      window.location.href = listUrl;
      return;
    }

    if (status === 404) {
      setFlash('データがありません', 'error');
      allowLeave();
      window.location.href = listUrl;
      return;
    }

    handleError(status, error);
  });

  const handleDelete = withSubmitting(async () => {
    if (!window.confirm('削除します。よろしいですか？')) {
      return;
    }

    clearErrors();

    const songTagId = props.data.tag.songTagId;

    if (!songTagId) {
      setFormError('削除対象の楽曲タグIDを取得できませんでした');
      return;
    }

    const { error, status } = await client.api['song-tags']({ songTagId }).delete();

    if (error) {
      handleError(status, error);
      return;
    }

    setFlash('削除しました');
    allowLeave();
    window.location.href = listUrl;
  });

  return (
    <>
      <EntityHeader
        breadcrumb={{ href: listUrl, label: '楽曲タグ' }}
        title={props.data.tag.name}
        formId="song-tag-form"
        isDirty={isDirty()}
        isSubmitting={isSubmitting()}
        submitLabel="保存"
        submittingLabel="保存中..."
        onDiscard={() => discardChanges(allowLeave)}
        menu={
          <ActionMenu
            label="その他の操作"
            items={[
              { label: 'この楽曲タグを削除する', danger: true, disabled: isSubmitting(), onSelect: handleDelete },
            ]}
          />
        }
      />
      <FormError message={formError()} onClose={clearErrors} />
      <form ref={bindForm} id="song-tag-form" class="max-w-4xl" onSubmit={handleUpdate}>
        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border px-6 py-3">
          <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
          <FormRow label="楽曲タグ名" for="name">
            <input
              id="name"
              type="text"
              class="input w-full"
              name="name"
              required
              value={props.data.tag.name}
              classList={{ 'input-error': !!getFieldError('name') }}
            />
            <Show when={getFieldError('name')}>{(message) => <p class="text-xs text-error">{message()}</p>}</Show>
          </FormRow>
          <FormRow label="表示順" for="orderNo">
            <input
              id="orderNo"
              type="number"
              class="input w-40"
              name="orderNo"
              required
              min="1"
              value={props.data.tag.orderNo}
              classList={{ 'input-error': !!getFieldError('orderNo') }}
            />
            <Show when={getFieldError('orderNo')}>{(message) => <p class="text-xs text-error">{message()}</p>}</Show>
          </FormRow>
        </fieldset>
      </form>
    </>
  );
};
