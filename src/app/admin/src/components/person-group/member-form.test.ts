import { describe, expect, it } from 'bun:test';
import { addMember, toMembersPayload } from './member-form';

const person = (personId: string) => ({ personId, name: `人物${personId}` });

describe('人物グループのメンバー', () => {
  it('メンバーを末尾に追加し、追加済みの人物は重複させない', () => {
    const members = addMember([], person('1'));

    expect(addMember(members, person('2')).map((member) => member.personId)).toEqual(['1', '2']);
    expect(addMember(members, person('1'))).toBe(members);
  });

  it('並び順を 1 始まりの表示順にして送る', () => {
    expect(toMembersPayload([person('2'), person('1')])).toEqual([
      { personId: '2', orderNo: 1 },
      { personId: '1', orderNo: 2 },
    ]);
  });
});
