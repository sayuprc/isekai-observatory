import type { Event as EventModel, EventStatusValue, EventTypeValue } from '../../generated/types.gen.js';
import { dottedDate } from '../../shared/date';

export type Event = EventModel;

export const EVENT_TYPE_NAMES: Record<EventTypeValue, string> = {
  1: 'ライブ',
  2: '配信',
  3: '展覧会',
  4: 'ラジオ',
  99: 'その他',
};

export const EVENT_STATUS = {
  normal: 1,
  postponed: 2,
  cancelled: 3,
} as const satisfies Record<string, EventStatusValue>;

// 通常開催は状態を出さず、延期と中止だけを表示する
const EVENT_STATUS_NAMES: Record<EventStatusValue, string | null> = {
  [EVENT_STATUS.normal]: null,
  [EVENT_STATUS.postponed]: '延期',
  [EVENT_STATUS.cancelled]: '中止',
};

export function eventDate(event: Event): string | null {
  const { startOn, endOn } = event.schedule;
  if (!startOn) return null;
  return endOn ? `${dottedDate(startOn)}〜${dottedDate(endOn)}` : dottedDate(startOn);
}

const WEEKDAYS = ['日', '月', '火', '水', '木', '金', '土'];

const weekdayOf = (value: string): string => WEEKDAYS[new Date(`${value}T12:00:00Z`).getUTCDay()] ?? '';

// 詳細の開催時期。年月日と曜日を出し、期間は開始日と終了日を別の要素で返す
export function eventDetailDate(event: Event): string[] | null {
  const { startOn, endOn } = event.schedule;
  if (!startOn) return null;

  const format = (value: string) => `${dottedDate(value)} (${weekdayOf(value)})`;
  return endOn ? [format(startOn), format(endOn)] : [format(startOn)];
}

// 一覧の日付列。単日は曜日、期間は終了日を添える
// 年は一覧では見出しに出すので省き、年見出しのないホームでは付ける
export function eventDateColumn(event: Event, withYear = false): { main: string; sub: string } | null {
  const { startOn, endOn } = event.schedule;
  if (!startOn) return null;

  const monthDay = (value: string) => dottedDate(value.slice(5));
  const main = withYear ? dottedDate(startOn) : monthDay(startOn);

  // 終了日は開始日と同じ年なら月日だけにする
  if (endOn) {
    const sameYear = startOn.slice(0, 4) === endOn.slice(0, 4);
    return { main, sub: `– ${sameYear ? monthDay(endOn) : dottedDate(endOn)}` };
  }

  return { main, sub: weekdayOf(startOn) };
}

export function eventStatusName(statusValue: EventStatusValue): string | null {
  return EVENT_STATUS_NAMES[statusValue];
}

type CoVocalist = Event['performances'][number]['coVocalists'][number];

// グループとして出演した共演者は、最初のメンバーの位置にグループ名 1 つでまとめる
export function coVocalistNames(coVocalists: CoVocalist[]): string[] {
  const names: string[] = [];
  const seenGroupIds = new Set<string>();

  for (const person of coVocalists) {
    if (person.personGroup === null) {
      names.push(person.creditName ?? person.name);
      continue;
    }

    if (!seenGroupIds.has(person.personGroup.personGroupId)) {
      seenGroupIds.add(person.personGroup.personGroupId);
      names.push(person.personGroup.name);
    }
  }

  return names;
}
