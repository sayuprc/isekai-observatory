import { For, Show, createSignal, type JSX } from 'solid-js';
import { redirectToLogin } from '../utils/auth-redirect';
import type { KeywordSearch } from './keyword-search';
import { KeywordSearchPanel } from './KeywordSearchPanel';
import { defaultSelectedKeys, pickSelectedCandidates, type SongCandidate } from './song-import';

interface SongImportPanelProps<S extends object> {
  title: string;
  description: string;
  placeholder: string;
  search: KeywordSearch<S>;
  sourceLabel: (source: S) => string;
  // 検索せずに選べる取り込み元 (イベントの関連リリースなど)
  quickSources?: S[];
  quickSourcesTitle?: string;
  load: (source: S) => Promise<{ items: SongCandidate[] | undefined; status: number }>;
  importLabel: string;
  onImport: (candidates: SongCandidate[]) => void;
  disabled?: boolean;
  // 取り込み先の選択など、取り込みボタンの前に置く入力
  children?: JSX.Element;
}

type Loaded<S> = { source: S; candidates: SongCandidate[] };

export const SongImportPanel = <S extends object>(props: SongImportPanelProps<S>) => {
  const [loaded, setLoaded] = createSignal<Loaded<S> | null>(null);
  const [selectedKeys, setSelectedKeys] = createSignal<Set<string>>(new Set());
  const [isLoading, setIsLoading] = createSignal(false);
  const [error, setError] = createSignal<string | null>(null);
  const [message, setMessage] = createSignal<string | null>(null);
  // 読み込み中に別の取り込み元を選んだら、古い結果を捨てる
  let latestRequest = 0;

  const selectSource = async (source: S) => {
    const request = ++latestRequest;
    setError(null);
    setMessage(null);
    setLoaded(null);
    setIsLoading(true);

    const { items, status } = await props.load(source);

    if (request !== latestRequest) {
      return;
    }
    setIsLoading(false);

    if (status === 401) {
      redirectToLogin();
      return;
    }

    if (!items) {
      setError(`読み込みに失敗しました (${status})`);
      return;
    }

    setLoaded({ source, candidates: items });
    setSelectedKeys(defaultSelectedKeys(items));
  };

  const toggle = (key: string, checked: boolean) => {
    setSelectedKeys((current) => {
      const next = new Set(current);
      if (checked) {
        next.add(key);
      } else {
        next.delete(key);
      }
      return next;
    });
  };

  const selectAll = (candidates: SongCandidate[]) => {
    setSelectedKeys(new Set(candidates.filter((candidate) => candidate.selectable).map(({ key }) => key)));
  };

  const selectedCount = () => {
    const current = loaded();
    return current ? pickSelectedCandidates(current.candidates, selectedKeys()).length : 0;
  };

  const importSelected = () => {
    const current = loaded();
    if (!current) {
      return;
    }

    const picked = pickSelectedCandidates(current.candidates, selectedKeys());
    if (picked.length === 0) {
      return;
    }

    props.onImport(picked);
    setLoaded(null);
    setMessage(`${props.sourceLabel(current.source)} から ${picked.length} 件を追加しました`);
  };

  return (
    <details class="collapse-arrow collapse rounded-box border border-base-300 bg-base-100">
      <summary class="collapse-title text-sm font-semibold">{props.title}</summary>
      <div class="collapse-content space-y-3">
        <p class="text-sm text-base-content/60">{props.description}</p>

        <Show when={props.quickSources && props.quickSources.length > 0}>
          <div>
            <p class="mb-1 text-xs text-base-content/60">{props.quickSourcesTitle}</p>
            <div class="flex flex-wrap gap-1">
              <For each={props.quickSources}>
                {(source) => (
                  <button
                    type="button"
                    class="btn btn-outline btn-xs"
                    disabled={props.disabled}
                    onClick={() => selectSource(source)}
                  >
                    {props.sourceLabel(source)}
                  </button>
                )}
              </For>
            </div>
          </div>
        </Show>

        <KeywordSearchPanel
          title="取り込み元を検索"
          placeholder={props.placeholder}
          search={props.search}
          itemLabel={props.sourceLabel}
          onAdd={selectSource}
          disabled={props.disabled}
          emptyResultMessage="該当する取り込み元がありません"
          actionLabel="選択"
        />

        <Show when={isLoading()}>
          <p class="text-sm text-base-content/60">読み込み中...</p>
        </Show>
        <Show when={error()}>{(text) => <p class="text-sm text-error">{text()}</p>}</Show>
        <Show when={message()}>{(text) => <p class="text-sm text-success">{text()}</p>}</Show>

        <Show when={loaded()}>
          {(current) => (
            <div class="rounded-box border border-base-300 p-3">
              <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <p class="text-sm font-semibold">{props.sourceLabel(current().source)}</p>
                <div class="flex gap-1">
                  <button type="button" class="btn btn-ghost btn-xs" onClick={() => selectAll(current().candidates)}>
                    全選択
                  </button>
                  <button type="button" class="btn btn-ghost btn-xs" onClick={() => setSelectedKeys(new Set())}>
                    選択解除
                  </button>
                  <button type="button" class="btn btn-ghost btn-xs" onClick={() => setLoaded(null)}>
                    閉じる
                  </button>
                </div>
              </div>
              <Show
                when={current().candidates.length > 0}
                fallback={<p class="text-sm text-base-content/60">取り込める楽曲がありません</p>}
              >
                <ul class="max-h-72 space-y-1 overflow-y-auto">
                  <For each={current().candidates}>
                    {(candidate) => (
                      <li>
                        <label
                          class="flex items-center gap-2 rounded px-2 py-1 hover:bg-base-200"
                          classList={{ 'opacity-50': !candidate.selectable }}
                        >
                          <input
                            type="checkbox"
                            class="checkbox checkbox-sm checkbox-primary"
                            checked={selectedKeys().has(candidate.key)}
                            disabled={props.disabled || !candidate.selectable}
                            onChange={(e) => toggle(candidate.key, e.currentTarget.checked)}
                          />
                          <span class="text-sm font-medium">{candidate.title}</span>
                          <Show when={candidate.songId === null}>
                            <span class="badge badge-ghost badge-sm">楽曲なし</span>
                          </Show>
                          <span class="text-xs text-base-content/60">{candidate.note}</span>
                        </label>
                      </li>
                    )}
                  </For>
                </ul>
              </Show>
              <div class="mt-3 flex flex-wrap items-end justify-end gap-2">
                {props.children}
                <button
                  type="button"
                  class="btn btn-primary btn-sm"
                  disabled={props.disabled || selectedCount() === 0}
                  onClick={importSelected}
                >
                  {props.importLabel} ({selectedCount()} 件)
                </button>
              </div>
            </div>
          )}
        </Show>
      </div>
    </details>
  );
};
