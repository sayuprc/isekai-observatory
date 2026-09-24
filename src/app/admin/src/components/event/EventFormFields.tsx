import type { EventStatusValue, EventTypeValue } from '../../generated';
import type { EventFormState } from './event-form';
import { EVENT_STATUS_OPTIONS, EVENT_TYPE_OPTIONS } from './event-options';
import { PerformanceEditor } from './PerformanceEditor';
import { SetlistEditor } from './SetlistEditor';

interface EventFormFieldsProps {
  form: EventFormState;
}

export const EventFormFields = (props: EventFormFieldsProps) => (
  <>
    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
      <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
      <label class="label" for="title">タイトル</label>
      <input
        id="title"
        class="input w-full"
        required
        maxLength={255}
        value={props.form.title()}
        onInput={e => props.form.setTitle(e.currentTarget.value)}
      />
      <label class="label mt-4" for="description">説明</label>
      <textarea
        id="description"
        class="textarea w-full"
        rows={4}
        value={props.form.description()}
        onInput={e => props.form.setDescription(e.currentTarget.value)}
      />
      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <label class="label" for="typeValue">種別</label>
          <select
            id="typeValue"
            class="select w-full"
            value={props.form.typeValue()}
            onChange={e => props.form.changeType(Number(e.currentTarget.value) as EventTypeValue)}
          >
            {EVENT_TYPE_OPTIONS.map(option => <option value={option.value}>{option.label}</option>)}
          </select>
        </div>
        <div>
          <label class="label" for="statusValue">状態</label>
          <select
            id="statusValue"
            class="select w-full"
            value={props.form.statusValue()}
            onChange={e => props.form.changeStatus(Number(e.currentTarget.value) as EventStatusValue)}
          >
            {EVENT_STATUS_OPTIONS.map(option => <option value={option.value}>{option.label}</option>)}
          </select>
        </div>
      </div>
      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <label class="label" for="startOn">開始日</label>
          <input
            id="startOn"
            type="date"
            class="input w-full"
            value={props.form.startOn()}
            onInput={e => props.form.setStartOn(e.currentTarget.value)}
          />
        </div>
        <div>
          <label class="label" for="endOn">終了日</label>
          <input
            id="endOn"
            type="date"
            class="input w-full"
            value={props.form.endOn()}
            onInput={e => props.form.setEndOn(e.currentTarget.value)}
          />
        </div>
      </div>
      <p class="mt-2 text-sm text-base-content/60">両方空は日付未定、開始日のみは単日、両方指定は期間です</p>
      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <label class="label" for="isDisplay">表示設定</label>
          <select
            id="isDisplay"
            class="select w-full"
            value={String(props.form.isDisplay())}
            onChange={e => props.form.setIsDisplay(e.currentTarget.value === 'true')}
          >
            <option value="true">表示する</option>
            <option value="false">表示しない</option>
          </select>
        </div>
      </div>
    </fieldset>

    <PerformanceEditor
      performances={props.form.performances()}
      onChange={props.form.updatePerformances}
      disabled={!props.form.canEditPerformances()}
    />
    <SetlistEditor
      setlist={props.form.setlist()}
      performances={props.form.performances()}
      onChange={props.form.updateSetlist}
      disabled={!props.form.canEditPerformances()}
      hidden={!props.form.canEditSetlist()}
    />
  </>
);
