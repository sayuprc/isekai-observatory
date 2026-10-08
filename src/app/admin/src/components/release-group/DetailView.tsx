import { For, Match, Show, Switch, createResource } from 'solid-js';
import type { ReleaseGroupGetResponse, ReleaseGroupReferencedRelease } from '../../generated';
import { RELEASE_FORMAT_NAMES } from '../../generated/enum-names.gen';
import { redirectToLogin } from '../../utils/auth-redirect';
import { client } from '../../utils/client';
import { createDirtyTracker, discardChanges } from '../../utils/dirty';
import { createFormErrors } from '../../utils/form-error';
import { getListUrl } from '../../utils/list-url';
import { createSubmitting } from '../../utils/use-submitting';
import { ActionMenu } from '../ActionMenu';
import { EntityHeader } from '../EntityHeader';
import { setFlash } from '../Flash';
import { FormColumns } from '../FormColumns';
import { FormError } from '../FormError';
import { createReleaseGroupForm, ReleaseGroupFields } from './ReleaseGroupFields';

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
    <Show when={props.release.formats.length > 0} fallback={<span class="text-sm text-base-content/60">—</span>}>
      <For each={props.release.formats}>
        {(formatValue) => <span class="badge badge-outline badge-sm">{RELEASE_FORMAT_NAMES[formatValue]}</span>}
      </For>
    </Show>
  </div>
);

const ReleaseDisplayBadge = (props: { release: ReleaseGroupReferencedRelease }) => (
  <span class={`badge badge-sm ${props.release.isDisplay ? 'badge-success badge-soft' : 'badge-ghost'}`}>
    {props.release.isDisplay ? '表示' : '非表示'}
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

export const DetailView = (props: DetailViewProps) => {
  const listUrl = getListUrl('/release-groups');

  const [resource, { refetch }] = createResource(async (): Promise<FetchState> => {
    const { data, status } = await client.api['release-groups']({ releaseGroupId: props.releaseGroupId }).get();

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
      <Match when={loadedData()}>{(data) => <ReleaseGroupForm data={data()} />}</Match>
    </Switch>
  );
};

const ReleaseGroupForm = (props: ReleaseGroupFormProps) => {
  const listUrl = getListUrl('/release-groups');
  const releaseGroupId = props.data.releaseGroup.releaseGroupId;

  const form = createReleaseGroupForm(props.data.releaseGroup);
  const { isDirty, allowLeave } = createDirtyTracker(form.toRequestBody);

  const { formError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting: isUpdating, withSubmitting: withUpdating } = createSubmitting();
  const { isSubmitting: isDeleting, withSubmitting: withDeleting } = createSubmitting();

  const handleSubmit = withUpdating(async (e: SubmitEvent) => {
    e.preventDefault();
    clearErrors();

    const { data, error, status } = await client.api['release-groups']({ releaseGroupId }).put(form.toRequestBody());

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

  const handleDelete = withDeleting(async () => {
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
    allowLeave();
    window.location.href = listUrl;
  });

  return (
    <>
      <EntityHeader
        breadcrumb={{ href: listUrl, label: 'リリースグループ' }}
        title={form.title() || '(タイトル未入力)'}
        formId="release-group-form"
        isDirty={isDirty()}
        isSubmitting={isUpdating() || isDeleting()}
        submitLabel="保存"
        submittingLabel="保存中..."
        onDiscard={() => discardChanges(allowLeave)}
        menu={
          <ActionMenu
            label="その他の操作"
            items={[
              {
                label: 'このリリースグループを削除する',
                danger: true,
                disabled: isUpdating() || isDeleting(),
                onSelect: handleDelete,
              },
            ]}
          />
        }
      />
      <FormError message={formError()} onClose={clearErrors} />
      <FormColumns
        main={
          <form id="release-group-form" onSubmit={handleSubmit}>
            <ReleaseGroupFields form={form} getFieldError={getFieldError} />
          </form>
        }
        side={
          <fieldset class="rounded-box border border-base-300 bg-base-200 p-6">
            <legend class="px-2 text-sm font-semibold text-base-content/70">リリース(版)</legend>
            <p class="mb-2 text-sm text-base-content/60">リリースが登録されているグループは削除できません</p>
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
                  {(release) => (
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
                      {(release) => (
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
        }
      />
    </>
  );
};
