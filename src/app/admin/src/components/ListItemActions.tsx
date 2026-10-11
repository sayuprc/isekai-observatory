interface ListItemActionsProps {
  label: string;
  index: number;
  length: number;
  onMove: (fromIndex: number, toIndex: number) => void;
  onRemove: () => void;
}

// 並べ替えできる一覧の 1 行に付ける、上下移動と削除のボタン
export const ListItemActions = (props: ListItemActionsProps) => (
  <div class="flex gap-2">
    <button
      type="button"
      class="btn btn-ghost btn-xs"
      aria-label={`${props.label}を上へ移動`}
      disabled={props.index === 0}
      onClick={() => props.onMove(props.index, props.index - 1)}
    >
      ↑
    </button>
    <button
      type="button"
      class="btn btn-ghost btn-xs"
      aria-label={`${props.label}を下へ移動`}
      disabled={props.index === props.length - 1}
      onClick={() => props.onMove(props.index, props.index + 1)}
    >
      ↓
    </button>
    <button type="button" class="btn btn-outline btn-error btn-xs" onClick={() => props.onRemove()}>
      削除
    </button>
  </div>
);
