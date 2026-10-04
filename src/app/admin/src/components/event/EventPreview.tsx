import { For, Show } from 'solid-js';
import type { EventSummary } from '../../generated';
import { MetaChip } from '../EntityHeader';
import { buildEventChecklist } from './event-checklist';
import { formatSchedule } from './event-options';

interface EventPreviewProps {
  event: EventSummary | undefined;
  detailUrl: (event: EventSummary) => string;
}

// イベント一覧の右側に出す、選択中の行の概要と入力状況
export const EventPreview = (props: EventPreviewProps) => (
  <section class="rounded-box border border-base-300 bg-base-200" aria-label="選択中のイベント">
    <Show
      when={props.event}
      fallback={<p class="px-5 py-6 text-sm text-base-content/60">行を選ぶと概要と入力状況を表示します</p>}
    >
      {(event) => {
        // 一覧では紐づけの詳細と関連の件数を持たないので、件数だけで判定できる項目を出す
        const checklist = () =>
          buildEventChecklist({
            typeValue: event().type.value,
            statusValue: event().status.value,
            venueCount: event().venueNames.length,
            sourceCount: event().sourceCount,
            performanceCount: event().performanceCount,
            setlistCount: event().setlistItemCount,
            unassignedPerformanceCount: 0,
            relatedCount: 0,
          }).filter((item) => item.key !== 'related');

        return (
          <>
            <div class="border-b border-base-300 px-5 py-4">
              <p class="leading-snug font-semibold break-words">{event().title}</p>
              <div class="mt-2 flex flex-wrap gap-1.5">
                <MetaChip>{event().type.name}</MetaChip>
                <MetaChip>
                  <span class="font-mono">{formatSchedule(event().schedule)}</span>
                </MetaChip>
                <MetaChip>{event().status.name}</MetaChip>
              </div>
            </div>
            <dl class="grid grid-cols-[auto_minmax(0,1fr)] gap-x-4 gap-y-1 border-b border-base-300 px-5 py-3 text-sm">
              <dt class="text-base-content/60">開催先</dt>
              <dd class="break-words">{event().venueNames.join(' · ') || '未登録'}</dd>
            </dl>
            <p class="px-5 pt-3 pb-1 text-xs text-base-content/60">入力状況</p>
            <ul>
              <For each={checklist()}>
                {(item) => (
                  <li class="flex items-center gap-3 border-b border-base-300 px-5 py-2 text-sm">
                    <span
                      class="size-2 shrink-0 rounded-full"
                      classList={{
                        'bg-success': item.state === 'done',
                        'bg-warning': item.state === 'missing',
                        'bg-base-content/30': item.state === 'notApplicable',
                      }}
                      aria-hidden="true"
                    />
                    <span>{item.label}</span>
                    <span
                      class="ml-auto text-right text-xs"
                      classList={{
                        'text-warning': item.state === 'missing',
                        'text-base-content/60': item.state !== 'missing',
                      }}
                    >
                      {item.detail}
                    </span>
                  </li>
                )}
              </For>
            </ul>
            <div class="flex flex-wrap gap-2 px-5 py-4">
              <a href={props.detailUrl(event())} class="btn btn-primary btn-sm">
                開く
              </a>
              <a href={`${props.detailUrl(event())}&tab=performances`} class="btn btn-sm">
                演目を入力
              </a>
            </div>
          </>
        );
      }}
    </Show>
  </section>
);
