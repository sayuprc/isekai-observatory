import { For, Show, type JSX } from 'solid-js';

export interface TabItem<T extends string> {
  key: T;
  label: string;
  // 件数などの補足。空なら出さない
  count?: string;
}

interface TabListProps<T extends string> {
  label: string;
  // タブとパネルの id を組み立てる接頭辞
  idPrefix: string;
  items: TabItem<T>[];
  current: T;
  onChange: (key: T) => void;
}

export const TabList = <T extends string>(props: TabListProps<T>) => (
  <div role="tablist" aria-label={props.label} class="flex gap-6 overflow-x-auto">
    <For each={props.items}>
      {(item) => (
        <button
          type="button"
          role="tab"
          id={`${props.idPrefix}-tab-${item.key}`}
          aria-controls={`${props.idPrefix}-panel-${item.key}`}
          aria-selected={item.key === props.current}
          class="flex h-10 shrink-0 items-center gap-2 border-b-2 text-sm whitespace-nowrap"
          classList={{
            'border-primary font-semibold text-base-content': item.key === props.current,
            'border-transparent text-base-content/60 hover:text-base-content': item.key !== props.current,
          }}
          onClick={() => props.onChange(item.key)}
        >
          {item.label}
          <Show when={item.count}>
            <span class="font-mono text-xs text-base-content/50">{item.count}</span>
          </Show>
        </button>
      )}
    </For>
  </div>
);

interface TabPanelProps {
  idPrefix: string;
  tabKey: string;
  current: string;
  children: JSX.Element;
}

// 非表示のパネルも DOM に残し、入力途中の状態 (検索欄など) をタブ切り替えで失わないようにする
export const TabPanel = (props: TabPanelProps) => (
  <div
    role="tabpanel"
    id={`${props.idPrefix}-panel-${props.tabKey}`}
    aria-labelledby={`${props.idPrefix}-tab-${props.tabKey}`}
    data-tab={props.tabKey}
    hidden={props.tabKey !== props.current}
    class="space-y-6"
  >
    {props.children}
  </div>
);
