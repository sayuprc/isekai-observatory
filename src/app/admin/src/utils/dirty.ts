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

  onMount(() => {
    setInitial(JSON.stringify(snapshot()));
    window.addEventListener('beforeunload', handleBeforeUnload);
  });
  onCleanup(() => window.removeEventListener('beforeunload', handleBeforeUnload));

  // 保存・削除・破棄のあとに画面を移るときは確認を出さない
  const allowLeave = () => {
    leaving = true;
  };

  return { isDirty, allowLeave };
};

// 入力値を状態に持たず、送信時に FormData で読むフォーム向け
// input / change のたびに FormData を読み直し、extra に渡した状態とあわせて初期値と比べる
export const createFormDirtyTracker = (extra?: () => unknown) => {
  const [form, setForm] = createSignal<HTMLFormElement>();
  const [version, setVersion] = createSignal(0);
  const bump = () => setVersion((current) => current + 1);

  const snapshot = () => {
    version();
    const element = form();
    const entries = element ? [...new FormData(element)].map(([key, value]) => [key, String(value)]) : [];
    return [entries, extra?.()];
  };

  const tracker = createDirtyTracker(snapshot);

  const bindForm = (element: HTMLFormElement) => {
    setForm(element);
    element.addEventListener('input', bump);
    element.addEventListener('change', bump);
  };

  return { ...tracker, bindForm };
};

// 未保存の変更を破棄して読み込み直す。確認でキャンセルされたら何もしない
export const discardChanges = (allowLeave: () => void) => {
  if (!window.confirm('未保存の変更を破棄します。よろしいですか？')) return;
  allowLeave();
  window.location.reload();
};
