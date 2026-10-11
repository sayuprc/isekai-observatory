export class ApiError extends Error {
  constructor(
    public readonly status: number,
    public readonly body: unknown,
  ) {
    super(`API Error: ${status}`);
  }
}

export type ApiResult<T> = { data?: T; error?: unknown; response?: Response };

export const resolveApiResponse = <T>(result: ApiResult<T>): T => {
  // 通信自体が失敗して response がない場合は、元の例外を想定外エラーとしてそのまま投げる
  if (!result.response) {
    throw result.error;
  }

  if (result.response.ok) {
    return result.data as T;
  }

  throw new ApiError(result.response.status, result.error ?? {});
};
