import { createSignal } from 'solid-js';

type ErrorDetail = {
  field: string;
  message: string;
};

type ErrorResponseBody = {
  code: string;
  message: string;
  details?: ErrorDetail[];
};

type EdenError = {
  status: number;
  value: unknown;
};

const isEdenError = (error: unknown): error is EdenError =>
  typeof error === 'object' && error !== null && 'status' in error && 'value' in error;

const extractErrorBody = (error: unknown): unknown => (isEdenError(error) ? error.value : error);

const isErrorResponse = (body: unknown): body is ErrorResponseBody =>
  typeof body === 'object'
  && body !== null
  && 'code' in body
  && 'message' in body
  && typeof (body as ErrorResponseBody).message === 'string';

export const createFormErrors = () => {
  const [formError, setFormError] = createSignal<string | null>(null);
  const [fieldErrors, setFieldErrors] = createSignal<ErrorDetail[]>([]);

  // ネストした field はパス形式 (例: media/0/tracks/1/trackNo) で届くため、
  // 完全一致に加えて配下のエラーも prefix 一致で拾い、パス付きで表示する
  const getFieldError = (field: string): string | undefined => {
    const matches = fieldErrors().filter(e => e.field === field || e.field.startsWith(`${field}/`));

    if (matches.length === 0) {
      return undefined;
    }

    return matches
      .map(e => (e.field === field ? e.message : `${e.field.slice(field.length + 1)}: ${e.message}`))
      .join('\n');
  };

  const clearErrors = () => {
    setFormError(null);
    setFieldErrors([]);
  };

  const handleError = (status: number, error: unknown) => {
    clearErrors();

    if (status === 401) {
      window.location.href = '/auth/login';
      return;
    }

    const body = extractErrorBody(error);

    if (!isErrorResponse(body)) {
      setFormError('予期しないエラーが発生しました');
      return;
    }

    if (status === 422 && body.details !== undefined) {
      setFieldErrors(body.details);
      return;
    }

    setFormError(body.message);
  };

  return { formError, setFormError, fieldErrors, getFieldError, clearErrors, handleError };
};
