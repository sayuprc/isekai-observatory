import type { JSX } from 'solid-js';

interface FormColumnsProps {
  // 主な入力。狭い幅では上に置く
  main: JSX.Element;
  // 付随する一覧など。狭い幅では main の下に積む
  side: JSX.Element;
}

// 複数の枠を持つ画面の組み方。広い幅では main と side を左右に並べ、各列は 1 列のときと同じ幅に抑える
export const FormColumns = (props: FormColumnsProps) => (
  <div class="@container">
    <div class="grid grid-cols-[minmax(0,56rem)] items-start gap-6 @6xl:grid-cols-[repeat(2,minmax(0,56rem))]">
      <div class="min-w-0 space-y-6">{props.main}</div>
      <div class="min-w-0 space-y-6">{props.side}</div>
    </div>
  </div>
);
