import { describe, expect, it } from 'bun:test';
import { isEnterKey } from './keyboard';

describe('Enter キーの判定', () => {
  it('Enter を操作として扱う', () => {
    expect(isEnterKey({ key: 'Enter', isComposing: false })).toBe(true);
  });

  it('IME の変換確定の Enter は操作として扱わない', () => {
    expect(isEnterKey({ key: 'Enter', isComposing: true })).toBe(false);
  });

  it('Enter 以外のキーは操作として扱わない', () => {
    expect(isEnterKey({ key: 'a', isComposing: false })).toBe(false);
  });
});
