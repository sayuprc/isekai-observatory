import { describe, expect, it } from 'bun:test';
import {
  addCoVocalist,
  addCoVocalistToPerformances,
  addPerformance,
  addPersonGroupToPerformances,
  appendUnassignedPerformances,
  collectCoVocalists,
  collectPersonGroups,
  moveCoVocalist,
  removeCoVocalist,
  setCreditName,
  toCoVocalistUnits,
  toPerformancesPayload,
  toSetlistPayload,
  type PerformanceForm,
} from './performance-form';

const performance = (performanceId: string, coVocalists: PerformanceForm['coVocalists'] = []): PerformanceForm => ({
  performanceId,
  songId: `song-${performanceId}`,
  songTitle: `曲${performanceId}`,
  coVocalists,
});

const person = { personId: 'person-1', name: '人物1', creditName: '', personGroup: null };

describe('イベントの楽曲披露フォーム', () => {
  it('楽曲を末尾に披露として追加する', () => {
    const result = addPerformance([performance('p1')], { songId: 'song-2', title: '曲2' });

    expect(result).toHaveLength(2);
    expect(result[1]).toMatchObject({ songId: 'song-2', songTitle: '曲2', coVocalists: [] });
  });

  it('指定した披露だけに共演者を追加する', () => {
    const result = addCoVocalist([performance('p1'), performance('p2')], 1, person);

    expect(result.map((item) => item.coVocalists)).toEqual([[], [person]]);
  });

  it('同じ披露には同一人物を重複して追加しない', () => {
    const performances = [performance('p1', [person])];

    expect(addCoVocalist(performances, 0, person)).toEqual(performances);
  });

  it('指定した共演者のクレジット名だけを変更する', () => {
    const other = { personId: 'person-2', name: '人物2', creditName: '', personGroup: null };
    const result = setCreditName([performance('p1', [person, other])], 0, 1, 'ゲスト');

    expect(result.map((item) => item.coVocalists)).toEqual([[person, { ...other, creditName: 'ゲスト' }]]);
  });

  it('指定した共演者を外す', () => {
    const result = removeCoVocalist([performance('p1', [person])], 0, 0);

    expect(result.map((item) => item.coVocalists)).toEqual([[]]);
  });

  it('存在しない楽曲披露への参照をセットリストの送信値から除く', () => {
    const result = toSetlistPayload(
      [{ setlistItemId: 's1', label: ' 本編 ', performanceIds: ['p1', 'removed'] }],
      [performance('p1')],
    );

    expect(result).toEqual([{ setlistItemId: 's1', orderNo: 1, label: '本編', performanceIds: ['p1'] }]);
  });

  it('未紐づけの楽曲披露だけを披露順に 1 件 1 項目でセットリスト末尾へ足す', () => {
    const existing = { setlistItemId: 's1', label: '', performanceIds: ['p2'] };
    const result = appendUnassignedPerformances([existing], [performance('p1'), performance('p2'), performance('p3')]);

    expect(result[0]).toBe(existing);
    expect(result.slice(1).map((item) => ({ label: item.label, performanceIds: item.performanceIds }))).toEqual([
      { label: '', performanceIds: ['p1'] },
      { label: '', performanceIds: ['p3'] },
    ]);
  });

  it('すべて紐づけ済みならセットリストを変えない', () => {
    const setlist = [{ setlistItemId: 's1', label: '', performanceIds: ['p1'] }];

    expect(appendUnassignedPerformances(setlist, [performance('p1')])).toEqual(setlist);
  });

  it('指定した複数の披露へ同じ共演者をまとめて追加し、付いている披露には重ねない', () => {
    const result = addCoVocalistToPerformances(
      [performance('p1', [person]), performance('p2'), performance('p3')],
      new Set(['p1', 'p2']),
      person,
    );

    expect(result.map((item) => item.coVocalists)).toEqual([[person], [person], []]);
  });

  it('イベント内の共演者を初出順に重複なく集める', () => {
    const other = { personId: 'person-2', name: '人物2', creditName: 'ゲスト', personGroup: null };
    const result = collectCoVocalists([performance('p1', [other]), performance('p2', [person, other])]);

    expect(result).toEqual([
      { personId: 'person-2', name: '人物2' },
      { personId: 'person-1', name: '人物1' },
    ]);
  });

  it('指定した披露の共演者だけを並べ替える', () => {
    const other = { personId: 'person-2', name: '人物2', creditName: '', personGroup: null };
    const result = moveCoVocalist([performance('p1', [person, other]), performance('p2', [person, other])], 0, 1, 0);

    expect(result.map((item) => item.coVocalists)).toEqual([
      [other, person],
      [person, other],
    ]);
  });

  describe('グループとしての共演者', () => {
    const groupRef = { personGroupId: 'group-1', name: 'グループ' };
    const preset = {
      ...groupRef,
      members: [
        { personId: 'member-1', name: 'メンバー1' },
        { personId: 'member-2', name: 'メンバー2' },
      ],
    };
    const member = (personId: string, name: string) => ({ personId, name, creditName: '', personGroup: groupRef });

    it('選択中の披露にメンバーをグループ付きでまとめて追加し、同じグループは重ねない', () => {
      const once = addPersonGroupToPerformances([performance('p1'), performance('p2')], new Set(['p1']), preset);
      const twice = addPersonGroupToPerformances(once, new Set(['p1']), preset);

      expect(twice.map((item) => item.coVocalists)).toEqual([
        [member('member-1', 'メンバー1'), member('member-2', 'メンバー2')],
        [],
      ]);
    });

    it('個別に付いていたメンバーはグループとしての出演に置き換える', () => {
      const individual = { personId: 'member-2', name: 'メンバー2', creditName: '', personGroup: null };
      const result = addPersonGroupToPerformances([performance('p1', [person, individual])], new Set(['p1']), preset);

      expect(result[0]!.coVocalists).toEqual([
        person,
        member('member-1', 'メンバー1'),
        member('member-2', 'メンバー2'),
      ]);
    });

    it('グループのメンバーは最初のメンバーの位置に 1 単位としてまとめる', () => {
      const units = toCoVocalistUnits([member('member-1', 'メンバー1'), person, member('member-2', 'メンバー2')]);

      expect(units.map((unit) => (unit.type === 'group' ? unit.members.length : unit.coVocalist.name))).toEqual([
        2,
        '人物1',
      ]);
    });

    it('並べ替えと削除はグループ単位で行う', () => {
      const performances = [
        performance('p1', [person, member('member-1', 'メンバー1'), member('member-2', 'メンバー2')]),
      ];

      expect(moveCoVocalist(performances, 0, 1, 0)[0]!.coVocalists.map((item) => item.personId)).toEqual([
        'member-1',
        'member-2',
        'person-1',
      ]);
      expect(removeCoVocalist(performances, 0, 1)[0]!.coVocalists).toEqual([person]);
    });

    it('個別の共演者だけを集め、グループは初出時のメンバー構成で集める', () => {
      const performances = [performance('p1', [person, member('member-1', 'メンバー1')])];

      expect(collectCoVocalists(performances)).toEqual([{ personId: 'person-1', name: '人物1' }]);
      expect(collectPersonGroups(performances)).toEqual([
        { ...groupRef, members: [{ personId: 'member-1', name: 'メンバー1' }] },
      ]);
    });

    it('送信値にはグループ ID を共演者ごとに載せる', () => {
      const payload = toPerformancesPayload([performance('p1', [person, member('member-1', 'メンバー1')])]);

      expect(payload[0]!.coVocalists).toEqual([
        { personId: 'person-1', creditName: null, personGroupId: null, orderNo: 1 },
        { personId: 'member-1', creditName: null, personGroupId: 'group-1', orderNo: 2 },
      ]);
    });
  });
});
