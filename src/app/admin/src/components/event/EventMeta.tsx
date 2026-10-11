import { EVENT_STATUS_NAMES, EVENT_TYPE_NAMES } from '../../utils/enum-names';
import { MetaChip } from '../EntityHeader';
import type { EventFormState } from './event-form';
import { formatSchedule } from './event-options';

// EntityHeader に出す、入力中の値から作るイベントの概要
export const EventMeta = (props: { form: EventFormState }) => (
  <>
    <MetaChip>{EVENT_TYPE_NAMES[props.form.typeValue()]}</MetaChip>
    <MetaChip>
      <span class="font-mono">
        {formatSchedule({ startOn: props.form.startOn() || null, endOn: props.form.endOn() || null })}
      </span>
    </MetaChip>
    <MetaChip>{EVENT_STATUS_NAMES[props.form.statusValue()]}</MetaChip>
    <MetaChip dot={props.form.isDisplay() ? 'success' : 'muted'}>{props.form.isDisplay() ? '公開' : '非公開'}</MetaChip>
  </>
);
