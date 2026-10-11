import type { JSX } from 'solid-js';
import { createSignal, onMount } from 'solid-js';

type FlashType = 'success' | 'error' | 'warning';

type FlashPayload = {
  message: string;
  type: FlashType;
};

const FLASH_KEY = 'flash';

const parseFlash = (raw: string | null): FlashPayload | null => {
  if (!raw) {
    return null;
  }

  try {
    const parsed = JSON.parse(raw) as Partial<FlashPayload>;

    if (typeof parsed.message !== 'string' || parsed.message.length === 0) {
      return null;
    }

    if (parsed.type !== 'success' && parsed.type !== 'error' && parsed.type !== 'warning') {
      return { message: parsed.message, type: 'success' };
    }

    return { message: parsed.message, type: parsed.type };
  } catch {
    return { message: raw, type: 'success' };
  }
};

export const setFlash = (message: string, type: FlashType = 'success') => {
  sessionStorage.setItem(FLASH_KEY, JSON.stringify({ message, type } satisfies FlashPayload));
};

const FLASH_STYLE: Record<FlashType, { alertClass: string; icon: () => JSX.Element }> = {
  success: {
    alertClass: 'alert-success',
    icon: () => (
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
          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
        />
      </svg>
    ),
  },
  error: {
    alertClass: 'alert-error',
    icon: () => (
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
          d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0Zm-9 6.75h.008v.008H12v-.008Z"
        />
      </svg>
    ),
  },
  warning: {
    alertClass: 'alert-warning',
    icon: () => (
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
          d="M12 9v3.75m0 3.75h.008v.008H12v-.008Zm8.25-3.75a8.25 8.25 0 11-16.5 0 8.25 8.25 0 0116.5 0Z"
        />
      </svg>
    ),
  },
};

export const FlashMessage = () => {
  const [flash] = createSignal(parseFlash(sessionStorage.getItem(FLASH_KEY)));
  const [visible, setVisible] = createSignal(false);

  const closeFlash = () => {
    setVisible(false);
  };

  onMount(() => {
    if (flash()) {
      sessionStorage.removeItem(FLASH_KEY);
      setVisible(true);
    }
  });

  return (
    <>
      {visible() && (
        <div
          role="alert"
          class={`alert mb-4 items-start gap-3 rounded-lg shadow-sm ${FLASH_STYLE[flash()!.type].alertClass}`}
        >
          {FLASH_STYLE[flash()!.type].icon()}
          <span class="grow leading-relaxed">{flash()!.message}</span>
          <button type="button" class="btn btn-ghost btn-xs btn-circle" onClick={closeFlash} aria-label="閉じる">
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
        </div>
      )}
    </>
  );
};
