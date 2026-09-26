import { z } from 'astro:content';

/** API の並び順を index として持たせたコレクションの要素 */
export type Indexed<T> = T & { index: number };

export const indexedSchema = <T>() =>
  z.custom<Indexed<T>>(
    (val: unknown) => typeof val === 'object' && val !== null && typeof (val as { index?: unknown }).index === 'number',
  );

/**
 * astro sync は型生成と同時にローダーも実行する
 * API がない型チェック用の sync では、SKIP_CONTENT_FETCH=true で API を呼ばず空のコレクションにする
 */
const shouldSkipFetch = (): boolean => process.env.SKIP_CONTENT_FETCH === 'true';

/** API から全件を取り、並び順を index に残してコレクションの要素にする */
export const indexedLoader =
  <T extends object>(fetchAll: () => Promise<T[]>, idOf: (item: T) => string) =>
  async () =>
    shouldSkipFetch() ? [] : (await fetchAll()).map((item, index) => ({ id: idOf(item), ...item, index }));
