import { createResource, Match, Show, Switch } from 'solid-js';
import type { Tag } from '../../generated';
import { redirectToLogin } from '../../utils/auth-redirect';
import { client } from '../../utils/client';
import { createFormDirtyTracker, discardChanges } from '../../utils/dirty';
import { createFormErrors } from '../../utils/form-error';
import { preventImplicitSubmit } from '../../utils/keyboard';
import { getListUrl } from '../../utils/list-url';
import { createTabState } from '../../utils/tab';
import { createSubmitting } from '../../utils/use-submitting';
import { ActionMenu } from '../ActionMenu';
import { TargetHistory } from '../audit-log/TargetHistory';
import { EntityHeader } from '../EntityHeader';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import { FormRow } from '../FormRow';
import { TabList, TabPanel, type TabItem } from '../Tabs';
import { TaggedEvents, TaggedSongs } from './TaggedRecords';

const TAG_TABS = ['overview', 'songs', 'events', 'history'] as const;
type TagTab = (typeof TAG_TABS)[number];

const TAB_ID_PREFIX = 'tag';

const TAB_ITEMS: TabItem<TagTab>[] = [
  { key: 'overview', label: '概要' },
  { key: 'songs', label: '楽曲' },
  { key: 'events', label: 'イベント' },
  { key: 'history', label: '履歴' },
];

interface DetailViewProps {
  tagId: string;
}

interface EditableFormProps {
  data: { tag: Tag };
}

interface FetchOkState {
  status: 'ok';
  data: { tag: Tag };
}

interface FetchErrorState {
  status: 'error';
}

type FetchState = FetchOkState | FetchErrorState;

export const DetailView = (props: DetailViewProps) => {
  const listUrl = getListUrl('/tags');

  const [resource, { refetch }] = createResource(async (): Promise<FetchState> => {
    const { data, status } = await client.api['tags']({ tagId: props.tagId }).get();

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
  const listUrl = getListUrl('/tags');
  const tagId = props.data.tag.tagId;

  const { tab, setTab } = createTabState(TAG_TABS, 'overview');
  const { formError, setFormError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();
  const { isDirty, allowLeave, bindForm } = createFormDirtyTracker();

  const handleUpdate = withSubmitting(async (e: SubmitEvent) => {
    e.preventDefault();
    clearErrors();

    const formData = new FormData(e.currentTarget as HTMLFormElement);

    if (!tagId) {
      setFormError('更新対象のタグIDを取得できませんでした');
      return;
    }

    const { data, error, status } = await client.api['tags']({ tagId }).put({
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

    if (!tagId) {
      setFormError('削除対象のタグIDを取得できませんでした');
      return;
    }

    const { error, status } = await client.api['tags']({ tagId }).delete();

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
        breadcrumb={{ href: listUrl, label: 'タグ' }}
        title={props.data.tag.name}
        formId="tag-form"
        isDirty={isDirty()}
        isSubmitting={isSubmitting()}
        submitLabel="保存"
        submittingLabel="保存中..."
        onDiscard={() => discardChanges(allowLeave)}
        tabs={
          <TabList label="タグの項目" idPrefix={TAB_ID_PREFIX} items={TAB_ITEMS} current={tab()} onChange={setTab} />
        }
        menu={
          <ActionMenu
            label="その他の操作"
            items={[{ label: 'このタグを削除する', danger: true, disabled: isSubmitting(), onSelect: handleDelete }]}
          />
        }
      />
      <FormError message={formError()} onClose={clearErrors} />
      <TabPanel idPrefix={TAB_ID_PREFIX} tabKey="overview" current={tab()} class="max-w-4xl">
        <form ref={bindForm} id="tag-form" onKeyDown={preventImplicitSubmit} onSubmit={handleUpdate}>
          <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border px-6 py-3">
            <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
            <FormRow label="タグ名" for="name">
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
      </TabPanel>
      <TabPanel idPrefix={TAB_ID_PREFIX} tabKey="songs" current={tab()} class="max-w-4xl">
        <TaggedSongs tagId={tagId} active={tab() === 'songs'} />
      </TabPanel>
      <TabPanel idPrefix={TAB_ID_PREFIX} tabKey="events" current={tab()} class="max-w-4xl">
        <TaggedEvents tagId={tagId} active={tab() === 'events'} />
      </TabPanel>
      <TabPanel idPrefix={TAB_ID_PREFIX} tabKey="history" current={tab()} class="max-w-4xl">
        <TargetHistory targetType="Tag" targetId={tagId} active={tab() === 'history'} />
      </TabPanel>
    </>
  );
};
