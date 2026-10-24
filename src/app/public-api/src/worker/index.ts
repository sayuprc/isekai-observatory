import { isResource } from '../shared/layout.ts';
import { listPage } from './list.ts';
import { findPositions } from './search.ts';

interface Env {
  ASSETS: { fetch: (request: Request | string) => Promise<Response> };
  /** 'true' のとき検索エンジンに載せない。dev / staging の deploy だけが渡す */
  NOINDEX?: string;
}

const CORS_HEADERS = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Methods': 'GET, OPTIONS',
};

const json = (body: unknown, status: number): Response =>
  new Response(JSON.stringify(body), {
    status,
    headers: { ...CORS_HEADERS, 'Content-Type': 'application/json' },
  });

const respond = async (request: Request, env: Env): Promise<Response> => {
  if (request.method === 'OPTIONS') {
    return new Response(null, { status: 204, headers: CORS_HEADERS });
  }

  const url = new URL(request.url);
  const resource = /^\/v1\/([a-z-]+)\/?$/.exec(url.pathname)?.[1];

  if (resource === undefined || !isResource(resource)) {
    return json({ message: '対象が見つかりません。' }, 404);
  }

  if (request.method !== 'GET') {
    return new Response(null, { status: 405, headers: { ...CORS_HEADERS, Allow: 'GET, OPTIONS' } });
  }

  const readAsset = async (path: string): Promise<Response> => {
    const response = await env.ASSETS.fetch(new URL(path, url.origin).toString());

    if (!response.ok) {
      throw new Error(`Snapshot file is missing: ${path}`);
    }

    return response;
  };
  const result = await listPage(
    resource,
    url.searchParams,
    async (path) => (await readAsset(path)).json(),
    async () => findPositions(resource, url.searchParams, async (path) => (await readAsset(path)).text()),
  );

  return result.ok ? json(result.body, 200) : json({ message: result.message }, 400);
};

export const handle = async (request: Request, env: Env): Promise<Response> => {
  const response = await respond(request, env);

  if (env.NOINDEX === 'true') {
    response.headers.set('X-Robots-Tag', 'noindex, nofollow');
  }

  return response;
};

export default { fetch: handle };
