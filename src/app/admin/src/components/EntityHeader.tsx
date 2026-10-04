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
}

// 詳細・作成画面の上部に固定する見出しと保存操作
// Layout のヘッダー (h-14) の直下に貼り付き、main の余白を打ち消して横幅いっぱいに広がる
export const EntityHeader = (props: EntityHeaderProps) => (
  <div class="sticky top-14 z-20 -mx-4 -mt-4 mb-4 border-b border-base-300 bg-base-100 px-4 py-3 sm:-mx-6 sm:-mt-6 sm:px-6">
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
      <div class="ml-auto flex flex-wrap items-center gap-2">
        <Show when={props.isDirty} fallback={<span class="text-sm text-base-content/60">変更なし</span>}>
          <span class="flex items-center gap-1.5 text-sm text-warning" role="status">
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
