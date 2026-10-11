import { describe, expect, it } from 'bun:test';
import { passkeyErrorMessage } from './webauthn';

describe('passkeyErrorMessage', () => {
  it('DOMException.name をフォーム共通の日本語メッセージへ変換する', () => {
    expect(passkeyErrorMessage(new DOMException('', 'NotAllowedError'), '失敗しました')).toBe(
      'パスキー操作がキャンセルされたか、許可されませんでした',
    );
    expect(passkeyErrorMessage(new DOMException('', 'SecurityError'), '失敗しました')).toBe(
      'この環境ではパスキーを利用できません',
    );
  });

  it('未定義の例外名は fallback を返す', () => {
    expect(passkeyErrorMessage(new DOMException('', 'UnknownError'), '失敗しました')).toBe('失敗しました');
  });
});
