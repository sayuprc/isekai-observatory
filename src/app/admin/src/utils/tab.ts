import { createSignal } from 'solid-js';

// 表示中のタブを URL の ?tab= に同期する。再読み込みや URL の共有で同じタブを開けるようにする
export const createTabState = <T extends string>(tabs: readonly T[], fallback: T) => {
  const read = (): T => {
    const value = new URLSearchParams(window.location.search).get('tab');
    return tabs.find((tab) => tab === value) ?? fallback;
  };

  const [tab, setTabSignal] = createSignal<T>(read());

  const setTab = (next: T) => {
    setTabSignal(() => next);
    const url = new URL(window.location.href);
    if (next === fallback) {
      url.searchParams.delete('tab');
    } else {
      url.searchParams.set('tab', next);
    }
    window.history.replaceState(window.history.state, '', url);
  };

  // 隠れたタブの入力欄が検証で弾かれると、ブラウザはエラーを出せずに送信だけ止める
  // form の invalid を capture で拾い、その欄のタブを開いてからエラーを出し直す
  let revealing = false;
  const revealInvalidField = (event: Event) => {
    const field = event.target;
    if (revealing || !(field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement)) {
      return;
    }
    const key = field.closest<HTMLElement>('[data-tab]')?.dataset['tab'];
    const next = tabs.find((candidate) => candidate === key);
    if (!next || next === tab()) {
      return;
    }
    revealing = true;
    setTab(next);
    setTimeout(() => {
      revealing = false;
      field.reportValidity();
    });
  };

  const bindForm = (form: HTMLFormElement) => form.addEventListener('invalid', revealInvalidField, true);

  return { tab, setTab, bindForm };
};
