import { Show, createSignal, type JSX } from 'solid-js';

interface CoVocalistChipProps {
  name: string;
  creditName: string;
  onCreditNameChange: (creditName: string) => void;
  // createSortable の draggableProps と dropTargetProps を渡す
  sortableProps: JSX.HTMLAttributes<HTMLElement>;
  isDragging: boolean;
  isDropTarget: boolean;
  onRemove: () => void;
}

// 共演者 1 人分のチップ. 名前をクリックするとクレジット名を編集でき、ドラッグで並べ替えられる
export const CoVocalistChip = (props: CoVocalistChipProps) => {
  const [editing, setEditing] = createSignal(false);

  return (
    <span
      {...props.sortableProps}
      // クレジット名の入力中は、テキスト選択がドラッグにならないよう止める
      draggable={!editing()}
      class="badge badge-outline cursor-grab gap-1 whitespace-nowrap active:cursor-grabbing"
      classList={{ 'opacity-50': props.isDragging, 'badge-primary': props.isDropTarget }}
      title="ドラッグで並べ替え"
    >
      <Show
        when={editing()}
        fallback={
          <button
            type="button"
            class="cursor-text"
            aria-label={`${props.name}のクレジット名を編集`}
            title="クリックでクレジット名を編集"
            onClick={() => setEditing(true)}
          >
            {props.name}
            <Show when={props.creditName !== ''}>
              <span class="text-base-content/60"> ({props.creditName})</span>
            </Show>
          </button>
        }
      >
        <span>{props.name}</span>
        <input
          ref={(el) => queueMicrotask(() => el.focus())}
          type="text"
          class="input input-xs w-28"
          aria-label={`${props.name}のクレジット名`}
          placeholder="クレジット名(任意)"
          value={props.creditName}
          onInput={(e) => props.onCreditNameChange(e.currentTarget.value)}
          onBlur={() => setEditing(false)}
          onKeyDown={(e) => {
            if (e.key === 'Enter' || e.key === 'Escape') {
              e.preventDefault();
              setEditing(false);
            }
          }}
        />
      </Show>
      <button type="button" class="text-error" aria-label={`${props.name}を外す`} onClick={() => props.onRemove()}>
        ×
      </button>
    </span>
  );
};
