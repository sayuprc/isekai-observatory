import { describe, expect, it } from 'bun:test';
import { coVocalistNames } from './types';

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
