export type PerformanceSelection = {
  selectedIds: ReadonlySet<string>;
  // Shift+クリックで範囲選択するときの起点. 並べ替えや削除でずれないよう ID で持つ
  anchorId: string | null;
};

export const emptySelection = (): PerformanceSelection => ({ selectedIds: new Set(), anchorId: null });

// 通常のクリックは 1 行の選択を切り替え、範囲指定のクリックは起点からその行までを選択に加える
export const clickPerformance = (
  selection: PerformanceSelection,
  performanceIds: readonly string[],
  index: number,
  extendRange: boolean,
): PerformanceSelection => {
  const id = performanceIds[index];
  if (id === undefined) {
    return selection;
  }

  const next = new Set(selection.selectedIds);
  const anchorIndex = selection.anchorId === null ? -1 : performanceIds.indexOf(selection.anchorId);

  if (extendRange && anchorIndex !== -1) {
    const [start, end] = anchorIndex < index ? [anchorIndex, index] : [index, anchorIndex];
    performanceIds.slice(start, end + 1).forEach((performanceId) => next.add(performanceId));
    return { selectedIds: next, anchorId: selection.anchorId };
  }

  if (next.has(id)) {
    next.delete(id);
  } else {
    next.add(id);
  }

  return { selectedIds: next, anchorId: id };
};
