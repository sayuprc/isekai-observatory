import { describe, expect, it } from 'bun:test';
import { isCsrfTokenMatch } from './csrf';

describe('isCsrfTokenMatch', () => {
  it('一致するトークンで true を返す', () => {
    expect(isCsrfTokenMatch('csrf-token-value', 'csrf-token-value')).toBe(true);
  });

  it('同じ長さの異なるトークンで false を返す', () => {
    expect(isCsrfTokenMatch('csrf-token-aaaa', 'csrf-token-bbbb')).toBe(false);
  });

  it('長さの異なるトークンでも例外を投げず false を返す', () => {
    expect(isCsrfTokenMatch('short', 'a-much-longer-csrf-token-value')).toBe(false);
  });

  it('空文字と非空文字で false を返す', () => {
    expect(isCsrfTokenMatch('', 'non-empty')).toBe(false);
  });
});
