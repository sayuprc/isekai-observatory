import { For, Show, createSignal } from 'solid-js';
import { extractColorsFromImage, normalizeHex, type ColorCandidate, type SwatchName } from '../../utils/extractColors';

const SWATCH_LABELS: Record<SwatchName, string> = {
  Vibrant: 'Vibrant',
  Muted: 'Muted',
  DarkVibrant: 'DarkVibrant',
  DarkMuted: 'DarkMuted',
  LightVibrant: 'LightVibrant',
  LightMuted: 'LightMuted',
};

interface ColorFieldProps {
  value: string;
  onChange: (color: string) => void;
  fieldError?: string;
}

/** 代表色入力。手入力に加え、ローカル画像からブラウザ内で候補を抽出する */
export const ColorField = (props: ColorFieldProps) => {
  const [candidates, setCandidates] = createSignal<ColorCandidate[]>([]);
  const [extracting, setExtracting] = createSignal(false);
  const [extractError, setExtractError] = createSignal<string | null>(null);

  const currentHex = () => normalizeHex(props.value) ?? '#989899';

  const setNormalizedColor = (raw: string) => {
    const normalized = normalizeHex(raw);
    props.onChange(normalized ?? raw.trim().toLowerCase());
  };

  const onFileChange = async (event: Event) => {
    const input = event.currentTarget as HTMLInputElement;
    const file = input.files?.[0];
    input.value = '';

    if (!file) {
      return;
    }

    setExtracting(true);
    setExtractError(null);

    try {
      const result = await extractColorsFromImage(file);
      setCandidates(result.candidates);
      props.onChange(result.defaultHex);
    } catch {
      setCandidates([]);
      setExtractError('色を抽出できませんでした。手入力してください。');
    } finally {
      setExtracting(false);
    }
  };

  return (
    <div class="md:col-span-2">
      <label class="label">代表色</label>
      <div class="flex flex-col gap-3">
        <div class="flex flex-wrap items-center gap-3">
          <input
            type="color"
            class="h-10 w-16 rounded-box border border-base-300"
            value={currentHex()}
            onInput={e => setNormalizedColor(e.currentTarget.value)}
            aria-label="代表色をカラーピッカーで選ぶ"
          />
          <input
            type="text"
            class="input w-40 font-mono"
            value={props.value}
            onInput={e => setNormalizedColor(e.currentTarget.value)}
            placeholder="#989899"
            required
            classList={{ 'input-error': !!props.fieldError }}
          />
        </div>

        <div class="flex flex-col gap-2">
          <label class="label py-0 text-xs text-base-content/70">
            画像から候補を抽出(端末内のみ。彩度×面積で並べ、初期選択も同じ基準)
          </label>
          <input
            type="file"
            accept="image/*"
            class="file-input file-input-bordered file-input-sm w-full max-w-md"
            onChange={onFileChange}
            disabled={extracting()}
            aria-label="代表色を抽出する画像を選ぶ"
          />
          <Show when={extracting()}>
            <p class="text-xs text-base-content/60">抽出中…</p>
          </Show>
          <Show when={extractError()}>
            {message => <p class="text-xs text-error">{message()}</p>}
          </Show>
        </div>

        <Show when={candidates().length > 0}>
          <div class="flex flex-wrap gap-2" role="listbox" aria-label="抽出した色の候補">
            <For each={candidates()}>
              {(candidate) => {
                const selected = () => normalizeHex(props.value) === candidate.hex;

                return (
                  <button
                    type="button"
                    role="option"
                    aria-selected={selected()}
                    class="flex items-center gap-2 rounded-box border px-2 py-1.5 text-xs transition-colors"
                    classList={{
                      'border-primary bg-primary/10': selected(),
                      'border-base-300 hover:border-base-content/40': !selected(),
                    }}
                    onClick={() => props.onChange(candidate.hex)}
                  >
                    <span
                      class="h-6 w-6 shrink-0 rounded-box border border-base-300"
                      style={{ 'background-color': candidate.hex }}
                      aria-hidden="true"
                    />
                    <span class="font-mono">{candidate.hex}</span>
                    <span class="text-base-content/60">{SWATCH_LABELS[candidate.name]}</span>
                  </button>
                );
              }}
            </For>
          </div>
        </Show>
      </div>
      <Show when={props.fieldError}>
        {message => <p class="mt-1 text-xs text-error">{message()}</p>}
      </Show>
    </div>
  );
};
