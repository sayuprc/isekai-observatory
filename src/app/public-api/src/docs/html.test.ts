import { describe, expect, it } from 'bun:test';
import { renderIndexHtml } from './html.ts';

const configOf = (html: string): unknown => {
  const json = /Scalar\.createApiReference\('#app', (.+)\);/.exec(html)?.[1];

  return json === undefined ? undefined : JSON.parse(json);
};

describe('renderIndexHtml', () => {
  it('Public API の URL に /v1 を付けて server にし、telemetry を切る', () => {
    expect(configOf(renderIndexHtml('https://api.example.com'))).toEqual({
      url: '/openapi.yaml',
      servers: [{ url: 'https://api.example.com/v1' }],
      telemetry: false,
    });
  });

  it('URL 末尾のスラッシュは重ねない', () => {
    expect(configOf(renderIndexHtml('https://api.example.com/'))).toMatchObject({
      servers: [{ url: 'https://api.example.com/v1' }],
    });
  });

  it('設定に < が含まれても script 要素を閉じない', () => {
    const html = renderIndexHtml('https://api.example.com/</script>');

    expect(html.match(/<\/script>/g)).toHaveLength(2);
  });
});
