import type { APIContext, MiddlewareHandler, MiddlewareNext } from 'astro';
import { PROXY_SHARED_SECRET } from 'astro:env/server';
import { defineMiddleware, sequence } from 'astro:middleware';
import { createRequestLogger } from './server/logger';
import { isTrustedProxyRequest } from './server/proxy-secret';
import { getSessionCredential } from './server/session';
import { createLoginRedirectPath, isAuthRequiredPath } from './utils/auth-redirect';

type AdminMiddlewareProps = Record<string, unknown>;
type AdminMiddlewareParams = Record<string, string | undefined>;
type AdminMiddlewareContext = APIContext<AdminMiddlewareProps, AdminMiddlewareParams>;
type AstroCookies = AdminMiddlewareContext['cookies'];

const AUTH_COOKIE_NAMES = ['session', 'csrf'] as const satisfies readonly string[];

const clearAuthCookies = (cookies: AstroCookies): void => {
  for (const cookieName of AUTH_COOKIE_NAMES) {
    cookies.delete(cookieName, { path: '/' });
  }
};

const requestLogger: MiddlewareHandler = async (
  context: AdminMiddlewareContext,
  next: MiddlewareNext,
): Promise<Response> => {
  const startedAt = performance.now();
  const log = createRequestLogger(context.request.headers);
  const { method } = context.request;
  const { pathname } = context.url;

  try {
    const response = await next();

    log.info(
      { method, path: pathname, status: response.status, durationMs: Math.round(performance.now() - startedAt) },
      'request',
    );

    return response;
  } catch (error) {
    // ページレンダリング等で middleware まで漏れた例外を構造化して記録し、再送出する
    log.error(
      { method, path: pathname, durationMs: Math.round(performance.now() - startedAt), err: error },
      'unhandled request error',
    );

    throw error;
  }
};

const requireProxySecret: MiddlewareHandler = async (
  context: AdminMiddlewareContext,
  next: MiddlewareNext,
): Promise<Response> => {
  if (!isTrustedProxyRequest(PROXY_SHARED_SECRET, context.request.headers)) {
    return new Response('Forbidden', { status: 403 });
  }

  return next();
};

const requireAuth: MiddlewareHandler = async (
  context: AdminMiddlewareContext,
  next: MiddlewareNext,
): Promise<Response> => {
  const { pathname, search } = context.url;

  if (!isAuthRequiredPath(pathname)) {
    return next();
  }

  const sessionId = context.cookies.get('session')?.value;
  const credential = sessionId ? await getSessionCredential(sessionId) : null;

  if (credential) {
    return next();
  }

  clearAuthCookies(context.cookies);

  return context.redirect(createLoginRedirectPath(`${pathname}${search}`));
};

export const onRequest: MiddlewareHandler = sequence(
  defineMiddleware(requestLogger),
  defineMiddleware(requireProxySecret),
  defineMiddleware(requireAuth),
);
