import { createSignal, Show, type JSX } from 'solid-js';
import { FormRow } from '../FormRow';
import { MediaSection } from '../media/MediaSection';
import { SegmentedControl } from '../SegmentedControl';
import { TabList, TabPanel, type TabItem } from '../Tabs';
import type { EventFormState } from './event-form';
import { EVENT_STATUS_OPTIONS, EVENT_TYPE_OPTIONS } from './event-options';
import { PerformanceEditor } from './PerformanceEditor';
import { ReleaseEditor } from './ReleaseEditor';
import { SetlistEditor } from './SetlistEditor';
import { SourceEditor } from './SourceEditor';
import { VenueEditor } from './VenueEditor';

export const EVENT_TABS = ['overview', 'performances', 'related', 'history'] as const;
export type EventTab = (typeof EVENT_TABS)[number];

const TAB_ID_PREFIX = 'event';

interface EventTabListProps {
  form: EventFormState;
  current: EventTab;
  onChange: (tab: EventTab) => void;
  // 作成画面には履歴がない
  withHistory: boolean;
}

export const EventTabList = (props: EventTabListProps) => {
  const items = (): TabItem<EventTab>[] => [
    { key: 'overview', label: '概要' },
    {
      key: 'performances',
      label: '演目',
      count: props.form.canEditSetlist()
        ? `${props.form.performances().length} · ${props.form.setlist().length}`
        : String(props.form.performances().length),
    },
    { key: 'related', label: '関連', count: String(props.form.mediaEntries().length + props.form.releases().length) },
    ...(props.withHistory ? [{ key: 'history' as const, label: '履歴' }] : []),
  ];

  return (
    <TabList
      label="イベントの項目"
      idPrefix={TAB_ID_PREFIX}
      items={items()}
      current={props.current}
      onChange={props.onChange}
    />
  );
};

interface EventFormFieldsProps {
  form: EventFormState;
  tab: EventTab;
  // 履歴タブの中身。作成画面では渡さない
  history?: JSX.Element;
}

export const EventFormFields = (props: EventFormFieldsProps) => (
  <>
    <TabPanel idPrefix={TAB_ID_PREFIX} tabKey="overview" current={props.tab}>
      <EventBasicInfo form={props.form} />
      <VenueEditor venues={props.form.venues()} onChange={props.form.setVenues} />
      <SourceEditor sources={props.form.sources()} onChange={props.form.setSources} />
    </TabPanel>
    <TabPanel idPrefix={TAB_ID_PREFIX} tabKey="performances" current={props.tab}>
      <PerformanceEditor
        performances={props.form.performances()}
        onChange={props.form.updatePerformances}
        relatedReleases={props.form.releases()}
        disabled={!props.form.canEditPerformances()}
      />
      <Show when={props.form.canEditSetlist()}>
        <SetlistEditor
          setlist={props.form.setlist()}
          performances={props.form.performances()}
          onChange={props.form.setSetlist}
          relatedReleases={props.form.releases()}
          onImport={props.form.importSetlist}
          disabled={!props.form.canEditPerformances()}
        />
      </Show>
    </TabPanel>
    <TabPanel idPrefix={TAB_ID_PREFIX} tabKey="related" current={props.tab}>
      <MediaSection
        entries={props.form.mediaEntries}
        setEntries={props.form.setMediaEntries}
        availableMedia={props.form.availableMedia}
        setAvailableMedia={props.form.setAvailableMedia}
      />
      <ReleaseEditor releases={props.form.releases()} onChange={props.form.setReleases} />
    </TabPanel>
    <Show when={props.history}>
      <TabPanel idPrefix={TAB_ID_PREFIX} tabKey="history" current={props.tab}>
        {props.history}
      </TabPanel>
    </Show>
  </>
);

type ScheduleMode = 'undecided' | 'single' | 'range';

const SCHEDULE_MODE_OPTIONS: { value: ScheduleMode; label: string }[] = [
  { value: 'undecided', label: '未定' },
  { value: 'single', label: '単日' },
  { value: 'range', label: '期間' },
];

const DISPLAY_OPTIONS = [
  { value: 'true', label: '表示する' },
  { value: 'false', label: '表示しない' },
];

const EventBasicInfo = (props: { form: EventFormState }) => {
  // 開催時期の形式は入力欄の出し分けにだけ使う。保存する値は開始日と終了日から決まる
  const [scheduleMode, setScheduleMode] = createSignal<ScheduleMode>(
    props.form.endOn() ? 'range' : props.form.startOn() ? 'single' : 'undecided',
  );

  const changeScheduleMode = (next: ScheduleMode) => {
    setScheduleMode(next);
    if (next === 'undecided') {
      props.form.setStartOn('');
    }
    if (next !== 'range') {
      props.form.setEndOn('');
    }
  };

  return (
    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border px-6 py-3">
      <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
      <FormRow label="タイトル" for="title">
        <input
          id="title"
          class="input w-full"
          required
          maxLength={255}
          value={props.form.title()}
          onInput={(e) => props.form.setTitle(e.currentTarget.value)}
        />
      </FormRow>
      <FormRow label="説明" for="description" hint="開演時刻などの補足を書きます">
        <textarea
          id="description"
          class="textarea w-full"
          rows={3}
          value={props.form.description()}
          onInput={(e) => props.form.setDescription(e.currentTarget.value)}
        />
      </FormRow>
      <FormRow
        label="活動種別"
        hint="ライブと配信だけがセットリストを持てます。ほかの種別に変えるとセットリストは消えます"
      >
        <SegmentedControl
          label="活動種別"
          options={EVENT_TYPE_OPTIONS}
          value={props.form.typeValue()}
          onChange={props.form.changeType}
        />
      </FormRow>
      <FormRow label="開催状態" hint="延期・中止に変えると楽曲披露とセットリストは消えます">
        <SegmentedControl
          label="開催状態"
          options={EVENT_STATUS_OPTIONS}
          value={props.form.statusValue()}
          onChange={props.form.changeStatus}
        />
      </FormRow>
      <FormRow label="開催時期">
        <div class="flex flex-wrap items-center gap-3">
          <SegmentedControl
            label="開催時期の形式"
            options={SCHEDULE_MODE_OPTIONS}
            value={scheduleMode()}
            onChange={changeScheduleMode}
          />
          <Show when={scheduleMode() !== 'undecided'}>
            <input
              type="date"
              class="input w-44 font-mono"
              aria-label={scheduleMode() === 'range' ? '開始日' : '開催日'}
              value={props.form.startOn()}
              onInput={(e) => props.form.setStartOn(e.currentTarget.value)}
            />
          </Show>
          <Show when={scheduleMode() === 'range'}>
            <span class="text-base-content/60" aria-hidden="true">
              〜
            </span>
            <input
              type="date"
              class="input w-44 font-mono"
              aria-label="終了日"
              value={props.form.endOn()}
              onInput={(e) => props.form.setEndOn(e.currentTarget.value)}
            />
          </Show>
        </div>
      </FormRow>
      <FormRow label="公開">
        <SegmentedControl
          label="公開"
          options={DISPLAY_OPTIONS}
          value={String(props.form.isDisplay())}
          onChange={(value) => props.form.setIsDisplay(value === 'true')}
        />
      </FormRow>
    </fieldset>
  );
};
