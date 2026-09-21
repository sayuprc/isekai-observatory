import { createEffect, createSignal, onCleanup, onMount, Show } from 'solid-js';
import { resolveAuthReturnTo } from '../../utils/auth-redirect';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { authenticatePasskey, isPasskeyConditionalMediationAvailable, passkeyErrorMessage } from '../../utils/webauthn';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';

type LoginFormProps = {
  returnTo?: string;
};

export const LoginForm = (props: LoginFormProps) => {
  const { formError, setFormError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();
  const [email, setEmail] = createSignal('');
  let conditionalAbortController: AbortController | undefined;
  let emailInput: HTMLInputElement | undefined;

  const redirectToReturnPath = () => {
    window.location.href = resolveAuthReturnTo(props.returnTo);
  };

  const abortConditionalLogin = () => {
    conditionalAbortController?.abort();
    conditionalAbortController = undefined;
  };

  const loginWithPasskey = async (
    loginEmail: string,
    options: { signal?: AbortSignal; mediation?: CredentialMediationRequirement; suppressStartError?: boolean } = {},
  ) => {
    const start = await client.api.auth.login.start.post({
      email: loginEmail,
    });

    if (start.error) {
      if (options.suppressStartError) {
        return;
      }

      if (start.status === 400 || start.status === 401) {
        setFormError('パスキー認証に失敗しました');
        return;
      }

      handleError(start.status, start.error);
      return;
    }

    try {
      const credential = await authenticatePasskey(start.data.publicKey, {
        mediation: options.mediation,
        signal: options.signal,
      });
      const finish = await client.api.auth.login.finish.post({
        authCeremonyId: start.data.authCeremonyId,
        credential,
      });

      if (finish.error) {
        if (finish.status === 400 || finish.status === 401) {
          setFormError('パスキー認証に失敗しました');
          return;
        }

        handleError(finish.status, finish.error);
        return;
      }

      setFlash('ログインしました');
      redirectToReturnPath();
    } catch (error) {
      if (options.signal?.aborted) {
        return;
      }

      setFormError(passkeyErrorMessage(error, 'パスキー認証に失敗しました'));
    }
  };

  const startConditionalLogin = async (loginEmail: string, abortController: AbortController) => {
    if (abortController.signal.aborted || !(await isPasskeyConditionalMediationAvailable())) {
      return;
    }

    if (abortController.signal.aborted) {
      return;
    }

    abortConditionalLogin();
    conditionalAbortController = abortController;

    try {
      await loginWithPasskey(loginEmail, {
        mediation: 'conditional',
        signal: abortController.signal,
        suppressStartError: true,
      });
    } finally {
      if (conditionalAbortController === abortController) {
        conditionalAbortController = undefined;
      }
    }
  };

  onMount(() => {
    setEmail(emailInput?.value ?? '');
  });

  createEffect(() => {
    const loginEmail = email().trim();

    if (!loginEmail.includes('@') || isSubmitting()) {
      abortConditionalLogin();
      return;
    }

    const abortController = new AbortController();
    const timeoutId = window.setTimeout(() => {
      void startConditionalLogin(loginEmail, abortController);
    }, 300);

    onCleanup(() => {
      window.clearTimeout(timeoutId);
      abortController.abort();
      if (conditionalAbortController === abortController) {
        conditionalAbortController = undefined;
      }
    });
  });

  const handleSubmit = withSubmitting(async (e: Event) => {
    e.preventDefault();
    clearErrors();
    abortConditionalLogin();

    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);
    await loginWithPasskey(formData.get('email')?.toString() ?? '');
  });

  return (
    <form onsubmit={handleSubmit}>
      <FormError message={formError()} onClose={clearErrors} />
      <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs border p-4">
        <label class="label">メールアドレス</label>
        <input
          ref={el => emailInput = el}
          type="email"
          class="input"
          name="email"
          autocomplete="username webauthn"
          required
          onInput={event => setEmail(event.currentTarget.value)}
          classList={{ 'input-error': !!getFieldError('email') }}
        />
        <Show when={getFieldError('email')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

        <button class="btn btn-primary mt-4" disabled={isSubmitting()}>
          {isSubmitting() ? 'ログイン中...' : 'ログイン'}
        </button>
      </fieldset>
    </form>
  );
};
