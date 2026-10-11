import { describe, expect, it } from 'bun:test';
import { EVENT_STATUS, coVocalistNames, eventStatusName } from './types';

const group = { personGroupId: 'group-1', name: 'グループ' };
const person = (name: string, personGroup: typeof group | null = null, creditName: string | null = null) => ({
  personId: name,
  name,
  creditName,
  personGroup,
  orderNo: 1,
});

describe('共演者の表示名', () => {
  it('グループとして出演した共演者を最初のメンバーの位置にグループ名でまとめる', () => {
    expect(coVocalistNames([person('ゲスト'), person('A', group), person('B', group)])).toEqual(['ゲスト', 'グループ']);
  });

  it('個別の共演者はクレジット名を優先する', () => {
    expect(coVocalistNames([person('A', null, '別名'), person('B')])).toEqual(['別名', 'B']);
  });
});

describe('開催状態の表示名', () => {
  it('通常開催は表示しない', () => {
    expect(eventStatusName(EVENT_STATUS.normal)).toBeNull();
  });

  it('延期と中止は表示する', () => {
    expect(eventStatusName(EVENT_STATUS.postponed)).toBe('延期');
    expect(eventStatusName(EVENT_STATUS.cancelled)).toBe('中止');
  });
});
