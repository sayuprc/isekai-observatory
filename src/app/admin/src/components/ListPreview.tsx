import { For, Show, type JSX } from 'solid-js';

interface ListWithPreviewProps {
  table: JSX.Element;
  preview: JSX.Element;
}

// 一覧の表と、選択中の行のプレビューを並べる
// プレビューはコンテナ幅 64rem 以上でだけ右に出し、狭い画面では表だけにする
export const ListWithPreview = (props: ListWithPreviewProps) => (
  <div class="@container">
    <div class="grid gap-4 @5xl:grid-cols-[minmax(0,1fr)_20rem] @5xl:items-start">
      <div class="min-w-0 overflow-x-auto rounded-box border border-base-300 bg-base-100">{props.table}</div>
      <div class="hidden @5xl:sticky @5xl:top-20 @5xl:block">{props.preview}</div>
    </div>
  </div>
);

// 選択中の行を強調する tr のクラス
export const selectedRowClass = 'bg-info/10 shadow-[inset_3px_0_0_var(--color-info)]';

export interface RecordPreviewData {
  title: string;
  meta?: JSX.Element;
  rows: { label: string; value: string | number }[];
  // external は外部サイトへのリンクで、別タブで開く
  actions: { label: string; href: string; primary?: boolean; external?: boolean }[];
}

interface RecordPreviewProps {
  label: string;
  record: RecordPreviewData | undefined;
}

// 選択中の行の概要。件数などの項目と、詳細へ移るリンクを出す
export const RecordPreview = (props: RecordPreviewProps) => (
  <section class="rounded-box border border-base-300 bg-base-200" aria-label={props.label}>
    <Show
      when={props.record}
      fallback={<p class="px-5 py-6 text-sm text-base-content/60">行を選ぶと概要を表示します</p>}
    >
      {(record) => (
        <>
          <div class="border-b border-base-300 px-5 py-4">
            <p class="leading-snug font-semibold break-words">{record().title}</p>
            <Show when={record().meta}>
              <div class="mt-2 flex flex-wrap gap-1.5">{record().meta}</div>
            </Show>
          </div>
          <dl class="grid grid-cols-[minmax(0,1fr)_auto] gap-x-4 gap-y-1.5 border-b border-base-300 px-5 py-3 text-sm">
            <For each={record().rows}>
              {(row) => (
                <>
                  <dt class="text-base-content/60">{row.label}</dt>
                  <dd class="text-right font-mono">{row.value}</dd>
                </>
              )}
            </For>
          </dl>
          <div class="flex flex-wrap gap-2 px-5 py-4">
            <For each={record().actions}>
              {(action) => (
                <a
                  href={action.href}
                  class="btn btn-sm"
                  classList={{ 'btn-primary': action.primary }}
                  target={action.external ? '_blank' : undefined}
                  rel={action.external ? 'noreferrer' : undefined}
                >
                  {action.label}
                </a>
              )}
            </For>
          </div>
        </>
      )}
    </Show>
  </section>
);
