/** Scalar の standalone ビルドを置く path。CDN に頼らず Worker から配る */
export const SCALAR_SCRIPT_PATH = '/scalar.js';

export const OPENAPI_PATH = '/openapi.yaml';

/**
 * API リファレンスの HTML を組み立てる
 * 契約の server はローカルの Laravel を指すため、利用者が叩く Public API の URL で上書きする
 */
export const renderIndexHtml = (publicApiUrl: string): string => {
  const config = {
    url: OPENAPI_PATH,
    servers: [{ url: `${publicApiUrl.replace(/\/+$/, '')}/v1` }],
    telemetry: false,
  };

  return `<!doctype html>
<html lang="ja">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ヰ世界観測所 API</title>
  </head>
  <body>
    <div id="app"></div>
    <script src="${SCALAR_SCRIPT_PATH}"></script>
    <script>
      Scalar.createApiReference('#app', ${JSON.stringify(config).replace(/</g, '\\u003c')});
    </script>
  </body>
</html>
`;
};
