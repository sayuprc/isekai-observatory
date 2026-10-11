/**
 * セッションおよび CSRF Cookie の有効期間(秒)
 *
 * 1日 - TODO: 調整する
 */
export const SESSION_TTL_SECONDS = 60 * 60 * 24;

/**
 * Astro 側で解決したクライアント実 IP を Elysia へ受け渡すための内部ヘッダー名
 *
 * 公開境界である Astro ルートで信頼できる値に上書きしてから渡すため、
 * クライアントが送ってきた同名ヘッダーは無視される
 */
export const CLIENT_IP_HEADER = 'x-client-ip';

/**
 * 認証系エンドポイントの IP 単位レート制限 (公開境界での多層防御の外側)
 */
export const AUTH_RATE_LIMITS = {
  loginStart: { limit: 20, windowSeconds: 60 },
  loginFinish: { limit: 20, windowSeconds: 60 },
  registerStart: { limit: 10, windowSeconds: 60 },
  registerFinish: { limit: 10, windowSeconds: 60 },
  recoveryStart: { limit: 10, windowSeconds: 60 },
  recoveryFinish: { limit: 10, windowSeconds: 60 },
} as const;
