import { createSignal, Show } from 'solid-js';
import type { EventFormState } from './event-form';
import { emptySelection } from './performance-selection';
import { PerformanceEditor } from './PerformanceEditor';
import { PerformanceSelectionPanel } from './PerformanceSelectionPanel';
import { SetlistEditor } from './SetlistEditor';

// 演目タブ。楽曲披露・セットリスト・選択中の楽曲披露を並べ、選択状態を 3 つで共有する
// 幅に応じて 1 列 → 2 列 (楽曲披露とセットリストを縦、選択パネルを右) → 3 列と組み替える
// セットリストを持たない種別の 3 列幅は、楽曲披露と選択パネルの 2 列にする
export const PerformanceWorkspace = (props: { form: EventFormState }) => {
  // 並べ替えや削除で位置が変わっても同じ披露を指すよう ID で持つ
  const [selection, setSelection] = createSignal(emptySelection());

  return (
    <div class="@container">
      <div
        class="grid gap-6 [grid-template-areas:'perf'_'panel'_'setlist'] @5xl:grid-cols-[minmax(0,1fr)_20rem] @5xl:[grid-template-areas:'perf_panel'_'setlist_panel']"
        classList={{
          "@[100rem]:grid-cols-[minmax(0,56rem)_minmax(0,56rem)_22rem] @[100rem]:[grid-template-areas:'perf_setlist_panel']":
            props.form.canEditSetlist(),
          // セットリストを持たない種別は、空の列を作らず選択パネルを楽曲披露の隣に置く
          "@[100rem]:grid-cols-[minmax(0,56rem)_22rem] @[100rem]:[grid-template-areas:'perf_panel']":
            !props.form.canEditSetlist(),
        }}
      >
        <div class="min-w-0 [grid-area:perf]">
          <PerformanceEditor
            performances={props.form.performances()}
            onChange={props.form.updatePerformances}
            relatedReleases={props.form.releases()}
            selection={selection()}
            onSelectionChange={setSelection}
            setlist={props.form.setlist()}
            disabled={!props.form.canEditPerformances()}
          />
        </div>
        <div class="min-w-0 [grid-area:setlist]">
          <Show when={props.form.canEditSetlist()}>
            <SetlistEditor
              setlist={props.form.setlist()}
              performances={props.form.performances()}
              onChange={props.form.setSetlist}
              relatedReleases={props.form.releases()}
              onImport={props.form.importSetlist}
              highlightedPerformanceIds={selection().selectedIds}
              disabled={!props.form.canEditPerformances()}
            />
          </Show>
        </div>
        <div class="min-w-0 [grid-area:panel] @5xl:sticky @5xl:top-44 @5xl:self-start">
          <PerformanceSelectionPanel
            performances={props.form.performances()}
            onChange={props.form.updatePerformances}
            setlist={props.form.setlist()}
            selection={selection()}
            onSelectionChange={setSelection}
            disabled={!props.form.canEditPerformances()}
          />
        </div>
      </div>
    </div>
  );
};
