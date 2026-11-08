import { createSignal, For, Show, type Setter } from 'solid-js';
import type { Media, MediaTypeValue } from '../../generated';
import { client } from '../../utils/client';
import { MEDIA_TYPE_NAMES, toOptions } from '../../utils/enum-names';
import { SegmentedControl } from '../SegmentedControl';
import { buildMediaDetailUrl, mergeMedia, toErrorMessage } from './media-utils';

interface Props {
  availableMedia: () => Media[];
  selectedIds: () => Set<string>;
  setAvailableMedia: Setter<Media[]>;
  onAdd: (media: Media) => void;
}

const mediaTypeOptions = toOptions(MEDIA_TYPE_NAMES);

const DISPLAY_OPTIONS = [
  { value: 'true', label: '表示' },
  { value: 'false', label: '非表示' },
];

const normalize = (value: string) => value.trim().toLocaleLowerCase('ja');

// その場でメディアを作って追加する欄
// 普段は閉じておき、既存メディアで見つからなかったときだけ開く
export const MediaCreatePanel = (props: Props) => {
  const [title, setTitle] = createSignal('');
  const [url, setUrl] = createSignal('');
  const [publishedAt, setPublishedAt] = createSignal('');
  const [typeValue, setTypeValue] = createSignal<MediaTypeValue>(1);
  const [isDisplay, setIsDisplay] = createSignal(true);
  const [creating, setCreating] = createSignal(false);
  const [error, setError] = createSignal<string | null>(null);

  const duplicateCandidates = () => {
    const inputTitle = normalize(title());
    const inputUrl = normalize(url());

    if (inputTitle === '' && inputUrl === '') {
      return [];
    }

    return props
      .availableMedia()
      .map((item) => {
        const itemTitle = normalize(item.title);
        const itemUrl = normalize(item.url);
        const reasons: string[] = [];

        if (inputUrl !== '' && itemUrl === inputUrl) {
          reasons.push('同じURL');
        }

        if (inputTitle.length >= 3 && itemTitle !== '') {
          if (itemTitle === inputTitle) {
            reasons.push('同じタイトル');
          } else if (itemTitle.includes(inputTitle) || inputTitle.includes(itemTitle)) {
            reasons.push('類似タイトル');
          }
        }

        if (reasons.length === 0) {
          return null;
        }

        return { item, reasons };
      })
      .filter((entry): entry is { item: Media; reasons: string[] } => entry !== null)
      .slice(0, 5);
  };

  const hasExactUrlDuplicate = () => duplicateCandidates().some((entry) => entry.reasons.includes('同じURL'));

  const handleCreate = async () => {
    const trimmedTitle = title().trim();
    const trimmedUrl = url().trim();

    setError(null);

    if (trimmedTitle === '') {
      setError('タイトルを入力してください');
      return;
    }

    if (trimmedUrl === '') {
      setError('URLを入力してください');
      return;
    }

    if (publishedAt() === '') {
      setError('公開日を入力してください');
      return;
    }

    if (hasExactUrlDuplicate()) {
      setError('同じURLの既存メディアがあります。既存メディアの追加を検討してください');
      return;
    }

    setCreating(true);

    const {
      data,
      error: createError,
      status,
    } = await client.api.media.post({
      title: trimmedTitle,
      url: trimmedUrl,
      publishedAt: publishedAt(),
      type: typeValue(),
      isDisplay: isDisplay(),
      // その場で作るメディアにはタグを付けず、必要ならメディアの画面で付ける
      tags: [],
    });

    setCreating(false);

    if (!data) {
      setError(toErrorMessage(createError, `作成に失敗しました (${status})`));
      return;
    }

    props.setAvailableMedia((prev) => mergeMedia(prev, [data.media]));
    props.onAdd(data.media);
    setTitle('');
    setUrl('');
    setPublishedAt('');
    setTypeValue(1);
    setIsDisplay(true);
  };

  return (
    <details class="rounded-box border border-base-300 bg-base-100">
      <summary class="cursor-pointer select-none px-4 py-3 text-sm font-medium">
        見つからないときは新規メディアを作成
      </summary>
      <div class="space-y-3 border-t border-base-300 p-4">
        <div class="grid gap-3 md:grid-cols-2">
          <input
            type="text"
            class="input input-bordered w-full"
            value={title()}
            onInput={(e) => setTitle(e.currentTarget.value)}
            placeholder="タイトル"
          />
          <input
            type="url"
            class="input input-bordered w-full"
            value={url()}
            onInput={(e) => setUrl(e.currentTarget.value)}
            placeholder="https://example.com/media"
          />
          <input
            type="datetime-local"
            class="input input-bordered w-full"
            value={publishedAt()}
            step="1"
            onInput={(e) => setPublishedAt(e.currentTarget.value)}
          />
          <select
            class="select select-bordered w-full"
            value={typeValue()}
            onChange={(e) => setTypeValue(Number(e.currentTarget.value) as MediaTypeValue)}
          >
            <For each={mediaTypeOptions}>{(option) => <option value={option.value}>{option.label}</option>}</For>
          </select>
        </div>

        <SegmentedControl
          label="公開"
          options={DISPLAY_OPTIONS}
          value={String(isDisplay())}
          onChange={(value) => setIsDisplay(value === 'true')}
        />

        <Show when={duplicateCandidates().length > 0}>
          <div class="rounded-box border border-warning/40 bg-warning/10 p-3">
            <p class="text-sm font-medium text-warning-content">重複候補があります</p>
            <div class="mt-2 space-y-2">
              <For each={duplicateCandidates()}>
                {(candidate) => (
                  <div class="rounded-box bg-base-100 p-3">
                    <div class="flex items-start justify-between gap-3">
                      <div class="min-w-0">
                        <div class="flex items-center gap-2">
                          <p class="truncate text-sm font-medium">{candidate.item.title}</p>
                          <For each={candidate.reasons}>
                            {(reason) => <span class="badge badge-warning badge-sm badge-outline">{reason}</span>}
                          </For>
                        </div>
                        <p class="text-xs text-base-content/60">{MEDIA_TYPE_NAMES[candidate.item.type]}</p>
                        <a
                          href={candidate.item.url}
                          target="_blank"
                          rel="noreferrer"
                          class="link link-hover break-all text-xs"
                        >
                          {candidate.item.url}
                        </a>
                      </div>
                      <div class="flex flex-col items-end gap-2">
                        <a
                          href={buildMediaDetailUrl(candidate.item.mediaId)}
                          target="_blank"
                          rel="noreferrer"
                          class="btn btn-ghost btn-xs"
                        >
                          詳細
                        </a>
                        <button
                          type="button"
                          class="btn btn-primary btn-xs"
                          disabled={props.selectedIds().has(candidate.item.mediaId)}
                          onClick={() => props.onAdd(candidate.item)}
                        >
                          {props.selectedIds().has(candidate.item.mediaId) ? '追加済み' : '既存を追加'}
                        </button>
                      </div>
                    </div>
                  </div>
                )}
              </For>
            </div>
          </div>
        </Show>

        <Show when={error()}>{(message) => <p class="text-sm text-error">{message()}</p>}</Show>

        <div class="flex justify-end">
          <button type="button" class="btn btn-primary" disabled={creating()} onClick={() => void handleCreate()}>
            {creating() ? '作成中...' : '作成して追加'}
          </button>
        </div>
      </div>
    </details>
  );
};
