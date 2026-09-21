import { For, Index, Show, createSignal } from 'solid-js';
import type { ReleaseGetResponse, SongSummary } from '../../generated';
import { client } from '../../utils/client';
import { createSortable, reorderItems } from '../sortable';

/**
 * songId が null のトラックは管理対象外楽曲(タイトルのみトラック)で title が必須
 * songId ありのトラックは title が空なら楽曲名で表示、入力があれば上書き名になる
 */
export type TrackForm = {
  songId: string | null;
  songTitle: string | null;
  title: string;
};

export type MediumForm = {
  name: string;
  tracks: TrackForm[];
};

/** API レスポンスからフォーム状態を組み立てる(参照トラックの楽曲名は収録曲 read model から引く) */
export const toMediumForms = (data: ReleaseGetResponse): MediumForm[] => {
  const titleBySongId = new Map(
    data.songs.flatMap(song => (song.songId !== null ? [[song.songId, song.title] as const] : [])),
  );

  return data.release.media.map(medium => ({
    name: medium.name ?? '',
    tracks: medium.tracks.map(track => ({
      songId: track.songId,
      songTitle: track.songId !== null ? titleBySongId.get(track.songId) ?? track.songId : null,
      title: track.title ?? '',
    })),
  }));
};

/** フォーム状態を API の media リクエスト形へ変換する(position / trackNo は並び順から採番、空の title / name は null) */
export const toMediaPayload = (media: MediumForm[]) =>
  media.map((medium, mediumIndex) => ({
    position: mediumIndex + 1,
    name: medium.name.trim() === '' ? null : medium.name.trim(),
    tracks: medium.tracks.map((track, trackIndex) => ({
      songId: track.songId,
      title: track.title.trim() === '' ? null : track.title.trim(),
      trackNo: trackIndex + 1,
    })),
  }));

const TrackSongLabel = (props: { track: TrackForm }) => (
  <Show when={props.track.songId !== null} fallback={<span class="badge badge-ghost badge-sm">対象外</span>}>
    {props.track.songTitle}
  </Show>
);

interface TrackActionsProps {
  label: string;
  songId: string | null;
  canMoveUp: boolean;
  canMoveDown: boolean;
  onMoveUp: () => void;
  onMoveDown: () => void;
  onRemove: () => void;
}

const TrackActions = (props: TrackActionsProps) => (
  <div class="flex flex-wrap justify-end gap-2">
    <button
      type="button"
      class="btn btn-ghost btn-xs"
      aria-label={`${props.label}を上へ移動`}
      disabled={!props.canMoveUp}
      onClick={props.onMoveUp}
    >
      ↑
    </button>
    <button
      type="button"
      class="btn btn-ghost btn-xs"
      aria-label={`${props.label}を下へ移動`}
      disabled={!props.canMoveDown}
      onClick={props.onMoveDown}
    >
      ↓
    </button>
    <Show when={props.songId}>
      {songId => (
        <a href={`/songs/${songId()}`} class="btn btn-ghost btn-xs">
          楽曲を見る
        </a>
      )}
    </Show>
    <button type="button" class="btn btn-outline btn-error btn-xs" onClick={props.onRemove}>
      削除
    </button>
  </div>
);

interface MediaEditorProps {
  media: MediumForm[];
  onChange: (updater: (prev: MediumForm[]) => MediumForm[]) => void;
  fieldError?: string;
}

export const MediaEditor = (props: MediaEditorProps) => {
  const reorderMedia = (fromIndex: number, toIndex: number) => {
    props.onChange(prev => reorderItems(prev, fromIndex, toIndex));
  };
  const reorderTracks = (mediumIndex: number, fromIndex: number, toIndex: number) => {
    props.onChange(prev => prev.map((medium, index) => (
      index === mediumIndex
        ? { ...medium, tracks: reorderItems(medium.tracks, fromIndex, toIndex) }
        : medium
    )));
  };
  const mediaSortable = createSortable((_scope, fromIndex, toIndex) => reorderMedia(fromIndex, toIndex));
  const trackSortable = createSortable<number>(reorderTracks);
  const [searchTitle, setSearchTitle] = createSignal('');
  const [manualTitle, setManualTitle] = createSignal('');
  const [searchResults, setSearchResults] = createSignal<SongSummary[]>([]);
  const [searchError, setSearchError] = createSignal<string | null>(null);
  const [isSearching, setIsSearching] = createSignal(false);
  const [hasSearched, setHasSearched] = createSignal(false);
  const [targetMediumIndex, setTargetMediumIndex] = createSignal(0);

  const addMedium = () => {
    props.onChange(prev => [...prev, { name: '', tracks: [] }]);
  };

  const removeMedium = (index: number) => {
    props.onChange(prev => prev.filter((_, i) => i !== index));
    setTargetMediumIndex(0);
  };

  const setMediumName = (index: number, name: string) => {
    props.onChange(prev => prev.map((medium, i) => (i === index ? { ...medium, name } : medium)));
  };

  const appendTrack = (track: TrackForm) => {
    const index = Math.min(targetMediumIndex(), props.media.length - 1);

    if (index < 0) {
      return;
    }

    props.onChange(prev =>
      prev.map((medium, i) => (i === index ? { ...medium, tracks: [...medium.tracks, track] } : medium)));
  };

  const addTrack = (song: SongSummary) => {
    appendTrack({ songId: song.songId, songTitle: song.title, title: '' });
  };

  const addTitleOnlyTrack = () => {
    const title = manualTitle().trim();

    if (title === '') {
      return;
    }

    appendTrack({ songId: null, songTitle: null, title });
    setManualTitle('');
  };

  const setTrackTitle = (mediumIndex: number, trackIndex: number, title: string) => {
    props.onChange(prev =>
      prev.map((medium, i) =>
        i === mediumIndex
          ? { ...medium, tracks: medium.tracks.map((track, j) => (j === trackIndex ? { ...track, title } : track)) }
          : medium,
      ));
  };

  const removeTrack = (mediumIndex: number, trackIndex: number) => {
    props.onChange(prev =>
      prev.map((medium, i) =>
        i === mediumIndex
          ? { ...medium, tracks: medium.tracks.filter((_, j) => j !== trackIndex) }
          : medium,
      ));
  };

  const handleSongSearch = async (e: Event) => {
    e.preventDefault();
    setSearchError(null);
    setHasSearched(true);

    if (searchTitle().trim() === '') {
      setSearchResults([]);
      setSearchError('楽曲名を入力してください');
      return;
    }

    setIsSearching(true);

    const { data, error, status } = await client.api.songs.search.get({
      query: {
        title: searchTitle().trim(),
        sort: 'title',
        order: 'asc',
        page: 1,
        per_page: 25,
      },
    });

    setIsSearching(false);

    if (status === 401) {
      window.location.href = '/auth/login';
      return;
    }

    if (!data) {
      if (typeof error === 'object' && error !== null && 'value' in error) {
        const body = (error as { value?: { message?: string } }).value;
        setSearchError(body?.message ?? `検索に失敗しました (${status})`);
      } else {
        setSearchError(`検索に失敗しました (${status})`);
      }
      setSearchResults([]);
      return;
    }

    setSearchResults(data.songs);
  };

  return (
    <fieldset class="rounded-box border border-base-300 bg-base-200 p-6">
      <legend class="px-2 text-sm font-semibold text-base-content/70">媒体と収録楽曲</legend>
      <Show when={props.fieldError}>{message => <p class="mb-4 text-sm text-error">{message()}</p>}</Show>
      <div class="space-y-6">
        <Show
          when={props.media.length > 0}
          fallback={<p class="text-sm text-base-content/60">媒体はまだ登録されていません。</p>}
        >
          <Index each={props.media}>
            {(medium, mediumIndex) => (
              <div
                {...mediaSortable.dropTargetProps('release-media', mediumIndex)}
                class="rounded-box border border-base-300 bg-base-100 p-4 transition-colors"
                classList={{
                  'opacity-50': mediaSortable.isDragging('release-media', mediumIndex),
                  'border-primary bg-primary/5': mediaSortable.isDropTarget('release-media', mediumIndex),
                }}
              >
                <div class="flex flex-wrap items-end justify-between gap-4">
                  <div class="flex flex-wrap items-end gap-4">
                    <button
                      {...mediaSortable.dragHandleProps('release-media', mediumIndex, `媒体${mediumIndex + 1}`)}
                      class="btn btn-ghost btn-sm mb-1 cursor-grab active:cursor-grabbing"
                    >
                      ⠿
                    </button>
                    <span class="badge badge-neutral badge-sm mb-2">媒体 {mediumIndex + 1}</span>
                    <div>
                      <label class="label">表示ラベル(任意)</label>
                      <input
                        type="text"
                        class="input input-bordered input-sm"
                        value={medium().name}
                        onInput={e => setMediumName(mediumIndex, e.currentTarget.value)}
                        placeholder="CD1 / Blu-ray など"
                      />
                    </div>
                  </div>
                  <div class="flex gap-2">
                    <button
                      type="button"
                      class="btn btn-ghost btn-xs"
                      aria-label={`媒体${mediumIndex + 1}を上へ移動`}
                      disabled={mediumIndex === 0}
                      onClick={() => reorderMedia(mediumIndex, mediumIndex - 1)}
                    >
                      ↑
                    </button>
                    <button
                      type="button"
                      class="btn btn-ghost btn-xs"
                      aria-label={`媒体${mediumIndex + 1}を下へ移動`}
                      disabled={mediumIndex === props.media.length - 1}
                      onClick={() => reorderMedia(mediumIndex, mediumIndex + 1)}
                    >
                      ↓
                    </button>
                    <button
                      type="button"
                      class="btn btn-outline btn-error btn-xs"
                      onClick={() => removeMedium(mediumIndex)}
                    >
                      媒体を削除
                    </button>
                  </div>
                </div>

                <div class="mt-4">
                  <Show
                    when={medium().tracks.length > 0}
                    fallback={<p class="text-sm text-base-content/60">収録楽曲はまだ登録されていません。</p>}
                  >
                    {/* For はオブジェクト同一性でキーするため、入力のたびに行が再生成されて IME が中断される。Index で DOM を保つ */}
                    <ul class="rounded-box border border-base-300 md:hidden">
                      <Index each={medium().tracks}>
                        {(track, trackIndex) => (
                          <li
                            {...trackSortable.dropTargetProps(mediumIndex, trackIndex)}
                            class="border-b border-base-300 p-3 last:border-b-0"
                            classList={{
                              'opacity-50': trackSortable.isDragging(mediumIndex, trackIndex),
                              'bg-primary/5': trackSortable.isDropTarget(mediumIndex, trackIndex),
                            }}
                          >
                            <div class="flex items-center gap-2">
                              <button
                                {...trackSortable.dragHandleProps(mediumIndex, trackIndex, `曲順${trackIndex + 1}`)}
                              >
                                ⠿
                              </button>
                              <span class="text-sm">{trackIndex + 1}</span>
                              <span class="min-w-0 flex-1 truncate text-sm">
                                <TrackSongLabel track={track()} />
                              </span>
                            </div>
                            <input
                              type="text"
                              class="input input-bordered input-sm mt-2 w-full"
                              value={track().title}
                              onInput={e => setTrackTitle(mediumIndex, trackIndex, e.currentTarget.value)}
                              placeholder={track().songId !== null ? track().songTitle ?? '' : 'トラック名を入力'}
                            />
                            <div class="mt-2">
                              <TrackActions
                                label={`曲順${trackIndex + 1}`}
                                songId={track().songId}
                                canMoveUp={trackIndex > 0}
                                canMoveDown={trackIndex < medium().tracks.length - 1}
                                onMoveUp={() => reorderTracks(mediumIndex, trackIndex, trackIndex - 1)}
                                onMoveDown={() => reorderTracks(mediumIndex, trackIndex, trackIndex + 1)}
                                onRemove={() => removeTrack(mediumIndex, trackIndex)}
                              />
                            </div>
                          </li>
                        )}
                      </Index>
                    </ul>
                    <div class="hidden overflow-x-auto rounded-box border border-base-300 md:block">
                      <table class="table table-sm">
                        <thead>
                          <tr>
                            <th>曲順</th>
                            <th>楽曲</th>
                            <th>トラック名</th>
                            <th class="text-right">操作</th>
                          </tr>
                        </thead>
                        <tbody>
                          <Index each={medium().tracks}>
                            {(track, trackIndex) => (
                              <tr
                                {...trackSortable.dropTargetProps(mediumIndex, trackIndex)}
                                classList={{
                                  'opacity-50': trackSortable.isDragging(mediumIndex, trackIndex),
                                  'bg-primary/5': trackSortable.isDropTarget(mediumIndex, trackIndex),
                                }}
                              >
                                <td>
                                  <div class="flex items-center gap-1">
                                    <button
                                      {...trackSortable.dragHandleProps(
                                        mediumIndex,
                                        trackIndex,
                                        `曲順${trackIndex + 1}`,
                                      )}
                                    >
                                      ⠿
                                    </button>
                                    <span>{trackIndex + 1}</span>
                                  </div>
                                </td>
                                <td>
                                  <TrackSongLabel track={track()} />
                                </td>
                                <td>
                                  <input
                                    type="text"
                                    class="input input-bordered input-sm w-full min-w-48"
                                    value={track().title}
                                    onInput={e => setTrackTitle(mediumIndex, trackIndex, e.currentTarget.value)}
                                    placeholder={track().songId !== null ? track().songTitle ?? '' : 'トラック名を入力'}
                                  />
                                </td>
                                <td>
                                  <TrackActions
                                    label={`曲順${trackIndex + 1}`}
                                    songId={track().songId}
                                    canMoveUp={trackIndex > 0}
                                    canMoveDown={trackIndex < medium().tracks.length - 1}
                                    onMoveUp={() => reorderTracks(mediumIndex, trackIndex, trackIndex - 1)}
                                    onMoveDown={() => reorderTracks(mediumIndex, trackIndex, trackIndex + 1)}
                                    onRemove={() => removeTrack(mediumIndex, trackIndex)}
                                  />
                                </td>
                              </tr>
                            )}
                          </Index>
                        </tbody>
                      </table>
                    </div>
                    <p class="mt-2 text-xs text-base-content/60">
                      トラック名が空の場合は楽曲名で表示されます(管理対象外楽曲では必須です)。
                    </p>
                  </Show>
                </div>
              </div>
            )}
          </Index>
        </Show>

        <div>
          <button type="button" class="btn btn-outline btn-sm" onClick={addMedium}>
            媒体を追加
          </button>
        </div>

        <Show when={props.media.length > 0}>
          <div>
            <label class="label">楽曲を追加</label>
            <div class="flex flex-col gap-4 md:flex-row md:items-end">
              <div>
                <label class="label">追加先媒体</label>
                <select
                  class="select select-bordered select-sm"
                  value={String(Math.min(targetMediumIndex(), props.media.length - 1))}
                  onChange={e => setTargetMediumIndex(Number(e.currentTarget.value))}
                >
                  <Index each={props.media}>
                    {(medium, index) => (
                      <option value={index}>
                        媒体
                        {' '}
                        {index + 1}
                        {medium().name.trim() !== '' ? `(${medium().name.trim()})` : ''}
                      </option>
                    )}
                  </Index>
                </select>
              </div>
              <div class="flex-1">
                <label class="label">楽曲名</label>
                <input
                  type="text"
                  class="input input-bordered w-full"
                  value={searchTitle()}
                  onInput={e => setSearchTitle(e.currentTarget.value)}
                  placeholder="楽曲名で検索"
                />
              </div>
              <button type="button" class="btn btn-primary" disabled={isSearching()} onClick={handleSongSearch}>
                {isSearching() ? '検索中...' : '検索'}
              </button>
            </div>

            <Show when={searchError()}>{message => <p class="mt-3 text-sm text-error">{message()}</p>}</Show>

            <Show when={hasSearched()}>
              <ul class="mt-4 rounded-box border border-base-300 bg-base-100 md:hidden">
                <Show
                  when={searchResults().length > 0}
                  fallback={(
                    <li class="p-3 text-center text-sm text-base-content/60">条件に一致する楽曲はありません。</li>
                  )}
                >
                  <For each={searchResults()}>
                    {song => (
                      <li class="flex items-center justify-between gap-3 border-b border-base-300 p-3 last:border-b-0">
                        <div class="min-w-0">
                          <p class="truncate text-sm font-medium">{song.title}</p>
                          <p class="mt-1 text-xs text-base-content/60">
                            {song.type.name}
                            {' · '}
                            {song.isDisplay ? '表示する' : '表示しない'}
                          </p>
                        </div>
                        <button type="button" class="btn btn-primary btn-xs shrink-0" onClick={() => addTrack(song)}>
                          追加
                        </button>
                      </li>
                    )}
                  </For>
                </Show>
              </ul>
              <div class="mt-4 hidden overflow-x-auto rounded-box border border-base-300 bg-base-100 md:block">
                <table class="table table-sm">
                  <thead>
                    <tr>
                      <th>楽曲名</th>
                      <th>種別</th>
                      <th>表示設定</th>
                      <th class="text-right">操作</th>
                    </tr>
                  </thead>
                  <tbody>
                    <Show
                      when={searchResults().length > 0}
                      fallback={(
                        <tr>
                          <td colSpan={4} class="text-center text-sm text-base-content/60">
                            条件に一致する楽曲はありません。
                          </td>
                        </tr>
                      )}
                    >
                      <For each={searchResults()}>
                        {song => (
                          <tr>
                            <td>{song.title}</td>
                            <td>{song.type.name}</td>
                            <td>{song.isDisplay ? '表示する' : '表示しない'}</td>
                            <td class="text-right">
                              <button
                                type="button"
                                class="btn btn-primary btn-xs"
                                onClick={() => addTrack(song)}
                              >
                                追加
                              </button>
                            </td>
                          </tr>
                        )}
                      </For>
                    </Show>
                  </tbody>
                </table>
              </div>
            </Show>
          </div>

          <div>
            <label class="label">管理対象外楽曲を追加</label>
            <div class="flex flex-col gap-4 md:flex-row md:items-end">
              <div class="flex-1">
                <label class="label">タイトル</label>
                <input
                  type="text"
                  class="input input-bordered w-full"
                  value={manualTitle()}
                  onInput={e => setManualTitle(e.currentTarget.value)}
                  placeholder="タイトルを直接入力"
                />
              </div>
              <button
                type="button"
                class="btn btn-outline"
                disabled={manualTitle().trim() === ''}
                onClick={addTitleOnlyTrack}
              >
                追加
              </button>
            </div>
            <p class="mt-1 text-xs text-base-content/60">
              管理していない楽曲をタイトルだけで収録曲に追加します(楽曲詳細へのリンクは付きません)。
            </p>
          </div>
        </Show>
      </div>
    </fieldset>
  );
};
