import { createSignal } from 'solid-js';
import type { EventStatusValue, EventTypeValue } from '../../generated';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { FormError } from '../FormError';
import {
  allowsPerformances,
  allowsSetlist,
  toPerformancesPayload,
  toSetlistPayload,
  validateSetlistItems,
  type PerformanceForm,
  type SetlistItemForm,
} from './performance-form';
import { PerformanceEditor } from './PerformanceEditor';
import { SetlistEditor } from './SetlistEditor';

const EVENT_TYPES: Array<{ value: EventTypeValue; label: string }> = [
  { value: 1, label: 'ライブ' },
  { value: 2, label: '配信' },
  { value: 3, label: '個展' },
  { value: 99, label: 'その他' },
];

export const CreateForm = () => {
  const { formError, clearErrors, setFormError } = createFormErrors();
  const [title, setTitle] = createSignal('');
  const [description, setDescription] = createSignal('');
  const [startOn, setStartOn] = createSignal('');
  const [endOn, setEndOn] = createSignal('');
  const [type, setType] = createSignal<EventTypeValue>(1);
  const [status, setStatus] = createSignal<EventStatusValue>(1);
  const [isDisplay, setIsDisplay] = createSignal(true);
  const [performances, setPerformances] = createSignal<PerformanceForm[]>([]);
  const [setlist, setSetlist] = createSignal<SetlistItemForm[]>([]);
  const [saving, setSaving] = createSignal(false);

  const submit = async (event: SubmitEvent) => {
    event.preventDefault();
    clearErrors();
    setSaving(true);

    const nextPerformances = allowsPerformances(status()) ? performances() : [];
    const nextSetlist = allowsPerformances(status()) && allowsSetlist(type()) ? setlist() : [];
    const setlistError = validateSetlistItems(nextSetlist);
    if (setlistError) {
      setFormError(setlistError);
      setSaving(false);
      return;
    }

    const response = await client.api.events.post({
      title: title(),
      description: description(),
      typeValue: type(),
      schedule: {
        startOn: startOn() || null,
        endOn: endOn() || null,
      },
      statusValue: status(),
      isDisplay: isDisplay(),
      venueIds: [],
      mediaIds: [],
      sources: [],
      performances: toPerformancesPayload(nextPerformances),
      setlist: toSetlistPayload(nextSetlist, nextPerformances),
    });
    setSaving(false);
    if (response.error || !response.data) {
      setFormError('保存に失敗しました');
      return;
    }
    window.location.href = `/events/${response.data.event.eventId}`;
  };

  return (
    <form class="max-w-4xl space-y-6" onSubmit={submit}>
      <FormError message={formError()} onClose={clearErrors} />
      <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
        <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
        <label class="label" for="title">タイトル</label>
        <input id="title" class="input w-full" required value={title()} onInput={e => setTitle(e.currentTarget.value)} />
        <label class="label mt-4" for="description">説明</label>
        <textarea id="description" class="textarea w-full" rows={4} value={description()} onInput={e => setDescription(e.currentTarget.value)} />
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label class="label" for="type">種別</label>
            <select
              id="type"
              class="select w-full"
              value={String(type())}
              onChange={(e) => {
                const next = Number(e.currentTarget.value) as EventTypeValue;
                setType(next);
                if (!allowsSetlist(next)) {
                  setSetlist([]);
                }
              }}
            >
              {EVENT_TYPES.map(option => <option value={option.value}>{option.label}</option>)}
            </select>
          </div>
          <div>
            <label class="label" for="status">状態</label>
            <select
              id="status"
              class="select w-full"
              value={String(status())}
              onChange={(e) => {
                const next = Number(e.currentTarget.value) as EventStatusValue;
                setStatus(next);
                if (!allowsPerformances(next)) {
                  setPerformances([]);
                  setSetlist([]);
                }
              }}
            >
              <option value="1">通常</option>
              <option value="2">延期</option>
              <option value="3">中止</option>
            </select>
          </div>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label class="label" for="startOn">開始日</label>
            <input id="startOn" type="date" class="input w-full" value={startOn()} onInput={e => setStartOn(e.currentTarget.value)} />
          </div>
          <div>
            <label class="label" for="endOn">終了日</label>
            <input id="endOn" type="date" class="input w-full" value={endOn()} onInput={e => setEndOn(e.currentTarget.value)} />
          </div>
        </div>
        <p class="mt-2 text-sm text-base-content/60">両方空は日付未定、開始日のみは単日、両方指定は期間です</p>
        <div>
          <label class="label" for="isDisplay">表示設定</label>
          <select id="isDisplay" class="select w-full" value={String(isDisplay())} onChange={e => setIsDisplay(e.currentTarget.value === 'true')}>
            <option value="true">表示する</option>
            <option value="false">表示しない</option>
          </select>
        </div>
      </fieldset>

      <PerformanceEditor
        performances={performances()}
        onChange={(updater) => {
          setPerformances((prev) => {
            const next = updater(prev);
            const ids = new Set(next.map(performance => performance.performanceId));
            setSetlist(items =>
              items.map(item => ({
                ...item,
                performanceIds: item.performanceIds.filter(id => ids.has(id)),
              })));
            return next;
          });
        }}
        disabled={!allowsPerformances(status())}
      />
      <SetlistEditor
        setlist={setlist()}
        performances={performances()}
        onChange={updater => setSetlist(updater)}
        disabled={!allowsPerformances(status())}
        hidden={!allowsSetlist(type())}
      />

      <button class="btn btn-primary" disabled={saving()} type="submit">{saving() ? '保存中…' : '保存'}</button>
    </form>
  );
};
