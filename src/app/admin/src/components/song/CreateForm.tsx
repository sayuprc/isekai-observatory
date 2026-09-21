import { createResource, createSignal, For, Match, Show, Switch } from 'solid-js';
import type {
  Media,
  RequestSongPerson,
  SongTag,
  SongType,
  SongTypeValue,
} from '../../generated';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import { SearchableSelect } from '../SearchableSelect';
import { buildSongMediaRequest, MediaSection, type MediaEntry } from './MediaSection';
import { toRequestSongPersons, type PersonSelections } from './person-selection';
import { PersonSearchSection } from './PersonSearchSection';

type SongTagEntry = {
  songTagId: string;
};

type CreateFormData = { types: SongType[]; tags: SongTag[]; media: Media[] };

interface CreateFormProps {
  data: CreateFormData;
}

interface FetchOkState {
  status: 'ok';
  data: CreateFormData;
}

interface FetchErrorState {
  status: 'error';
}

type FetchState = FetchOkState | FetchErrorState;

export const CreateView = () => {
  const [resource, { refetch }] = createResource(async (): Promise<FetchState> => {
    const { data, status } = await client.api.songs['create-form'].get();

    if (status === 401) {
      window.location.href = '/auth/login';
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
      <Match when={loadedData()}>{data => <CreateForm data={data()} />}</Match>
    </Switch>
  );
};

export const CreateForm = (props: CreateFormProps) => {
  const [types] = createSignal<SongType[]>(props.data.types);
  const [typeValue, setTypeValue] = createSignal<SongTypeValue | ''>('');
  const [availableTags] = createSignal<SongTag[]>(props.data.tags);
  const [availableMedia, setAvailableMedia] = createSignal<Media[]>(props.data.media);

  const [personSelections, setPersonSelections] = createSignal<PersonSelections>({ 1: [], 2: [], 3: [] });
  const [tags, setTags] = createSignal<SongTagEntry[]>([]);
  const [mediaEntries, setMediaEntries] = createSignal<MediaEntry[]>([]);
  const [tagPickerValue, setTagPickerValue] = createSignal('');

  const { formError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  const normalizeOptionalString = (value: FormDataEntryValue | null): string | null => {
    const normalized = value?.toString().trim() ?? '';

    return normalized === '' ? null : normalized;
  };

  const removeTagEntry = (index: number) => {
    setTags(prev => prev.filter((_, i) => i !== index));
  };

  const addTagEntry = (songTagId: string) => {
    if (songTagId === '') {
      return;
    }

    setTags(prev => (prev.some(entry => entry.songTagId === songTagId) ? prev : [...prev, { songTagId }]));
    setTagPickerValue('');
  };

  const buildPersons = (): RequestSongPerson[] => [
    ...toRequestSongPersons(personSelections()[1], 1),
    ...toRequestSongPersons(personSelections()[2], 2),
    ...toRequestSongPersons(personSelections()[3], 3),
  ];

  const handleSubmit = withSubmitting(async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);

    const { data, error, status } = await client.api.songs.post({
      title: formData.get('title')?.toString() ?? '',
      description: formData.get('description')?.toString() ?? '',
      lyricsLink: normalizeOptionalString(formData.get('lyricsLink')),
      typeValue: typeValue() as SongTypeValue,
      isDisplay: formData.get('isDisplay') === 'true',
      persons: buildPersons(),
      tags: tags(),
      media: buildSongMediaRequest(mediaEntries()),
    });

    if (data) {
      setFlash('作成しました');
      window.location.href = '/songs';
      return;
    }

    handleError(status, error);
  });

  const tagOptions = () => {
    const selectedTagIds = new Set(tags().map(entry => entry.songTagId));

    return availableTags()
      .filter(tag => !selectedTagIds.has(tag.songTagId))
      .map(tag => ({ value: tag.songTagId, label: tag.name }));
  };

  const selectedTags = () =>
    tags()
      .map(entry => availableTags().find(tag => tag.songTagId === entry.songTagId))
      .filter((tag): tag is SongTag => tag !== undefined);

  const TagList = () => (
    <div class="mt-4">
      <div class="flex flex-col gap-2">
        <span class="label">楽曲タグ</span>
        <SearchableSelect
          options={tagOptions()}
          value={tagPickerValue()}
          onChange={value => addTagEntry(value)}
          placeholder="楽曲タグを検索して追加..."
        />
        <p class="text-xs text-base-content/60">選択したタグは下に追加されます。</p>
      </div>
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
                onclick={() => removeTagEntry(index())}
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
    </div>
  );

  return (
    <form onsubmit={handleSubmit}>
      <a href="/songs" class="btn btn-ghost btn-sm mb-4">
        ← 一覧に戻る
      </a>
      <FormError message={formError()} onClose={clearErrors} />
      <div class="max-w-4xl space-y-6">
        <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
          <fieldset class="fieldset bg-base-200 border-base-300 rounded-box h-full border p-6">
            <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
            <div class="grid gap-5 md:grid-cols-2">
              <div>
                <label class="label">楽曲名</label>
                <input
                  type="text"
                  class="input w-full"
                  name="title"
                  required
                  classList={{ 'input-error': !!getFieldError('title') }}
                />
                <Show when={getFieldError('title')}>
                  {message => <p class="mt-1 text-xs text-error">{message()}</p>}
                </Show>
              </div>

              <div>
                <label class="label">楽曲種別</label>
                <select
                  class="select select-bordered w-full"
                  name="typeValue"
                  value={typeValue()}
                  onChange={e =>
                    setTypeValue(e.currentTarget.value === '' ? '' : (Number(e.currentTarget.value) as SongTypeValue))}
                  required
                  classList={{ 'select-error': !!getFieldError('typeValue') }}
                >
                  <option value="" disabled>
                    選択してください
                  </option>
                  <For each={types()}>{type => <option value={type.value}>{type.name}</option>}</For>
                </select>
                <Show when={getFieldError('typeValue')}>
                  {message => <p class="mt-1 text-xs text-error">{message()}</p>}
                </Show>
              </div>

              <div class="md:col-span-2">
                <label class="label">説明</label>
                <input
                  type="text"
                  class="input w-full"
                  name="description"
                  classList={{ 'input-error': !!getFieldError('description') }}
                />
                <Show when={getFieldError('description')}>
                  {message => <p class="mt-1 text-xs text-error">{message()}</p>}
                </Show>
              </div>

              <div class="md:col-span-2">
                <label class="label">歌詞リンク</label>
                <input
                  type="url"
                  class="input w-full"
                  name="lyricsLink"
                  placeholder="https://example.com/lyrics"
                  classList={{ 'input-error': !!getFieldError('lyricsLink') }}
                />
                <Show when={getFieldError('lyricsLink')}>
                  {message => <p class="mt-1 text-xs text-error">{message()}</p>}
                </Show>
              </div>

              <div>
                <label class="label">表示設定</label>
                <select class="select select-bordered w-full" name="isDisplay">
                  <option value="true" selected>
                    表示する
                  </option>
                  <option value="false">表示しない</option>
                </select>
              </div>

              <div class="md:col-span-2" />
            </div>
          </fieldset>

          <fieldset class="fieldset bg-base-200 border-base-300 rounded-box h-full border p-6">
            <legend class="px-2 text-sm font-semibold text-base-content/70">タグ</legend>
            <TagList />
          </fieldset>
        </div>

        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
          <legend class="px-2 text-sm font-semibold text-base-content/70">関係者</legend>
          <div class="space-y-4">
            <PersonSearchSection selections={personSelections} setSelections={setPersonSelections} />
          </div>
        </fieldset>

        <MediaSection
          entries={mediaEntries}
          setEntries={setMediaEntries}
          availableMedia={availableMedia}
          setAvailableMedia={setAvailableMedia}
        />

        <div class="flex justify-end">
          <button class="btn btn-primary" disabled={isSubmitting()}>
            {isSubmitting() ? '作成中...' : '作成'}
          </button>
        </div>
      </div>
    </form>
  );
};
