import { createSignal } from 'solid-js';
import type { EventTypeValue } from '../../generated';
import { client } from '../../utils/client';

export const CreateForm = () => {
  const [title, setTitle] = createSignal('');
  const [date, setDate] = createSignal('');
  const [type, setType] = createSignal<EventTypeValue>(1);
  const [error, setError] = createSignal<string | null>(null);
  const [saving, setSaving] = createSignal(false);

  const submit = async (event: SubmitEvent) => {
    event.preventDefault();
    setSaving(true);
    setError(null);
    const response = await client.api.events.post({ title: title(), description: '', typeValue: type(), schedule: { type: 2, startOn: date() || null, endOn: null }, statusValue: null, isDisplay: true, venueIds: [], mediaIds: [], sources: [], performances: [], setlist: [] });
    setSaving(false);
    if (response.error || !response.data) {
      setError('保存に失敗しました');
      return;
    }
    window.location.href = `/events/${response.data.event.eventId}`;
  };

  return <form class="max-w-2xl space-y-4" onSubmit={submit}><fieldset class="fieldset"><label class="fieldset-label" for="title">タイトル</label><input id="title" class="input input-bordered w-full" required value={title()} onInput={e => setTitle(e.currentTarget.value)} /></fieldset><fieldset class="fieldset"><label class="fieldset-label" for="type">種別</label><select id="type" class="select select-bordered" value={String(type())} onChange={e => setType(Number(e.currentTarget.value) as EventTypeValue)}><option value="1">ライブ</option><option value="2">配信</option><option value="3">個展</option><option value="99">その他</option></select></fieldset><fieldset class="fieldset"><label class="fieldset-label" for="date">開催日</label><input id="date" type="date" class="input input-bordered" value={date()} onInput={e => setDate(e.currentTarget.value)} /></fieldset>{error() && <p class="text-error">{error()}</p>}<button class="btn btn-primary" disabled={saving()} type="submit">{saving() ? '保存中…' : '保存'}</button></form>;
};
