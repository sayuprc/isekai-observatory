export type ScheduleMode = 'undecided' | 'single' | 'range';

export interface ScheduleState {
  mode: ScheduleMode;
  startOn: string;
  endOn: string;
  // 形式を切り替えて入力欄が隠れても失わないよう、最後に表示していた日付を覚えておく
  rememberedStartOn: string;
  rememberedEndOn: string;
}

// 保存する値 (開始日と終了日) から形式を決める
export const initialScheduleState = (startOn: string, endOn: string): ScheduleState => ({
  mode: endOn ? 'range' : startOn ? 'single' : 'undecided',
  startOn,
  endOn,
  rememberedStartOn: startOn,
  rememberedEndOn: endOn,
});

// 形式を切り替える。隠れる日付は空にして保存対象から外し、再び表示するときは覚えておいた日付を戻す
// 元の形式に戻せば保存する値も元どおりになり、未保存の変更として数えられない
export const switchScheduleMode = (state: ScheduleState, next: ScheduleMode): ScheduleState => {
  const rememberedStartOn = state.mode === 'undecided' ? state.rememberedStartOn : state.startOn;
  const rememberedEndOn = state.mode === 'range' ? state.endOn : state.rememberedEndOn;

  return {
    mode: next,
    startOn: next === 'undecided' ? '' : rememberedStartOn,
    endOn: next === 'range' ? rememberedEndOn : '',
    rememberedStartOn,
    rememberedEndOn,
  };
};
