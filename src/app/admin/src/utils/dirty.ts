import { createMemo, onCleanup, onMount } from 'solid-js';

// 初期値との差分で未保存の変更を検知し、未保存のまま離脱しようとしたらブラウザの確認を出す
export const createDirtyTracker = (snapshot: () => unknown) => {
  const initial = JSON.stringify(snapshot());
  let leaving = false;
  const isDirty = createMemo(() => JSON.stringify(snapshot()) !== initial);

  const handleBeforeUnload = (event: BeforeUnloadEvent) => {
    if (leaving || !isDirty()) {
      return;
    }
    event.preventDefault();
  };

  onMount(() => window.addEventListener('beforeunload', handleBeforeUnload));
  onCleanup(() => window.removeEventListener('beforeunload', handleBeforeUnload));

  // 保存・削除・破棄のあとに画面を移るときは確認を出さない
  const allowLeave = () => {
    leaving = true;
  };

  return { isDirty, allowLeave };
};
