import { Show, createSignal } from 'solid-js';

interface CoVocalistChipProps {
  name: string;
  creditName: string;
  onCreditNameChange: (creditName: string) => void;
  onRemove: () => void;
}

// 共演者 1 人分のチップ. 名前をクリックするとクレジット名を編集できる
export const CoVocalistChip = (props: CoVocalistChipProps) => {
  const [editing, setEditing] = createSignal(false);

  return (
    <span class="badge badge-outline gap-1 whitespace-nowrap">
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
