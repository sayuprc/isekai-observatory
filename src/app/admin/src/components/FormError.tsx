import { Show } from 'solid-js';

interface Props {
  message: string | null;
  onClose?: () => void;
}

export const FormError = (props: Props) => (
  <Show when={props.message}>
    <div role="alert" class="alert alert-error mb-4 max-w-lg items-start gap-3 rounded-lg shadow-sm">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-5 w-5 shrink-0"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
        />
      </svg>
      <span class="grow leading-relaxed">{props.message}</span>
      <Show when={props.onClose}>
        <button type="button" class="btn btn-ghost btn-xs btn-circle" onClick={props.onClose} aria-label="閉じる">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-4 w-4"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
          </svg>
        </button>
      </Show>
    </div>
  </Show>
);
