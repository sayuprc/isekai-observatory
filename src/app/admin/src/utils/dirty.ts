import { createMemo, createSignal, onCleanup, onMount } from 'solid-js';

// 初期値との差分で未保存の変更を検知し、未保存のまま離脱しようとしたらブラウザの確認を出す
// 初期値は表示が終わった時点の値にする。子の部品がマウント時に整えた値を変更として数えないため
export const createDirtyTracker = (snapshot: () => unknown) => {
  const [initial, setInitial] = createSignal<string>();
  let leaving = false;
  const isDirty = createMemo(() => {
    const base = initial();
    return base !== undefined && JSON.stringify(snapshot()) !== base;
  });

  const handleBeforeUnload = (event: BeforeUnloadEvent) => {
    if (leaving || !isDirty()) {
      return;
    }
    event.preventDefault();
  };

  // client:load の部品はサーバーでも描画され、そこでは onCleanup も走る
  // window はブラウザにしかないので、登録と解除はどちらも onMount の中で行う
  onMount(() => {
    setInitial(JSON.stringify(snapshot()));
    window.addEventListener('beforeunload', handleBeforeUnload);
    onCleanup(() => window.removeEventListener('beforeunload', handleBeforeUnload));
  });

  // 保存・削除・破棄のあとに画面を移るときは確認を出さない
  const allowLeave = () => {
    leaving = true;
  };

  return { isDirty, allowLeave };
};

// 未保存の変更を破棄して読み込み直す。確認でキャンセルされたら何もしない
export const discardChanges = (allowLeave: () => void) => {
  if (!window.confirm('未保存の変更を破棄します。よろしいですか？')) return;
  allowLeave();
  window.location.reload();
};
