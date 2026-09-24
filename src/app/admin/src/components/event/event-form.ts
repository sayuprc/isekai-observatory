import { createSignal } from 'solid-js';
import type { Event, EventCreateRequest, EventStatusValue, EventTypeValue } from '../../generated';
import { normalizeDateValue } from '../../utils/date';
import { allowsPerformances, allowsSetlist } from './event-options';
import {
  toPerformanceForms,
  toPerformancesPayload,
  toSetlistItemForms,
  toSetlistPayload,
  validateSetlistItems,
  type PerformanceForm,
  type SetlistItemForm,
} from './performance-form';

export type EventRequestBody = EventCreateRequest;

type EventLinks = Pick<EventRequestBody, 'venueIds' | 'mediaIds' | 'sources'>;

export const createEventForm = (event?: Event) => {
  const [title, setTitle] = createSignal(event?.title ?? '');
  const [description, setDescription] = createSignal(event?.description ?? '');
  const [startOn, setStartOn] = createSignal(normalizeDateValue(event?.schedule.startOn));
  const [endOn, setEndOn] = createSignal(normalizeDateValue(event?.schedule.endOn));
  const [typeValue, setTypeValue] = createSignal<EventTypeValue>(event?.typeValue ?? 1);
  const [statusValue, setStatusValue] = createSignal<EventStatusValue>(event?.statusValue ?? 1);
  const [isDisplay, setIsDisplay] = createSignal(event?.isDisplay ?? true);
  const [performances, setPerformances] = createSignal<PerformanceForm[]>(
    toPerformanceForms(event?.performances ?? []),
  );
  const [setlist, setSetlist] = createSignal<SetlistItemForm[]>(toSetlistItemForms(event?.setlist ?? []));

  const canEditPerformances = () => allowsPerformances(statusValue());
  const canEditSetlist = () => allowsSetlist(typeValue());

  // 種別と状態を変えたら、持てなくなった子要素を消して保存時の拒否を防ぐ
  const changeType = (next: EventTypeValue) => {
    setTypeValue(next);
    if (!allowsSetlist(next)) {
      setSetlist([]);
    }
  };

  const changeStatus = (next: EventStatusValue) => {
    setStatusValue(next);
    if (!allowsPerformances(next)) {
      setPerformances([]);
      setSetlist([]);
    }
  };

  // 削除された楽曲披露への参照をセットリストから外す
  const updatePerformances = (updater: (prev: PerformanceForm[]) => PerformanceForm[]) => {
    const next = updater(performances());
    const ids = new Set(next.map(performance => performance.performanceId));
    setPerformances(next);
    setSetlist(items =>
      items.map(item => ({
        ...item,
        performanceIds: item.performanceIds.filter(id => ids.has(id)),
      })));
  };

  const updateSetlist = (updater: (prev: SetlistItemForm[]) => SetlistItemForm[]) => {
    setSetlist(updater);
  };

  const validate = (): string | null => validateSetlistItems(setlist());

  const toRequestBody = (links: EventLinks): EventRequestBody => ({
    title: title(),
    description: description(),
    typeValue: typeValue(),
    schedule: {
      startOn: startOn() || null,
      endOn: endOn() || null,
    },
    statusValue: statusValue(),
    isDisplay: isDisplay(),
    ...links,
    performances: toPerformancesPayload(performances()),
    setlist: toSetlistPayload(setlist(), performances()),
  });

  return {
    title,
    setTitle,
    description,
    setDescription,
    startOn,
    setStartOn,
    endOn,
    setEndOn,
    typeValue,
    changeType,
    statusValue,
    changeStatus,
    isDisplay,
    setIsDisplay,
    performances,
    updatePerformances,
    setlist,
    updateSetlist,
    canEditPerformances,
    canEditSetlist,
    validate,
    toRequestBody,
  };
};

export type EventFormState = ReturnType<typeof createEventForm>;
