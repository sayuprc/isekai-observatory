interface Env {
  ORIGIN_URL: string;
  PROXY_SHARED_SECRET: string;
}

export default {
  async fetch(request: Request, env: Env): Promise<Response> {
    const origin = new URL(env.ORIGIN_URL);
    const url = new URL(request.url);
    url.protocol = origin.protocol;
    url.hostname = origin.hostname;
    url.port = origin.port;

    const proxied = new Request(url, request);
    proxied.headers.set('X-Proxy-Shared-Secret', env.PROXY_SHARED_SECRET);
    proxied.headers.set('X-Forwarded-Host', new URL(request.url).hostname);

    // origin の redirect をそのまま client へ返すため follow しない
    return fetch(proxied, { redirect: 'manual' });
  },
};
