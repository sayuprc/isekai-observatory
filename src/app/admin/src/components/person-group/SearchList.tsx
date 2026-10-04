import { createSignal, For, Match, Show, Switch } from 'solid-js';
import { client } from '../../utils/client';
import {
  PER_PAGE_OPTIONS,
  createSearchResource,
  createSearchState,
  parsePage,
  pickParam,
} from '../../utils/search-list';
import type { PerPageOption as PerPage } from '../../utils/search-list';
import { CountCell } from '../CountCell';
import { ListWithPreview, RecordPreview, selectedRowClass, type RecordPreviewData } from '../ListPreview';
import { ListState } from '../ListState';
import { Pagination } from '../Pagination';

type SearchParams = {
  name: string;
  page: number;
  perPage: PerPage;
};

const DEFAULT_PARAMS: SearchParams = {
  name: '',
  page: 1,
  perPage: 25,
};

const parseParams = (query: URLSearchParams): SearchParams => ({
  name: query.get('name') ?? '',
  page: parsePage(query.get('page')),
  perPage: pickParam(query.get('per_page'), PER_PAGE_OPTIONS, DEFAULT_PARAMS.perPage),
});

const toQuery = (params: SearchParams) => ({
  name: params.name,
  page: params.page,
  per_page: params.perPage,
});

export const SearchList = () => {
  const { params, input, updateInput, handleSearch, handleReset, handlePageChange } = createSearchState({
    defaults: DEFAULT_PARAMS,
    parse: parseParams,
    toQuery,
  });

  const { data, refetch, fetchError } = createSearchResource(params, (current) =>
    client.api['person-groups'].search.get({
      query: {
        name: current.name,
        page: current.page,
        per_page: current.perPage,
      },
    }),
  );

  // 右側のプレビューに出す行。取得結果の中から引くので、ページを移ると外れる
  const [selectedId, setSelectedId] = createSignal<string | null>(null);
  const detailUrl = (id: string) => `/person-groups/${id}?back=${encodeURIComponent(window.location.search)}`;
  const selectedRecord = (): RecordPreviewData | undefined => {
    const personGroup = data()?.personGroups.find((candidate) => candidate.personGroupId === selectedId());
    if (!personGroup) return undefined;
    return {
      title: personGroup.name,
      rows: [
        { label: 'メンバー', value: personGroup.members.length },
        { label: '共演した楽曲披露', value: personGroup.performanceCount },
      ],
      actions: [{ label: '開く', href: detailUrl(personGroup.personGroupId), primary: true }],
    };
  };

  return (
    <>
      <form onSubmit={handleSearch} class="mb-4 flex flex-wrap items-end gap-4">
        <fieldset class="fieldset">
          <label class="fieldset-label" for="name">
            グループ名
          </label>
          <input
            type="text"
            id="name"
            name="name"
            value={input().name}
            onInput={(e) => updateInput({ name: e.currentTarget.value })}
            class="input input-bordered input-sm"
            placeholder="グループ名で検索"
          />
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="perPage">
            表示件数
          </label>
          <select
            id="perPage"
            name="perPage"
            class="select select-bordered select-sm"
            onChange={(e) => updateInput({ perPage: Number(e.currentTarget.value) as PerPage })}
          >
            <For each={PER_PAGE_OPTIONS}>
              {(n) => (
                <option value={n} selected={input().perPage === n}>
                  {n}件
                </option>
              )}
            </For>
          </select>
        </fieldset>
        <button type="submit" class="btn btn-primary btn-sm mb-1">
          検索
        </button>
        <button type="button" class="btn btn-ghost btn-sm mb-1" onClick={handleReset}>
          リセット
        </button>
      </form>
      <div class="mb-4 flex justify-end">
        <a href="/person-groups/create" class="btn btn-primary btn-sm">
          新規作成
        </a>
      </div>
      <ListWithPreview
        table={
          <table class="table table-sm">
            <thead>
              <tr>
                <th>グループ名</th>
                <th>メンバー</th>
                <th class="text-right">共演</th>
              </tr>
            </thead>
            <tbody>
              <Switch>
                <Match when={data.loading}>
                  <ListState state="loading" colSpan={3} />
                </Match>
                <Match when={fetchError()}>
                  {(message) => <ListState state="error" colSpan={3} message={message()} onRetry={() => refetch()} />}
                </Match>
                <Match when={data() && data()!.personGroups.length === 0}>
                  <ListState state="empty" colSpan={3} />
                </Match>
                <Match when={data()}>
                  {(result) => (
                    <For each={result().personGroups}>
                      {(personGroup) => (
                        <tr
                          class="cursor-pointer hover:bg-base-200"
                          classList={{ [selectedRowClass]: personGroup.personGroupId === selectedId() }}
                          onClick={() => setSelectedId(personGroup.personGroupId)}
                          onFocusIn={() => setSelectedId(personGroup.personGroupId)}
                        >
                          <td>
                            <a href={detailUrl(personGroup.personGroupId)} class="link link-hover font-medium">
                              {personGroup.name}
                            </a>
                          </td>
                          <td class="max-w-md truncate text-base-content/70">
                            {personGroup.members.map((member) => member.name).join(' / ')}
                          </td>
                          <CountCell count={personGroup.performanceCount} />
                        </tr>
                      )}
                    </For>
                  )}
                </Match>
              </Switch>
            </tbody>
          </table>
        }
        preview={<RecordPreview label="選択中の人物グループ" record={selectedRecord()} />}
      />

      <Show when={!data.loading && !fetchError() && (data()?.maxPage ?? 0) > 1}>
        <Pagination page={params().page} maxPage={data()!.maxPage} onChange={handlePageChange} />
      </Show>
    </>
  );
};
