import { z } from 'astro:content';

/** API の並び順を index として持たせたコレクションの要素 */
export type Indexed<T> = T & { index: number };

export const indexedSchema = <T>() =>
  z.custom<Indexed<T>>(
    (val: unknown) => typeof val === 'object' && val !== null && typeof (val as { index?: unknown }).index === 'number',
  );

/** API から全件を取り、並び順を index に残してコレクションの要素にする */
export const indexedLoader =
  <T extends object>(fetchAll: () => Promise<T[]>, idOf: (item: T) => string) =>
  async () =>
    (await fetchAll()).map((item, index) => ({ id: idOf(item), ...item, index }));
