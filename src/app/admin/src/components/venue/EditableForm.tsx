import { Match, Switch, createResource, createSignal } from 'solid-js';
import type { Venue, VenueKindValue } from '../../generated';
import { validateVenueName } from '../../schemas/venue';
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
import { VenueFields } from './VenueFields';

interface DetailViewProps {
  venueId: string;
}
interface EditableFormProps {
  data: { venue: Venue };
}
type FetchState = { status: 'ok'; data: { venue: Venue } } | { status: 'forbidden' } | { status: 'error' };
type Payload = { name: string; kind: VenueKindValue };

export const DetailView = (props: DetailViewProps) => {
  const listUrl = getListUrl('/venues');
  const [resource, { refetch }] = createResource(async (): Promise<FetchState> => {
    const { data, status } = await client.api.venues({ venueId: props.venueId }).get();
    if (status === 401) {
      redirectToLogin();
      return { status: 'error' };
    }
    if (status === 403) return { status: 'forbidden' };
    if (status === 404 || status === 422) {
      setFlash(status === 404 ? 'データがありません' : '不正なリクエストです', 'error');
      window.location.href = listUrl;
      return { status: 'error' };
    }
    return data ? { status: 'ok', data } : { status: 'error' };
  });

  const loadedData = () => {
    const state = resource();
    return state?.status === 'ok' ? state.data : undefined;
  };

  return (
    <Switch>
      <Match when={resource.loading}>
        <div class="flex items-center justify-center gap-3 py-10" role="status">
          <span class="loading loading-spinner loading-md" />
          読み込み中...
        </div>
      </Match>
      <Match when={resource()?.status === 'forbidden'}>
        <div class="alert alert-error">開催先の閲覧権限がありません。</div>
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

const EditableForm = (props: EditableFormProps) => {
  const listUrl = getListUrl('/venues');
  const { formError, setFormError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();
  const venueId = props.data.venue.venueId;
  const [kind, setKind] = createSignal<VenueKindValue>(props.data.venue.kind);
  const { isDirty, allowLeave, bindForm } = createFormDirtyTracker(kind);

  const save = async (payload: Payload) => {
    const { data, error, status } = await client.api.venues({ venueId }).put(payload);
    if (data) {
      setFlash('更新しました');
      allowLeave();
      window.location.href = listUrl;
      return;
    }
    if (status === 403) {
      setFormError('開催先の編集権限がありません');
      return;
    }
    if (status === 404) {
      setFlash('データがありません', 'error');
      allowLeave();
      window.location.href = listUrl;
      return;
    }
    handleError(status, error);
  };

  const handleUpdate = withSubmitting(async (event: SubmitEvent) => {
    event.preventDefault();
    clearErrors();
    const formData = new FormData(event.currentTarget as HTMLFormElement);
    const payload: Payload = {
      name: formData.get('name')?.toString() ?? '',
      kind: Number(formData.get('kind')?.toString() ?? '1') as VenueKindValue,
    };
    const nameError = validateVenueName(payload.name);
    if (nameError) {
      setFormError(nameError);
      return;
    }

    await save(payload);
  });

  const handleDelete = withSubmitting(async () => {
    if (!window.confirm('削除します。よろしいですか？')) return;
    const { error, status } = await client.api.venues({ venueId }).delete();
    if (status === 403) {
      setFormError('開催先の編集権限がありません');
      return;
    }
    if (status === 404) {
      setFlash('データがありません', 'error');
      allowLeave();
      window.location.href = listUrl;
      return;
    }
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
        breadcrumb={{ href: listUrl, label: '開催先' }}
        title={props.data.venue.name}
        formId="venue-form"
        isDirty={isDirty()}
        isSubmitting={isSubmitting()}
        submitLabel="保存"
        submittingLabel="保存中..."
        onDiscard={() => discardChanges(allowLeave)}
        menu={
          <ActionMenu
            label="その他の操作"
            items={[{ label: 'この開催先を削除する', danger: true, disabled: isSubmitting(), onSelect: handleDelete }]}
          />
        }
      />
      <FormError message={formError()} onClose={clearErrors} />
      <form ref={bindForm} id="venue-form" class="max-w-4xl" onSubmit={handleUpdate}>
        <VenueFields
          name={props.data.venue.name}
          kind={kind()}
          onKindChange={setKind}
          nameError={getFieldError('name')}
        />
      </form>
    </>
  );
};
