const METADATA_IDENTITY_URL = 'http://metadata.google.internal/computeMetadata/v1/instance/service-accounts/default/identity';

/**
 * 期限の 5 分前から新しい token を取り直す
 * metadata server の token 期限は約 1 時間
 */
const REFRESH_MARGIN_MS = 5 * 60 * 1000;

const FALLBACK_TTL_MS = 30 * 60 * 1000;

type CachedToken = {
  token: string;
  expiresAtMs: number;
};

const cache = new Map<string, CachedToken>();

/**
 * K_SERVICE (service) / CLOUD_RUN_JOB (job) は Cloud Run が設定する予約環境変数
 * viewer のビルドは viewer-deploy job 内で走る。ローカルビルドでは未設定なので無効化される
 */
const isRunningOnCloudRun = (): boolean =>
  process.env.K_SERVICE !== undefined || process.env.CLOUD_RUN_JOB !== undefined;

const decodeJwtExpMs = (token: string): number | null => {
  const payload = token.split('.')[1];

  if (payload === undefined) {
    return null;
  }

  try {
    const decoded: unknown = JSON.parse(Buffer.from(payload, 'base64url').toString('utf-8'));

    if (typeof decoded !== 'object' || decoded === null || !('exp' in decoded) || typeof decoded.exp !== 'number') {
      return null;
    }

    return decoded.exp * 1000;
  } catch {
    return null;
  }
};

/**
 * 非公開 API (allow_unauthenticated = false) を呼ぶための Google ID token を
 * Cloud Run の metadata server から取得する。audience ごとに期限までキャッシュする
 * Cloud Run 外 (ローカルビルドなど) では null を返し、呼び出し側は付与をスキップする
 */
export const getGoogleIdToken = async (audience: string): Promise<string | null> => {
  if (!isRunningOnCloudRun()) {
    return null;
  }

  const cached = cache.get(audience);

  if (cached && Date.now() < cached.expiresAtMs - REFRESH_MARGIN_MS) {
    return cached.token;
  }

  const response = await fetch(`${METADATA_IDENTITY_URL}?audience=${encodeURIComponent(audience)}`, {
    headers: { 'Metadata-Flavor': 'Google' },
  });

  if (!response.ok) {
    throw new Error(`Failed to fetch Google ID token from metadata server: ${response.status}`);
  }

  const token = await response.text();

  cache.set(audience, {
    token,
    expiresAtMs: decodeJwtExpMs(token) ?? Date.now() + FALLBACK_TTL_MS,
  });

  return token;
};
