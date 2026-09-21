import { createResource, createSignal, For, Match, Show, Switch } from 'solid-js';
import type {
  Media,
  RequestSongPerson,
  Song,
  SongPerson,
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
import { buildSongMediaRequest, MediaSection, toMediaEntry, type MediaEntry } from './MediaSection';
import { toRequestSongPersons, type PersonSelections, type SelectedPerson } from './person-selection';
import { PersonSearchSection } from './PersonSearchSection';

type SongTagEntry = {
  songTagId: string;
};

interface DetailViewProps {
  songId: string;
}

type EditableFormData = { song: Song; types: SongType[]; tags: SongTag[]; media: Media[] };

interface EditableFormProps {
  data: EditableFormData;
}

interface FetchOkState {
  status: 'ok';
  data: EditableFormData;
}

interface FetchErrorState {
  status: 'error';
}

type FetchState = FetchOkState | FetchErrorState;

const getListUrl = () => {
  const back = new URLSearchParams(window.location.search).get('back') ?? '';
  const listQuery = (() => {
    if (!back.startsWith('?')) return '';
    try {
      const q = new URLSearchParams(back.slice(1)).toString();
      return q ? `?${q}` : '';
    } catch {
      return '';
    }
  })();

  return `/songs${listQuery}`;
};

export const DetailView = (props: DetailViewProps) => {
  const listUrl = getListUrl();

  const [resource, { refetch }] = createResource(async (): Promise<FetchState> => {
    const { data, status } = await client.api.songs({ songId: props.songId })['edit-form'].get();

    if (status === 401) {
      window.location.href = '/auth/login';
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
      <Match when={loadedData()}>{data => <EditableForm data={data()} />}</Match>
    </Switch>
  );
};

export const EditableForm = (props: EditableFormProps) => {
  const listUrl = getListUrl();

  const types = props.data.types;
  const [typeValue, setTypeValue] = createSignal<SongTypeValue | ''>(props.data.song.type.value);
  const availableTags = props.data.tags;
  const initialAvailableMedia = (() => {
    const items = [...props.data.media];
    for (const item of props.data.song.media) {
      if (!items.some(media => media.mediaId === item.mediaId)) {
        items.push({
          mediaId: item.mediaId,
          title: item.title,
          url: item.url,
          publishedAt: item.publishedAt,
          type: item.type,
          isDisplay: item.isDisplay,
        });
      }
    }
    return items;
  })();

  const toSelectedPersons = (items: SongPerson[] | undefined): SelectedPerson[] =>
    (items ?? []).map(item => ({ personId: item.personId, name: item.name }));

  const persons = props.data.song.persons;
  const [personSelections, setPersonSelections] = createSignal<PersonSelections>({
    1: toSelectedPersons(persons.filter(person => person.role === 1)),
    2: toSelectedPersons(persons.filter(person => person.role === 2)),
    3: toSelectedPersons(persons.filter(person => person.role === 3)),
  });
  const [tags, setTags] = createSignal<SongTagEntry[]>(props.data.song.tags.map(tag => ({ songTagId: tag.songTagId })));
  const [availableMedia, setAvailableMedia] = createSignal<Media[]>(initialAvailableMedia);
  const [mediaEntries, setMediaEntries] = createSignal<MediaEntry[]>(props.data.song.media.map(toMediaEntry));
  const [tagPickerValue, setTagPickerValue] = createSignal('');

  const { formError, setFormError, getFieldError, clearErrors, handleError } = createFormErrors();
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

  const handleSubmit = async (e: Event) => {
    e.preventDefault();
  };

  const handleDelete = withSubmitting(async () => {
    if (!window.confirm('削除します。よろしいですか？')) {
      return;
    }

    clearErrors();

    const songId = props.data.song.songId;

    if (!songId) {
      setFormError('削除対象の楽曲IDを取得できませんでした');
      return;
    }

    const { error, status } = await client.api.songs({ songId: songId }).delete();

    if (error) {
      handleError(status, error);
      return;
    }

    setFlash('削除しました');
    window.location.href = listUrl;
  });

  const handleUpdate = withSubmitting(async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const form = (e.target as HTMLButtonElement).form as HTMLFormElement;
    const formData = new FormData(form);

    const songId = props.data.song.songId;

    if (!songId) {
      setFormError('更新対象の楽曲IDを取得できませんでした');
      return;
    }

    const { data, error, status } = await client.api.songs({ songId: songId }).put({
      title: formData.get('title')?.toString() ?? '',
      description: formData.get('description')?.toString() ?? '',
      lyricsLink: normalizeOptionalString(formData.get('lyricsLink')),
      typeValue: typeValue() as SongTypeValue,
      isDisplay: formData.get('isDisplay') === 'true',
      orderNo: Number(formData.get('orderNo')),
      persons: buildPersons(),
      tags: tags(),
      media: buildSongMediaRequest(mediaEntries()),
    });

    if (data) {
      setFlash('更新しました');
      window.location.href = listUrl;
      return;
    }

    if (status === 404) {
      setFlash('データがありません', 'error');
      window.location.href = listUrl;
      return;
    }

    handleError(status, error);
  });

  const tagOptions = () => {
    const selectedTagIds = new Set(tags().map(entry => entry.songTagId));

    return availableTags
      .filter(tag => !selectedTagIds.has(tag.songTagId))
      .map(tag => ({ value: tag.songTagId, label: tag.name }));
  };

  const selectedTags = () =>
    tags()
      .map(entry => availableTags.find(tag => tag.songTagId === entry.songTagId))
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
    <>
      <a href={listUrl} class="btn btn-ghost btn-sm mb-4">
        ← 一覧に戻る
      </a>
      <FormError message={formError()} onClose={clearErrors} />
      <form onsubmit={handleSubmit}>
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
                    value={props.data.song.title}
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
                    <For each={types}>{type => <option value={type.value}>{type.name}</option>}</For>
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
                    value={props.data.song.description}
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
                    value={props.data.song.lyricsLink ?? ''}
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
                    <option value="true" selected={props.data.song.isDisplay === true}>
                      表示する
                    </option>
                    <option value="false" selected={props.data.song.isDisplay === false}>
                      表示しない
                    </option>
                  </select>
                </div>

                <div>
                  <label class="label">表示順</label>
                  <input
                    type="number"
                    class="input w-full"
                    name="orderNo"
                    required
                    min="1"
                    value={props.data.song.orderNo}
                  />
                </div>
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
            <button onClick={handleUpdate} class="btn btn-primary" disabled={isSubmitting()}>
              {isSubmitting() ? '更新中...' : '更新'}
            </button>
          </div>
        </div>
      </form>

      <div class="divider max-w-4xl" />

      <div class="max-w-4xl rounded-box border border-error/20 bg-error/5 p-6">
        <h3 class="font-semibold text-error">危険な操作</h3>
        <p class="mt-1 text-sm text-base-content/60">この操作は取り消せません。</p>
        <div class="mt-4">
          <button onClick={handleDelete} class="btn btn-outline btn-error btn-sm" disabled={isSubmitting()}>
            {isSubmitting() ? '削除中...' : 'この楽曲を削除する'}
          </button>
        </div>
      </div>
    </>
  );
};
