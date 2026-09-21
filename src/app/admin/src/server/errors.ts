export class ApiError extends Error {
  constructor(
    public readonly status: number,
    public readonly body: unknown,
  ) {
    super(`API Error: ${status}`);
  }
}

export const resolveApiResponse = <T>(result: { data?: T; error?: unknown; response: Response }): T => {
  if (result.response.ok) {
    return result.data as T;
  }

  throw new ApiError(result.response.status, result.error ?? {});
};
