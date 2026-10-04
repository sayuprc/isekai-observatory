import { MetaChip } from '../EntityHeader';
import type { EventFormState } from './event-form';
import { EVENT_STATUS_OPTIONS, EVENT_TYPE_OPTIONS, formatSchedule } from './event-options';

// EntityHeader に出す、入力中の値から作るイベントの概要
export const EventMeta = (props: { form: EventFormState }) => (
  <>
    <MetaChip>{EVENT_TYPE_OPTIONS.find((option) => option.value === props.form.typeValue())?.label}</MetaChip>
    <MetaChip>
      <span class="font-mono">
        {formatSchedule({ startOn: props.form.startOn() || null, endOn: props.form.endOn() || null })}
      </span>
    </MetaChip>
    <MetaChip>{EVENT_STATUS_OPTIONS.find((option) => option.value === props.form.statusValue())?.label}</MetaChip>
    <MetaChip dot={props.form.isDisplay() ? 'success' : 'muted'}>{props.form.isDisplay() ? '公開' : '非公開'}</MetaChip>
  </>
);
