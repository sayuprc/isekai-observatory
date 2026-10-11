export type MemberForm = {
  personId: string;
  name: string;
};

// 同じ人物はグループ内で重複できないので、追加済みなら何もしない
export const addMember = (members: MemberForm[], person: MemberForm): MemberForm[] =>
  members.some((member) => member.personId === person.personId)
    ? members
    : [...members, { personId: person.personId, name: person.name }];

// 並び順をそのままメンバーの表示順にする
export const toMembersPayload = (members: MemberForm[]): { personId: string; orderNo: number }[] =>
  members.map((member, index) => ({ personId: member.personId, orderNo: index + 1 }));
