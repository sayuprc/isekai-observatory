import { CLIENT_IP_HEADER } from './constants';
import { ApiError } from './errors';
import { redis } from './redis';

type RateLimitConfig = {
  limit: number;
  windowSeconds: number;
};

const createRateLimitKey = (scope: string, clientIp: string): string => {
  return `rate-limit:auth:${scope}:${clientIp}`;
};

const resolveClientIp = (request: Request): string => {
  return request.headers.get(CLIENT_IP_HEADER) ?? 'unknown';
};

/**
 * クライアント実 IP 単位の固定ウィンドウレート制限
 *
 * 上限を超えた場合は 429 を投げる。複数インスタンス構成でもカウンタを共有する
 * 初回リクエストで必ず TTL 付きでキーを作成するため、INCR と EXPIRE の間で
 * プロセスが落ちてカウンタが永続化する事故を防ぐ
 */
export const enforceAuthRateLimit = async (request: Request, scope: string, config: RateLimitConfig): Promise<void> => {
  const key = createRateLimitKey(scope, resolveClientIp(request));

  const created = await redis.set(key, 1, { nx: true, ex: config.windowSeconds });

  let count = 1;

  if (created !== 'OK') {
    count = await redis.incr(key);

    // SET と INCR の間でキーが失効していた場合の TTL 補填
    if (count === 1) {
      await redis.expire(key, config.windowSeconds);
    }
  }

  if (count > config.limit) {
    throw new ApiError(429, {});
  }
};
