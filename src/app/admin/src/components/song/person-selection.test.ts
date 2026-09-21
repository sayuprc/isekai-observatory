import { describe, expect, it } from 'bun:test';
import {
  addSelectedPerson,
  addSelectedPersonToRole,
  toRequestSongPersons,
} from './person-selection';

describe('楽曲の人物選択', () => {
  it('同じ役割には同一人物を重複して追加しない', () => {
    const selected = [{ personId: 'person-1', name: '人物1' }];

    expect(addSelectedPerson(selected, { personId: 'person-1', name: '人物1' })).toEqual(selected);
  });

  it('選択した役割だけに人物を追加する', () => {
    const selections = {
      1: [{ personId: 'person-1', name: '人物1' }],
      2: [],
      3: [],
    };

    expect(addSelectedPersonToRole(selections, 2, { personId: 'person-2', name: '人物2' })).toEqual({
      1: [{ personId: 'person-1', name: '人物1' }],
      2: [{ personId: 'person-2', name: '人物2' }],
      3: [],
    });
  });

  it('画面上の順序から送信用の表示順を生成する', () => {
    const selected = [
      { personId: 'person-2', name: '人物2' },
      { personId: 'person-1', name: '人物1' },
    ];

    expect(toRequestSongPersons(selected, 2)).toEqual([
      { personId: 'person-2', role: 2, orderNo: 1 },
      { personId: 'person-1', role: 2, orderNo: 2 },
    ]);
  });
});
