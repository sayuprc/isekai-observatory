import { describe, expect, it } from 'bun:test';
import { isTrustedProxyRequest, PROXY_SHARED_SECRET_HEADER } from './proxy-secret';

describe('isTrustedProxyRequest', () => {
  it('secret が未設定ならヘッダなしでも true を返す', () => {
    expect(isTrustedProxyRequest(undefined, new Headers())).toBe(true);
  });

  it('secret が空文字ならヘッダなしでも true を返す', () => {
    expect(isTrustedProxyRequest('', new Headers())).toBe(true);
  });

  it('ヘッダが一致すれば true を返す', () => {
    const headers = new Headers({ [PROXY_SHARED_SECRET_HEADER]: 'proxy-secret-value' });

    expect(isTrustedProxyRequest('proxy-secret-value', headers)).toBe(true);
  });

  it('ヘッダが不一致なら false を返す', () => {
    const headers = new Headers({ [PROXY_SHARED_SECRET_HEADER]: 'wrong-value' });

    expect(isTrustedProxyRequest('proxy-secret-value', headers)).toBe(false);
  });

  it('ヘッダがなければ false を返す', () => {
    expect(isTrustedProxyRequest('proxy-secret-value', new Headers())).toBe(false);
  });

  it('長さの異なる値でも例外を投げず false を返す', () => {
    const headers = new Headers({ [PROXY_SHARED_SECRET_HEADER]: 'short' });

    expect(isTrustedProxyRequest('a-much-longer-proxy-secret-value', headers)).toBe(false);
  });
});
