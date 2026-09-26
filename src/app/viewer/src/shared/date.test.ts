import { describe, expect, it } from 'bun:test';
import { dottedDate, dottedDateOfDateTime } from './date';

describe('dottedDate', () => {
  it('ハイフン区切りの日付をドット区切りにする', () => {
    expect(dottedDate('2024-12-31')).toBe('2024.12.31');
    expect(dottedDate('12-31')).toBe('12.31');
  });
});

describe('dottedDateOfDateTime', () => {
  it('日時を日本時間の日付にしてドット区切りにする', () => {
    expect(dottedDateOfDateTime('2024-12-31T15:00:00Z')).toBe('2025.01.01');
    expect(dottedDateOfDateTime('2024-12-31T14:59:59Z')).toBe('2024.12.31');
  });
});
