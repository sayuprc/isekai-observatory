import { Show } from 'solid-js';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { passkeyErrorMessage, registerPasskey } from '../../utils/webauthn';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';

export const RecoveryForm = () => {
  const { formError, setFormError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  const handleSubmit = withSubmitting(async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);

    const start = await client.api.auth.recovery.start.post({
      email: formData.get('email')?.toString() ?? '',
      recoveryCode: formData.get('recoveryCode')?.toString() ?? '',
      name: formData.get('name')?.toString() ?? '',
    });

    if (start.error) {
      if (start.status === 400) {
        setFormError('リカバリーに失敗しました。入力内容を確認してください。');
        return;
      }

      handleError(start.status, start.error);
      return;
    }

    try {
      const credential = await registerPasskey(start.data.publicKey);
      const finish = await client.api.auth.recovery.finish.post({
        authCeremonyId: start.data.authCeremonyId,
        credential,
      });

      if (finish.error) {
        if (finish.status === 400 || finish.status === 401) {
          setFormError('リカバリーに失敗しました。入力内容を確認してください。');
          return;
        }

        handleError(finish.status, finish.error);
        return;
      }

      setFlash('リカバリーが完了しました');
      window.location.href = '/song-types';
    } catch (error) {
      setFormError(passkeyErrorMessage(error, 'パスキー登録に失敗しました'));
    }
  });

  return (
    <form onsubmit={handleSubmit}>
      <FormError message={formError()} onClose={clearErrors} />
      <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs border p-4">
        <label class="label">メールアドレス</label>
        <input
          type="email"
          class="input"
          name="email"
          required
          classList={{ 'input-error': !!getFieldError('email') }}
        />
        <Show when={getFieldError('email')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

        <label class="label">リカバリーコード</label>
        <input
          type="text"
          class="input"
          name="recoveryCode"
          required
          classList={{ 'input-error': !!getFieldError('recoveryCode') }}
        />
        <Show when={getFieldError('recoveryCode')}>
          {message => <p class="mt-1 text-xs text-error">{message()}</p>}
        </Show>

        <label class="label">新しいパスキー名</label>
        <input type="text" class="input" name="name" required classList={{ 'input-error': !!getFieldError('name') }} />
        <Show when={getFieldError('name')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

        <button class="btn btn-primary mt-4" disabled={isSubmitting()}>
          {isSubmitting() ? 'リカバリー中...' : 'リカバリー'}
        </button>
      </fieldset>
    </form>
  );
};
