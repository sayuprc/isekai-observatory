import { children, createSignal, For, type JSX, Show } from 'solid-js';
import type { SongTag } from '../../generated';
import { SONG_TYPE_NAMES } from '../../generated/enum-names.gen';
import { toOptions } from '../../utils/enum-options';
import { FormColumns } from '../FormColumns';
import { FormRow } from '../FormRow';
import { MediaSection } from '../media/MediaSection';
import { SearchableSelect } from '../SearchableSelect';
import { SegmentedControl } from '../SegmentedControl';
import { TabList, TabPanel, type TabItem } from '../Tabs';
import { PersonSearchSection } from './PersonSearchSection';
import type { SongFormState } from './song-form';

const SONG_TYPE_OPTIONS = toOptions(SONG_TYPE_NAMES);

export const SONG_TABS = ['overview', 'persons', 'media', 'history'] as const;
export type SongTab = (typeof SONG_TABS)[number];

const TAB_ID_PREFIX = 'song';

const DISPLAY_OPTIONS = [
  { value: 'true', label: '表示' },
  { value: 'false', label: '非表示' },
];

interface SongTabListProps {
  form: SongFormState;
  current: SongTab;
  onChange: (tab: SongTab) => void;
  // 作成画面には履歴がない
  withHistory: boolean;
}

export const SongTabList = (props: SongTabListProps) => {
  const items = (): TabItem<SongTab>[] => [
    { key: 'overview', label: '概要' },
    { key: 'persons', label: 'クレジット', count: String(props.form.personCount()) },
    { key: 'media', label: 'メディア', count: String(props.form.mediaEntries().length) },
    ...(props.withHistory ? [{ key: 'history' as const, label: '履歴' }] : []),
  ];

  return (
    <TabList
      label="楽曲の項目"
      idPrefix={TAB_ID_PREFIX}
      items={items()}
      current={props.current}
      onChange={props.onChange}
    />
  );
};

interface SongFormFieldsProps {
  form: SongFormState;
  tab: SongTab;
  availableTags: SongTag[];
  getFieldError: (field: string) => string | undefined;
  // 編集画面だけ表示順を出す
  withOrderNo: boolean;
  // 履歴タブの中身。作成画面では渡さない
  history?: JSX.Element;
}

export const SongFormFields = (props: SongFormFieldsProps) => {
  // JSX の props を Show の条件と中身で 2 回参照すると要素が 2 つ作られるので、1 回だけ評価して使い回す
  const history = children(() => props.history);

  return (
    <>
      <TabPanel idPrefix={TAB_ID_PREFIX} tabKey="overview" current={props.tab}>
        <FormColumns
          main={<SongBasicInfo {...props} />}
          side={<SongTagSection form={props.form} availableTags={props.availableTags} />}
        />
      </TabPanel>
      <TabPanel idPrefix={TAB_ID_PREFIX} tabKey="persons" current={props.tab} class="max-w-4xl">
        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
          <legend class="px-2 text-sm font-semibold text-base-content/70">クレジット</legend>
          <div class="space-y-4">
            <PersonSearchSection
              selections={props.form.personSelections}
              setSelections={props.form.setPersonSelections}
            />
          </div>
        </fieldset>
      </TabPanel>
      <TabPanel idPrefix={TAB_ID_PREFIX} tabKey="media" current={props.tab} class="max-w-4xl">
        <MediaSection
          entries={props.form.mediaEntries}
          setEntries={props.form.setMediaEntries}
          availableMedia={props.form.availableMedia}
          setAvailableMedia={props.form.setAvailableMedia}
        />
      </TabPanel>
      <Show when={history()}>
        <TabPanel idPrefix={TAB_ID_PREFIX} tabKey="history" current={props.tab} class="max-w-4xl">
          {history()}
        </TabPanel>
      </Show>
    </>
  );
};

const SongBasicInfo = (props: SongFormFieldsProps) => (
  <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border px-6 py-3">
    <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
    <FormRow label="楽曲名" for="title">
      <input
        id="title"
        type="text"
        class="input w-full"
        required
        value={props.form.title()}
        onInput={(e) => props.form.setTitle(e.currentTarget.value)}
        classList={{ 'input-error': !!props.getFieldError('title') }}
      />
      <Show when={props.getFieldError('title')}>{(message) => <p class="text-xs text-error">{message()}</p>}</Show>
    </FormRow>
    <FormRow label="楽曲種別">
      <SegmentedControl
        label="楽曲種別"
        options={SONG_TYPE_OPTIONS}
        value={props.form.typeValue()}
        onChange={props.form.setTypeValue}
      />
      <Show when={props.getFieldError('type')}>{(message) => <p class="text-xs text-error">{message()}</p>}</Show>
    </FormRow>
    <FormRow label="説明" for="description">
      <input
        id="description"
        type="text"
        class="input w-full"
        value={props.form.description()}
        onInput={(e) => props.form.setDescription(e.currentTarget.value)}
        classList={{ 'input-error': !!props.getFieldError('description') }}
      />
      <Show when={props.getFieldError('description')}>
        {(message) => <p class="text-xs text-error">{message()}</p>}
      </Show>
    </FormRow>
    <FormRow label="歌詞リンク" for="lyricsLink">
      <input
        id="lyricsLink"
        type="url"
        class="input w-full"
        placeholder="https://example.com/lyrics"
        value={props.form.lyricsLink()}
        onInput={(e) => props.form.setLyricsLink(e.currentTarget.value)}
        classList={{ 'input-error': !!props.getFieldError('lyricsLink') }}
      />
      <Show when={props.getFieldError('lyricsLink')}>{(message) => <p class="text-xs text-error">{message()}</p>}</Show>
    </FormRow>
    <Show when={props.withOrderNo}>
      <FormRow label="表示順" for="orderNo">
        <input
          id="orderNo"
          type="number"
          class="input w-40"
          required
          min="1"
          value={props.form.orderNo()}
          onInput={(e) => props.form.setOrderNo(Number(e.currentTarget.value))}
        />
      </FormRow>
    </Show>
    <FormRow label="公開">
      <SegmentedControl
        label="公開"
        options={DISPLAY_OPTIONS}
        value={String(props.form.isDisplay())}
        onChange={(value) => props.form.setIsDisplay(value === 'true')}
      />
    </FormRow>
  </fieldset>
);

const SongTagSection = (props: { form: SongFormState; availableTags: SongTag[] }) => {
  const [pickerValue, setPickerValue] = createSignal('');

  const addTag = (songTagId: string) => {
    if (songTagId === '') return;
    props.form.setTags((prev) =>
      prev.some((entry) => entry.songTagId === songTagId) ? prev : [...prev, { songTagId }],
    );
    setPickerValue('');
  };

  const removeTag = (index: number) => {
    props.form.setTags((prev) => prev.filter((_, i) => i !== index));
  };

  const tagOptions = () => {
    const selectedIds = new Set(props.form.tags().map((entry) => entry.songTagId));
    return props.availableTags
      .filter((tag) => !selectedIds.has(tag.songTagId))
      .map((tag) => ({ value: tag.songTagId, label: tag.name }));
  };

  const selectedTags = () =>
    props.form
      .tags()
      .map((entry) => props.availableTags.find((tag) => tag.songTagId === entry.songTagId))
      .filter((tag): tag is SongTag => tag !== undefined);

  return (
    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
      <legend class="px-2 text-sm font-semibold text-base-content/70">タグ</legend>
      <SearchableSelect
        options={tagOptions()}
        value={pickerValue()}
        onChange={addTag}
        placeholder="楽曲タグを検索して追加..."
      />
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
