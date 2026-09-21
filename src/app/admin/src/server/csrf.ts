import { createHash, timingSafeEqual } from 'node:crypto';

/**
 * CSRF トークンを定数時間で比較する
 *
 * 単純な `===` / `!==` は一致した先頭文字数に比例して処理時間が変わり、
 * トークンを 1 文字ずつ推測されうる。長さの差による早期 return (長さオラクル) も
 * 避けるため、固定長の SHA-256 ハッシュへ変換してから timingSafeEqual で比較する
 */
export const isCsrfTokenMatch = (expected: string, actual: string): boolean => {
  const hash = (value: string): Buffer => createHash('sha256').update(value).digest();

  return timingSafeEqual(hash(expected), hash(actual));
};
