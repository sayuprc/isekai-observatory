import { describe, expect, it } from 'bun:test';
import {
  createLoginRedirectPath,
  DEFAULT_AUTH_RETURN_TO,
  isAuthRequiredPath,
  resolveAuthReturnTo,
} from './auth-redirect';

describe('resolveAuthReturnTo', () => {
  it('return_to がない場合は既定の遷移先を返す', () => {
    expect(resolveAuthReturnTo(null)).toBe(DEFAULT_AUTH_RETURN_TO);
    expect(resolveAuthReturnTo('')).toBe(DEFAULT_AUTH_RETURN_TO);
  });

  it('同一 origin のパスを返す', () => {
    expect(resolveAuthReturnTo('/songs?sort=title')).toBe('/songs?sort=title');
  });

  it('外部 URL と protocol-relative URL は既定の遷移先へ丸める', () => {
    expect(resolveAuthReturnTo('https://example.com/songs')).toBe(DEFAULT_AUTH_RETURN_TO);
    expect(resolveAuthReturnTo('//example.com/songs')).toBe(DEFAULT_AUTH_RETURN_TO);
  });
});

describe('isAuthRequiredPath', () => {
  it('管理画面のページは認証必須にする', () => {
    expect(isAuthRequiredPath('/')).toBe(true);
    expect(isAuthRequiredPath('/songs')).toBe(true);
    expect(isAuthRequiredPath('/songs/1')).toBe(true);
    expect(isAuthRequiredPath('/exports/songs.json')).toBe(true);
  });

  it('API、認証ページ、静的アセットは認証対象から外す', () => {
    expect(isAuthRequiredPath('/api')).toBe(false);
    expect(isAuthRequiredPath('/api/songs')).toBe(false);
    expect(isAuthRequiredPath('/auth/login')).toBe(false);
    expect(isAuthRequiredPath('/auth/recovery')).toBe(false);
    expect(isAuthRequiredPath('/_astro/app.js')).toBe(false);
    expect(isAuthRequiredPath('/favicon.svg')).toBe(false);
  });
});

describe('createLoginRedirectPath', () => {
  it('現在のパスとクエリを return_to に入れたログイン URL を返す', () => {
    const redirectUrl = new URL(createLoginRedirectPath('/songs?keyword=a b'), 'https://admin.example.test');

    expect(redirectUrl.pathname).toBe('/auth/login');
    expect(redirectUrl.searchParams.get('return_to')).toBe('/songs?keyword=a b');
  });
});
