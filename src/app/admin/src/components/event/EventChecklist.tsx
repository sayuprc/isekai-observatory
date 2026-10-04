import { For, Show } from 'solid-js';
import { buildEventChecklist, type ChecklistItem } from './event-checklist';
import type { EventFormState } from './event-form';

interface EventChecklistProps {
  form: EventFormState;
  // 別タブの項目を選んだときにそのタブを開く
  onNavigate: (tab: ChecklistItem['tab']) => void;
}

// 概要タブに置く入力状況。未入力の項目を見つけて、入力するタブへ移れるようにする
export const EventChecklist = (props: EventChecklistProps) => {
  const items = () => {
    const assignedIds = new Set(props.form.setlist().flatMap((item) => item.performanceIds));
    return buildEventChecklist({
      typeValue: props.form.typeValue(),
      statusValue: props.form.statusValue(),
      venueCount: props.form.venues().length,
      sourceCount: props.form.sources().length,
      performanceCount: props.form.performances().length,
      setlistCount: props.form.setlist().length,
      unassignedPerformanceCount: props.form
        .performances()
        .filter((performance) => !assignedIds.has(performance.performanceId)).length,
      relatedCount: props.form.mediaEntries().length + props.form.releases().length,
    });
  };

  return (
    <section class="rounded-box border border-base-300 bg-base-200" aria-labelledby="event-checklist-heading">
      <h3 id="event-checklist-heading" class="border-b border-base-300 px-5 py-3 text-sm font-semibold">
        入力状況
      </h3>
      <ul>
        <For each={items()}>
          {(item) => (
            <li class="flex items-center gap-3 border-b border-base-300 px-5 py-2.5 text-sm last:border-b-0">
              <span
                class="size-2 shrink-0 rounded-full"
                classList={{
                  'bg-success': item.state === 'done',
                  'bg-warning': item.state === 'missing',
                  'bg-base-content/30': item.state === 'notApplicable' || item.state === 'optional',
                }}
                aria-hidden="true"
              />
              <span>{item.label}</span>
              <Show
                when={item.tab !== 'overview'}
                fallback={
                  <span
                    class="ml-auto text-right text-xs"
                    classList={{
                      'text-warning': item.state === 'missing',
                      'text-base-content/60': item.state !== 'missing',
                    }}
                  >
                    {item.detail}
                  </span>
                }
              >
                <button
                  type="button"
                  class="link link-hover ml-auto text-right text-xs"
                  classList={{
                    'text-warning': item.state === 'missing',
                    'text-base-content/60': item.state !== 'missing',
                  }}
                  onClick={() => props.onNavigate(item.tab)}
                >
                  {item.detail}
                </button>
              </Show>
            </li>
          )}
        </For>
      </ul>
    </section>
  );
};
