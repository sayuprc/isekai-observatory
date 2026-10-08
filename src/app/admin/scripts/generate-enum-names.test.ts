import { describe, expect, test } from 'bun:test';
import { renderEnumNames, toConstantName } from './generate-enum-names';

describe('toConstantName', () => {
  test('末尾の Value を除いた大文字のスネークケースに _NAMES を付ける', () => {
    expect(toConstantName('SongTypeValue')).toBe('SONG_TYPE_NAMES');
    expect(toConstantName('SongPersonRole')).toBe('SONG_PERSON_ROLE_NAMES');
  });
});

describe('renderEnumNames', () => {
  test('enum の値と x-enum-descriptions を対応表にする', () => {
    const output = renderEnumNames({
      SongTypeValue: { 'enum': [1, 2], 'x-enum-descriptions': ['オリジナル曲', 'カバー曲'] },
      AuditAction: { 'enum': ['create'], 'x-enum-descriptions': ['作成'] },
    });

    expect(output).toContain("import type { AuditAction, SongTypeValue } from './types.gen';");
    expect(output).toContain(
      'export const SONG_TYPE_NAMES: Record<SongTypeValue, string> = {\n  "1": "オリジナル曲",\n  "2": "カバー曲",\n};',
    );
    expect(output).toContain(
      'export const AUDIT_ACTION_NAMES: Record<AuditAction, string> = {\n  "create": "作成",\n};',
    );
  });

  test('x-enum-descriptions を持たないスキーマと、ドットを含む名前のスキーマは対象にしない', () => {
    const output = renderEnumNames({
      'Plain': { enum: [1] },
      'IsekaiObservatory.Admin.Version': { 'enum': ['v1'], 'x-enum-descriptions': ['バージョン 1'] },
    });

    expect(output).not.toContain('PLAIN_NAMES');
    expect(output).not.toContain('VERSION_NAMES');
  });

  test('enum と x-enum-descriptions の件数が違うときは失敗する', () => {
    expect(() =>
      renderEnumNames({ SongTypeValue: { 'enum': [1, 2], 'x-enum-descriptions': ['オリジナル曲'] } }),
    ).toThrow();
  });
});
