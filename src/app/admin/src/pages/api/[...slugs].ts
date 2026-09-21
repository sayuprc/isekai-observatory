import type { APIContext } from 'astro';
import { app } from '../../server';
import { CLIENT_IP_HEADER } from '../../server/constants';

const resolveClientAddress = (context: APIContext): string => {
  // Astro アダプタが信頼できるプロキシ設定に基づいて解決した実 IP のみを採用する
  // 解決できない場合にクライアント送信の x-forwarded-for を信用すると、攻撃者が
  // キーを自由に分散させてレート制限を無効化できるため、固定値に倒す
  try {
    return context.clientAddress;
  } catch {
    return 'unknown';
  }
};

const handle = (context: APIContext): Response | Promise<Response> => {
  // クライアントが詐称した同名ヘッダーを信頼できる実 IP で上書きしてから Elysia へ渡す
  const headers = new Headers(context.request.headers);
  headers.set(CLIENT_IP_HEADER, resolveClientAddress(context));

  return app.handle(new Request(context.request, { headers }));
};

export const ALL = handle;
