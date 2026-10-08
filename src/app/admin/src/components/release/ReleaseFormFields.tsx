import { children, type JSX, Show } from 'solid-js';
import { FormRow } from '../FormRow';
import { SegmentedControl } from '../SegmentedControl';
import { TabList, TabPanel, type TabItem } from '../Tabs';
import { ColorField } from './ColorField';
import { FormatCheckboxes } from './FormatCheckboxes';
import { MediaEditor } from './MediaEditor';
import type { ReleaseFormState } from './release-form';

export const RELEASE_TABS = ['overview', 'tracks', 'history'] as const;
export type ReleaseTab = (typeof RELEASE_TABS)[number];

const TAB_ID_PREFIX = 'release';

const DISPLAY_OPTIONS = [
  { value: 'true', label: '表示' },
  { value: 'false', label: '非表示' },
];

interface ReleaseTabListProps {
  form: ReleaseFormState;
  current: ReleaseTab;
  onChange: (tab: ReleaseTab) => void;
  // 作成画面には履歴がない
  withHistory: boolean;
}

export const ReleaseTabList = (props: ReleaseTabListProps) => {
  const items = (): TabItem<ReleaseTab>[] => [
    { key: 'overview', label: '概要' },
    { key: 'tracks', label: '媒体と収録曲', count: String(props.form.trackCount()) },
    ...(props.withHistory ? [{ key: 'history' as const, label: '履歴' }] : []),
  ];

  return (
    <TabList
      label="リリースの項目"
      idPrefix={TAB_ID_PREFIX}
      items={items()}
      current={props.current}
      onChange={props.onChange}
    />
  );
};

interface ReleaseFormFieldsProps {
  form: ReleaseFormState;
  tab: ReleaseTab;
  getFieldError: (field: string) => string | undefined;
  // 履歴タブの中身。作成画面では渡さない
  history?: JSX.Element;
}

export const ReleaseFormFields = (props: ReleaseFormFieldsProps) => {
  // JSX の props を Show の条件と中身で 2 回参照すると要素が 2 つ作られるので、1 回だけ評価して使い回す
  const history = children(() => props.history);

  return (
    <>
      <TabPanel idPrefix={TAB_ID_PREFIX} tabKey="overview" current={props.tab} class="max-w-4xl">
        <ReleaseBasicInfo form={props.form} getFieldError={props.getFieldError} />
      </TabPanel>
      <TabPanel idPrefix={TAB_ID_PREFIX} tabKey="tracks" current={props.tab} class="max-w-5xl">
        <MediaEditor
          media={props.form.media()}
          onChange={props.form.setMedia}
          fieldError={props.getFieldError('media')}
        />
      </TabPanel>
      <Show when={history()}>
        <TabPanel idPrefix={TAB_ID_PREFIX} tabKey="history" current={props.tab} class="max-w-4xl">
          {history()}
        </TabPanel>
      </Show>
    </>
  );
};

const FieldError = (props: { message: string | undefined }) => (
  <Show when={props.message}>{(message) => <p class="text-xs text-error">{message()}</p>}</Show>
);

const ReleaseBasicInfo = (props: { form: ReleaseFormState; getFieldError: (field: string) => string | undefined }) => (
  <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border px-6 py-3">
    <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
    <FormRow label="版名 (任意)" for="name">
      <input
        id="name"
        type="text"
        class="input w-full"
        value={props.form.name()}
        onInput={(e) => props.form.setName(e.currentTarget.value)}
        placeholder="通常盤 / 初回限定盤 / 配信 など"
        classList={{ 'input-error': !!props.getFieldError('name') }}
      />
      <FieldError message={props.getFieldError('name')} />
    </FormRow>
    <FormRow label="発売日" for="releasedOn">
      <input
        id="releasedOn"
        type="date"
        class="input w-44 font-mono"
        value={props.form.releasedOn()}
        onInput={(e) => props.form.setReleasedOn(e.currentTarget.value)}
        classList={{ 'input-error': !!props.getFieldError('releasedOn') }}
      />
      <FieldError message={props.getFieldError('releasedOn')} />
    </FormRow>
    <FormRow label="説明" for="description">
      <textarea
        id="description"
        class="textarea min-h-32 w-full"
        value={props.form.description()}
        onInput={(e) => props.form.setDescription(e.currentTarget.value)}
        classList={{ 'textarea-error': !!props.getFieldError('description') }}
      />
      <FieldError message={props.getFieldError('description')} />
    </FormRow>
    {/* 代表色と提供形態は部品が自前でラベルを持つので、ラベル左の行に入れず並べる */}
    <div class="border-b border-base-300 py-3">
      <ColorField value={props.form.color()} onChange={props.form.setColor} fieldError={props.getFieldError('color')} />
    </div>
    <div class="border-b border-base-300 py-3">
      <FormatCheckboxes
        formatValues={props.form.formatValues()}
        onChange={props.form.setFormatValues}
        fieldError={props.getFieldError('formats')}
      />
    </div>
    <FormRow label="表示順" for="orderNo">
      <input
        id="orderNo"
        type="number"
        min="1"
        step="1"
        class="input w-40"
        value={props.form.orderNo()}
        onInput={(e) => props.form.setOrderNo(Number(e.currentTarget.value))}
        classList={{ 'input-error': !!props.getFieldError('orderNo') }}
      />
      <FieldError message={props.getFieldError('orderNo')} />
    </FormRow>
    <FormRow label="公開">
      <SegmentedControl
        label="公開"
        options={DISPLAY_OPTIONS}
        value={String(props.form.isDisplay())}
        onChange={(value) => props.form.setIsDisplay(value === 'true')}
      />
      <FieldError message={props.getFieldError('isDisplay')} />
    </FormRow>
  </fieldset>
);
