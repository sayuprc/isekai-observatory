import type { BulkSelection } from './bulk-selection';

interface HeaderProps {
  selection: BulkSelection;
  // 表示中のページにある記録の ID
  pageIds: string[];
}

export const BulkSelectAllHeader = (props: HeaderProps) => (
  <th class="w-10">
    <input
      type="checkbox"
      class="checkbox checkbox-sm"
      aria-label="このページをすべて選ぶ"
      checked={props.selection.allSelected(props.pageIds)}
      onChange={(e) => props.selection.toggleAll(props.pageIds, e.currentTarget.checked)}
    />
  </th>
);

interface CellProps {
  selection: BulkSelection;
  id: string;
  title: string;
}

export const BulkSelectCell = (props: CellProps) => (
  <td>
    <input
      type="checkbox"
      class="checkbox checkbox-sm"
      aria-label={`${props.title}を選ぶ`}
      checked={props.selection.has(props.id)}
      onChange={(e) => props.selection.toggle(props.id, e.currentTarget.checked)}
    />
  </td>
);
