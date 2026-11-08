import { createSignal, For, Show, type Setter } from 'solid-js';
import type { Media, MediaTypeValue } from '../../generated';
import { client } from '../../utils/client';
import { MEDIA_TYPE_NAMES, toOptions } from '../../utils/enum-names';
import { isEnterKey } from '../../utils/keyboard';
import { buildMediaDetailUrl, mergeMedia, toErrorMessage } from './media-utils';

interface Props {
  selectedIds: () => Set<string>;
  setAvailableMedia: Setter<Media[]>;
  onAdd: (media: Media) => void;
}

type DisplayFilter = '' | 'true' | 'false';
type PerPage = 25 | 50 | 100;

const mediaTypeOptions = toOptions(MEDIA_TYPE_NAMES);

const perPageOptions: PerPage[] = [25, 50, 100];

// 既存メディアを検索して追加する欄
export const MediaSearchPanel = (props: Props) => {
  const [title, setTitle] = createSignal('');
  const [typeValue, setTypeValue] = createSignal<'' | `${MediaTypeValue}`>('');
  const [isDisplay, setIsDisplay] = createSignal<DisplayFilter>('');
  const [page, setPage] = createSignal(1);
  const [perPage, setPerPage] = createSignal<PerPage>(25);
  const [maxPage, setMaxPage] = createSignal(1);
  const [searching, setSearching] = createSignal(false);
  const [results, setResults] = createSignal<Media[]>([]);
  const [error, setError] = createSignal<string | null>(null);
  const [hasSearched, setHasSearched] = createSignal(false);

  const handleSearch = async (nextPage = 1) => {
    setError(null);
    setSearching(true);
    setHasSearched(true);
    setPage(nextPage);

    const {
      data,
      error: searchError,
      status,
    } = await client.api.media.search.get({
      query: {
        title: title(),
        type: typeValue() || undefined,
        is_display: isDisplay() === '' ? undefined : isDisplay() === 'true',
        page: nextPage,
        per_page: perPage(),
      },
    });

    setSearching(false);

    if (!data) {
      setError(toErrorMessage(searchError, `検索に失敗しました (${status})`));
      return;
    }

    setResults(data.media);
    setMaxPage(data.maxPage);
    props.setAvailableMedia((prev) => mergeMedia(prev, data.media));
  };

  const handleReset = () => {
    setTitle('');
    setTypeValue('');
    setIsDisplay('');
    setPage(1);
    setPerPage(25);
    setMaxPage(1);
    setError(null);
    setHasSearched(false);
    setResults([]);
  };

  return (
    <div class="space-y-4">
      <div>
        <label class="label">既存メディアを検索</label>
        <div class="grid gap-3 md:grid-cols-2">
          <input
            type="text"
            class="input input-bordered w-full"
            value={title()}
            onInput={(e) => setTitle(e.currentTarget.value)}
            onKeyDown={(e) => isEnterKey(e) && void handleSearch()}
            placeholder="タイトルで検索"
          />

          <select
            class="select select-bordered w-full"
            value={typeValue()}
            onChange={(e) => setTypeValue(e.currentTarget.value as '' | `${MediaTypeValue}`)}
          >
            <option value="">すべての種別</option>
            <For each={mediaTypeOptions}>{(option) => <option value={option.value}>{option.label}</option>}</For>
          </select>

          <select
            class="select select-bordered w-full"
            value={isDisplay()}
            onChange={(e) => setIsDisplay(e.currentTarget.value as DisplayFilter)}
          >
            <option value="">すべての表示設定</option>
            <option value="true">表示</option>
            <option value="false">非表示</option>
          </select>

          <select
            class="select select-bordered w-full"
            value={perPage()}
            onChange={(e) => setPerPage(Number(e.currentTarget.value) as PerPage)}
          >
            <For each={perPageOptions}>{(option) => <option value={option}>{option}件表示</option>}</For>
          </select>
        </div>

        <div class="mt-3 flex gap-2">
          <button type="button" class="btn btn-outline" disabled={searching()} onClick={() => void handleSearch()}>
            {searching() ? '検索中...' : '検索'}
          </button>
          <button type="button" class="btn btn-ghost" disabled={searching()} onClick={handleReset}>
            リセット
          </button>
        </div>
        <Show when={error()}>{(message) => <p class="mt-2 text-sm text-error">{message()}</p>}</Show>
      </div>

      <div class="space-y-2">
        <Show
          when={hasSearched() && results().length > 0}
          fallback={
            <p class="text-sm text-base-content/60">
              {hasSearched() ? '条件に一致するメディアはありません。' : '検索すると候補が表示されます。'}
            </p>
          }
        >
          <div class="mb-2 flex items-center justify-between text-xs text-base-content/60">
            <p>
              {page()} / {maxPage()} ページ
            </p>
            <Show when={maxPage() > 1}>
              <div class="flex gap-2">
                <button
                  type="button"
                  class="btn btn-ghost btn-xs"
                  disabled={searching() || page() <= 1}
                  onClick={() => void handleSearch(page() - 1)}
                >
                  前へ
                </button>
                <button
                  type="button"
                  class="btn btn-ghost btn-xs"
                  disabled={searching() || page() >= maxPage()}
                  onClick={() => void handleSearch(page() + 1)}
                >
                  次へ
                </button>
              </div>
            </Show>
          </div>
          <For each={results()}>
            {(item) => (
              <div class="rounded-box border border-base-300 bg-base-100 p-3">
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <div class="flex items-center gap-2">
                      <p class="truncate font-medium">{item.title}</p>
                      <Show when={props.selectedIds().has(item.mediaId)}>
                        <span class="badge badge-sm badge-primary badge-soft">選択中</span>
                      </Show>
                    </div>
                    <p class="text-xs text-base-content/60">{MEDIA_TYPE_NAMES[item.type]}</p>
                    <a href={item.url} target="_blank" rel="noreferrer" class="link link-hover break-all text-xs">
                      {item.url}
                    </a>
                  </div>
                  <div class="flex flex-col items-end gap-2">
                    <a
                      href={buildMediaDetailUrl(item.mediaId)}
                      target="_blank"
                      rel="noreferrer"
                      class="btn btn-ghost btn-xs"
                    >
                      詳細
                    </a>
                    <button
                      type="button"
                      class="btn btn-xs btn-primary"
                      disabled={props.selectedIds().has(item.mediaId)}
                      onClick={() => props.onAdd(item)}
                    >
                      {props.selectedIds().has(item.mediaId) ? '追加済み' : '追加'}
                    </button>
                  </div>
                </div>
              </div>
            )}
          </For>
        </Show>
      </div>
    </div>
  );
};
