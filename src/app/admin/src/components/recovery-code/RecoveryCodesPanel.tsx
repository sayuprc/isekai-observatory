import { createSignal, For, Show } from 'solid-js';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';

export const RecoveryCodesPanel = () => {
  const { formError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();
  const [codes, setCodes] = createSignal<string[]>([]);

  const generate = withSubmitting(async () => {
    clearErrors();

    const { data, error, status } = await client.api['recovery-codes'].post();

    if (data) {
      setCodes(data.recoveryCodes);
      setFlash('リカバリーコードを発行しました');
      return;
    }

    handleError(status, error);
  });

  const copyAll = async () => {
    await navigator.clipboard.writeText(codes().join('\n'));
    setFlash('コピーしました');
  };

  const download = () => {
    const blob = new Blob([codes().join('\n')], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const anchor = document.createElement('a');
    anchor.href = url;
    anchor.download = 'recovery-codes.txt';
    anchor.click();
    // click() 直後に revoke するとダウンロード開始前に URL が無効化され得るため、非同期で解放する
    setTimeout(() => URL.revokeObjectURL(url), 0);
  };

  return (
    <div class="max-w-2xl space-y-6">
      <FormError message={formError()} onClose={clearErrors} />

      <div class="rounded-box border border-base-300 bg-base-200 p-6">
        <p class="text-sm text-base-content/70">
          リカバリーコードはパスキーを紛失した際にアカウントへのアクセスを回復するためのワンタイムコードです。
          発行すると既存のコードは無効になります。コードは発行時に一度だけ表示され、再表示はできません。安全な場所に保管してください。
        </p>
        <button class="btn btn-primary mt-4" disabled={isSubmitting()} onClick={generate}>
          {isSubmitting() ? '発行中...' : 'リカバリーコードを発行/再生成'}
        </button>
      </div>

      <Show when={codes().length > 0}>
        <div class="rounded-box border border-base-300 bg-base-100 p-6">
          <p class="mb-4 text-sm font-semibold text-warning">
            このコードは再表示できません。今すぐコピーまたはダウンロードして保管してください。
          </p>
          <ul class="grid grid-cols-2 gap-2 font-mono text-sm">
            <For each={codes()}>{code => <li class="rounded bg-base-200 px-3 py-2">{code}</li>}</For>
          </ul>
          <div class="mt-4 flex gap-2">
            <button class="btn btn-sm" onClick={copyAll}>
              コピー
            </button>
            <button class="btn btn-sm" onClick={download}>
              ダウンロード
            </button>
          </div>
        </div>
      </Show>
    </div>
  );
};
