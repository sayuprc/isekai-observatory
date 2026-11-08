import { createSignal } from 'solid-js';

// 一覧で選んだ記録の ID。ページを切り替えても保つ
export const createBulkSelection = () => {
  const [selected, setSelected] = createSignal<ReadonlySet<string>>(new Set());

  const update = (change: (next: Set<string>) => void) => {
    setSelected((prev) => {
      const next = new Set(prev);
      change(next);
      return next;
    });
  };

  return {
    ids: () => [...selected()],
    count: () => selected().size,
    has: (id: string) => selected().has(id),
    toggle: (id: string, checked: boolean) => update((next) => (checked ? next.add(id) : next.delete(id))),
    toggleAll: (ids: string[], checked: boolean) =>
      update((next) => ids.forEach((id) => (checked ? next.add(id) : next.delete(id)))),
    allSelected: (ids: string[]) => ids.length > 0 && ids.every((id) => selected().has(id)),
    clear: () => setSelected(() => new Set<string>()),
  };
};

export type BulkSelection = ReturnType<typeof createBulkSelection>;
