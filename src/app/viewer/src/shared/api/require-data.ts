type SdkResult<T> = {
  data?: T;
  error?: unknown;
  response?: Response;
};

/** 生成 SDK の結果から data を取り出す。取れなければ API 名と HTTP ステータス付きで build を止める */
export function requireData<T>(operation: string, result: SdkResult<T>): T {
  if (result.data === undefined) {
    throw new Error(`${operation} failed: HTTP ${result.response?.status} ${JSON.stringify(result.error)}`);
  }

  return result.data;
}
