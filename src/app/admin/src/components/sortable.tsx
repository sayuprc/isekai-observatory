import { createSignal } from 'solid-js';
import type { JSX } from 'solid-js';

type SortableScope = string | number;

type Position<TScope extends SortableScope> = {
  scope: TScope;
  index: number;
};

export function reorderItems<T>(items: readonly T[], fromIndex: number, toIndex: number): T[] {
  if (
    fromIndex === toIndex
    || fromIndex < 0
    || toIndex < 0
    || fromIndex >= items.length
    || toIndex >= items.length
  ) {
    return [...items];
  }

  const reordered = [...items];
  const [item] = reordered.splice(fromIndex, 1);

  if (item === undefined) {
    return [...items];
  }

  reordered.splice(toIndex, 0, item);

  return reordered;
}

export const createSortable = <TScope extends SortableScope>(
  onMove: (scope: TScope, fromIndex: number, toIndex: number) => void,
) => {
  const [dragging, setDragging] = createSignal<Position<TScope> | null>(null);
  const [dropTarget, setDropTarget] = createSignal<Position<TScope> | null>(null);

  const isSamePosition = (position: Position<TScope> | null, scope: TScope, index: number) =>
    position?.scope === scope && position.index === index;

  const dragHandleProps = (
    scope: TScope,
    index: number,
    label: string,
  ): JSX.ButtonHTMLAttributes<HTMLButtonElement> => ({
    'type': 'button',
    'draggable': true,
    'class': 'btn btn-ghost btn-xs cursor-grab active:cursor-grabbing',
    'aria-label': `${label}を並べ替え`,
    'title': 'ドラッグまたは上下キーで並べ替え',
    'onDragStart': (event) => {
      setDragging({ scope, index });
      event.dataTransfer?.setData('text/plain', `${String(scope)}:${index}`);

      if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
      }
    },
    'onDragEnd': () => {
      setDragging(null);
      setDropTarget(null);
    },
    'onKeyDown': (event) => {
      if (event.key !== 'ArrowUp' && event.key !== 'ArrowDown') {
        return;
      }

      event.preventDefault();
      onMove(scope, index, index + (event.key === 'ArrowUp' ? -1 : 1));
    },
  });

  const dropTargetProps = (
    scope: TScope,
    index: number,
  ): Pick<JSX.HTMLAttributes<HTMLElement>, 'onDragOver' | 'onDrop'> => ({
    onDragOver: (event) => {
      const source = dragging();

      if (source?.scope !== scope) {
        return;
      }

      event.preventDefault();
      setDropTarget({ scope, index });

      if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'move';
      }
    },
    onDrop: (event) => {
      const source = dragging();

      if (source?.scope !== scope) {
        return;
      }

      event.preventDefault();
      onMove(scope, source.index, index);
      setDragging(null);
      setDropTarget(null);
    },
  });

  return {
    dragHandleProps,
    dropTargetProps,
    isDragging: (scope: TScope, index: number) => isSamePosition(dragging(), scope, index),
    isDropTarget: (scope: TScope, index: number) => isSamePosition(dropTarget(), scope, index),
  };
};
