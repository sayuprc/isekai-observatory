import type { JSX } from 'solid-js';

interface PersonGroupChipProps {
  name: string;
  memberNames: string[];
  // createSortable の draggableProps と dropTargetProps を渡す
  sortableProps: JSX.HTMLAttributes<HTMLElement>;
  isDragging: boolean;
  isDropTarget: boolean;
  onRemove: () => void;
}

// グループとして追加した共演者のチップ. メンバーはまとめて並べ替え・削除する
export const PersonGroupChip = (props: PersonGroupChipProps) => (
  <span
    {...props.sortableProps}
    class="badge badge-secondary badge-outline cursor-grab gap-1 whitespace-nowrap active:cursor-grabbing"
    classList={{ 'opacity-50': props.isDragging, 'badge-primary': props.isDropTarget }}
    title={`${props.memberNames.join(' / ')} (ドラッグで並べ替え)`}
  >
    <span>{props.name}</span>
    <span class="text-base-content/60">({props.memberNames.length}人)</span>
    <button type="button" class="text-error" aria-label={`${props.name}を外す`} onClick={() => props.onRemove()}>
      ×
    </button>
  </span>
);
