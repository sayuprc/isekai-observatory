import { Show, type JSX } from 'solid-js';

interface EntityHeaderProps {
  breadcrumb: { href: string; label: string };
  title: string;
  // 種別や状態などのチップ
  meta?: JSX.Element;
  // 保存ボタンを form 属性で紐づけるフォームの id
  formId: string;
  isDirty: boolean;
  isSubmitting: boolean;
  submitLabel: string;
  submittingLabel: string;
  onDiscard?: () => void;
  // ActionMenu など
  menu?: JSX.Element;
  // TabList など。見出しの下端に揃えて置く
  tabs?: JSX.Element;
}

// 詳細・作成画面の見出しと保存操作
// sm 以上は Layout のヘッダー (h-14) の直下に貼り付き、main の余白を打ち消して横幅いっぱいに広がる
// sm 未満は見出しが折り返して画面を占めるので固定せず、保存操作だけを画面下に固定する
export const EntityHeader = (props: EntityHeaderProps) => (
  <div class="z-20 -mx-4 -mt-4 mb-4 border-b border-base-300 bg-base-100 px-4 py-3 sm:sticky sm:top-14 sm:-mx-6 sm:-mt-6 sm:px-6">
    <nav aria-label="パンくずリスト" class="text-xs text-base-content/60">
      <a href={props.breadcrumb.href} class="link link-hover">
        {props.breadcrumb.label}
      </a>
      <span class="mx-2" aria-hidden="true">
        /
      </span>
      <span>{props.title}</span>
    </nav>
    <div class="mt-1 flex flex-wrap items-center gap-x-5 gap-y-2">
      <h2 class="min-w-0 text-xl font-bold break-words">{props.title}</h2>
      <Show when={props.meta}>
        <div class="flex flex-wrap gap-1.5">{props.meta}</div>
      </Show>
      {/* Layout の main は data-mobile-actionbar を持つ要素があると、下に固定したバーの分だけ余白を足す */}
      <div
        data-mobile-actionbar
        class="fixed inset-x-0 bottom-0 z-30 flex items-center gap-2 border-t border-base-300 bg-base-200 px-4 py-3 sm:static sm:z-auto sm:ml-auto sm:flex-wrap sm:border-0 sm:bg-transparent sm:p-0"
      >
        <Show when={props.isDirty} fallback={<span class="text-sm text-base-content/60 max-sm:mr-auto">変更なし</span>}>
          <span class="flex items-center gap-1.5 text-sm text-warning max-sm:mr-auto" role="status">
            <span class="size-2 rounded-full bg-warning" aria-hidden="true" />
            未保存の変更あり
          </span>
          <Show when={props.onDiscard}>
            {(onDiscard) => (
              <button type="button" class="btn btn-sm" disabled={props.isSubmitting} onClick={() => onDiscard()()}>
                破棄
              </button>
            )}
          </Show>
        </Show>
        <button
          type="submit"
          form={props.formId}
          class="btn btn-primary btn-sm"
          disabled={!props.isDirty || props.isSubmitting}
        >
          {props.isSubmitting ? props.submittingLabel : props.submitLabel}
        </button>
        {props.menu}
      </div>
    </div>
    <Show when={props.tabs}>
      <div class="-mb-3 mt-2">{props.tabs}</div>
    </Show>
  </div>
);

// EntityHeader の meta に並べるチップ
export const MetaChip = (props: { children: JSX.Element; dot?: 'success' | 'muted' }) => (
  <span class="badge badge-outline badge-sm gap-1.5 border-base-300 whitespace-nowrap text-base-content/80">
    <Show when={props.dot}>
      <span
        class="size-1.5 rounded-full"
        classList={{ 'bg-success': props.dot === 'success', 'bg-base-content/40': props.dot === 'muted' }}
        aria-hidden="true"
      />
    </Show>
    {props.children}
  </span>
);
