import { describe, expect, it } from 'bun:test';
import { decodeJwtExpMs } from './google-id-token';

const createJwt = (payload: unknown): string => {
  const encode = (value: unknown): string => Buffer.from(JSON.stringify(value)).toString('base64url');

  return `${encode({ alg: 'RS256' })}.${encode(payload)}.signature`;
};

describe('decodeJwtExpMs', () => {
  it('exp をミリ秒で返す', () => {
    expect(decodeJwtExpMs(createJwt({ exp: 1_800_000_000 }))).toBe(1_800_000_000_000);
  });

  it('exp がなければ null を返す', () => {
    expect(decodeJwtExpMs(createJwt({ iat: 1_800_000_000 }))).toBeNull();
  });

  it('exp が数値でなければ null を返す', () => {
    expect(decodeJwtExpMs(createJwt({ exp: 'soon' }))).toBeNull();
  });

  it('JWT 形式でなければ null を返す', () => {
    expect(decodeJwtExpMs('not-a-jwt')).toBeNull();
  });

  it('payload が JSON でなければ null を返す', () => {
    expect(decodeJwtExpMs('header.%%%%.signature')).toBeNull();
  });
});
