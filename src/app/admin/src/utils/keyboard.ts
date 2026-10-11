type EnterKeyEvent = Pick<KeyboardEvent, 'key' | 'isComposing'>;

// IME の変換確定で押した Enter は操作として扱わない
export const isEnterKey = (event: EnterKeyEvent) => event.key === 'Enter' && !event.isComposing;

// 保存系のフォームは Enter による暗黙送信を止め、保存ボタンからだけ送信する
export const preventImplicitSubmit = (event: KeyboardEvent) => {
  if (event.key === 'Enter' && event.target instanceof HTMLInputElement) {
    event.preventDefault();
  }
};
