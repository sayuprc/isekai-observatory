// Viewer の API クライアントは `${API_URL}/v1` を叩くが、Prism は OAS の paths (`/songs` など) をそのまま公開する
// その差を吸収するため、`/v1` を外して Prism へ転送するだけのプロキシ
const listenPort = Number(process.env.PROXY_PORT ?? 4011);
const upstream = process.env.UPSTREAM ?? '127.0.0.1:4010';

Bun.serve({
  hostname: '127.0.0.1',
  port: listenPort,
  fetch(request) {
    const url = new URL(request.url);
    url.host = upstream;
    url.pathname = url.pathname.replace(/^\/v1(?=\/|$)/, '') || '/';

    return fetch(new Request(url, request));
  },
});
