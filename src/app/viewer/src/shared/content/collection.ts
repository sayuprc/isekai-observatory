type IndexedEntry = {
  id: string;
  data: {
    index: number;
  };
};

export function stripIndex<T extends { index: number }>(data: T): Omit<T, 'index'> {
  const { index, ...entity } = data;
  void index;

  return entity;
}

// getStaticPaths が entry.id を params に使うため、並べ替えても id を落とさない
export function sortByIndex<T extends { index: number }>(
  entries: ReadonlyArray<IndexedEntry>,
): Array<{ id: string; data: T }> {
  return [...entries]
    .sort((a, b) => a.data.index - b.data.index)
    .map(entry => ({ id: entry.id, data: entry.data as T }));
}

export function allFromCollection<T extends { index: number }>(
  entries: ReadonlyArray<IndexedEntry>,
): Omit<T, 'index'>[] {
  return sortByIndex<T>(entries).map(entry => stripIndex(entry.data));
}

export function latestFromCollection<T extends { index: number }>(
  entries: ReadonlyArray<IndexedEntry>,
  position: 'first' | 'last',
): Omit<T, 'index'> | null {
  const sorted = sortByIndex<T>(entries);

  if (sorted.length === 0) {
    return null;
  }

  const entry = position === 'first' ? sorted[0] : sorted[sorted.length - 1];

  return stripIndex(entry.data);
}
