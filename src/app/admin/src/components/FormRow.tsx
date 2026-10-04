import { Show, type JSX } from 'solid-js';

interface FormRowProps {
  label: string;
  // 単一の入力欄に紐づけるときの id。選択肢ボタンなどのグループは省略し、グループ側に aria-label を付ける
  for?: string;
  hint?: string;
  children: JSX.Element;
}

// ラベルを左に置くフォーム行。狭い画面ではラベルを上に積む
export const FormRow = (props: FormRowProps) => (
  <div class="grid gap-2 border-b border-base-300 py-3 last:border-b-0 sm:grid-cols-[8rem_minmax(0,1fr)] sm:gap-5">
    <Show when={props.for} fallback={<span class="pt-1.5 text-sm text-base-content/70">{props.label}</span>}>
      {(id) => (
        <label class="pt-1.5 text-sm text-base-content/70" for={id()}>
          {props.label}
        </label>
      )}
    </Show>
    <div class="min-w-0 space-y-1.5">
      {props.children}
      <Show when={props.hint}>
        <p class="text-xs text-base-content/60">{props.hint}</p>
      </Show>
    </div>
  </div>
);
