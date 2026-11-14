import { createResource, createSignal, For, Show, type Accessor, type Setter } from 'solid-js';
import type { RequestTag, Tag } from '../../generated';
import { client } from '../../utils/client';
import { SearchableSelect } from '../SearchableSelect';

interface TagPickerProps {
  tags: Accessor<RequestTag[]>;
  setTags: Setter<RequestTag[]>;
  // 欄の見出し。タグを付ける欄以外で使うときに変える
  label?: string;
}

// 記録に付けるタグを選ぶ欄。楽曲・イベントなど、タグを付けられる記録のフォームで共通に使う
export const TagPicker = (props: TagPickerProps) => {
  const [pickerValue, setPickerValue] = createSignal('');
  // 選択肢と、付いているタグの名前の表示に使う
  const [fetched] = createResource(async (): Promise<Tag[] | null> => {
    const { data } = await client.api.tags.get();
    // 権限不足などで取れなかったときに、候補が空なだけに見えないよう null で区別する
    return data?.tags ?? null;
  });
  const availableTags = () => fetched() ?? [];
  const failed = () => !fetched.loading && fetched() === null;

  const addTag = (tagId: string) => {
    if (tagId === '') return;
    props.setTags((prev) => (prev.some((entry) => entry.tagId === tagId) ? prev : [...prev, { tagId }]));
    setPickerValue('');
  };

  const removeTag = (index: number) => {
    props.setTags((prev) => prev.filter((_, i) => i !== index));
  };

  const tagOptions = () => {
    const selectedIds = new Set(props.tags().map((entry) => entry.tagId));
    return availableTags()
      .filter((tag) => !selectedIds.has(tag.tagId))
      .map((tag) => ({ value: tag.tagId, label: tag.name }));
  };

  const selectedTags = () =>
    props
      .tags()
      .map((entry) => availableTags().find((tag) => tag.tagId === entry.tagId))
      .filter((tag): tag is Tag => tag !== undefined);

  return (
    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
      <legend class="px-2 text-sm font-semibold text-base-content/70">{props.label ?? 'タグ'}</legend>
      <Show when={!failed()} fallback={<p class="text-sm text-error">タグを取得できませんでした。</p>}>
        <SearchableSelect
          options={tagOptions()}
          value={pickerValue()}
          onChange={addTag}
          placeholder="タグを検索して追加..."
        />
      </Show>
      <Show
        when={selectedTags().length > 0}
        fallback={<p class="mt-3 text-sm text-base-content/60">タグはまだ追加されていません。</p>}
      >
        <div class="mt-3 flex flex-wrap gap-2">
          <For each={selectedTags()}>
            {(tag, index) => (
              <button
                type="button"
                class="badge badge-lg cursor-pointer gap-2 border border-base-300 bg-base-100 px-3 py-4"
                aria-label={`${tag.name}を外す`}
                onClick={() => removeTag(index())}
              >
                <span>{tag.name}</span>
                <span class="text-error" aria-hidden="true">
                  ×
                </span>
              </button>
            )}
          </For>
        </div>
      </Show>
    </fieldset>
  );
};
