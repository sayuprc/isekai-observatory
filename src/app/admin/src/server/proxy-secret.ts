import { createHash, timingSafeEqual } from 'node:crypto';

export const PROXY_SHARED_SECRET_HEADER = 'X-Proxy-Shared-Secret';

/**
 * リクエストが proxy Worker 経由であることを共有シークレットヘッダで検証する
 *
 * Cloudflare 側の IP 制限は proxy Worker のドメインにしか効かないため、
 * このヘッダ検証がないと Cloud Run の run.app URL への直アクセスで IP 制限を迂回できてしまう
 *
 * secret が未設定の環境(ローカル開発など)では検証せずすべて許可する
 * 比較は csrf.ts と同じ理由で、固定長ハッシュへ変換してから定数時間で行う
 */
export const isTrustedProxyRequest = (secret: string | undefined, headers: Headers): boolean => {
  if (secret === undefined || secret === '') {
    return true;
  }

  const actual = headers.get(PROXY_SHARED_SECRET_HEADER);

  if (actual === null) {
    return false;
  }

  const hash = (value: string): Buffer => createHash('sha256').update(value).digest();

  return timingSafeEqual(hash(secret), hash(actual));
};
