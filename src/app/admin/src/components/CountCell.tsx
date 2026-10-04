import { Show } from 'solid-js';

interface CountCellProps {
  count: number;
  // 持てない項目は「—」にする
  applicable?: boolean;
  // 入力が期待される項目で、0 件を警告色にする
  warnWhenZero?: boolean;
}

// 一覧の件数の列
export const CountCell = (props: CountCellProps) => (
  <td class="text-right font-mono text-xs">
    <Show when={props.applicable ?? true} fallback={<span class="text-base-content/40">—</span>}>
      <span
        classList={{
          'text-warning': props.warnWhenZero === true && props.count === 0,
          'text-base-content/50': props.warnWhenZero !== true && props.count === 0,
        }}
      >
        {props.count}
      </span>
    </Show>
  </td>
);
