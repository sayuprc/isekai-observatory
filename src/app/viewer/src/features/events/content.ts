import { eventRepository } from './api.js';
import { groupEvents, type EventGroups } from './group.js';
import type { Event } from './types.js';

// SSG のビルド日を基準にする。日付は日本時間の YYYY-MM-DD で比べる
const today = (): string => new Intl.DateTimeFormat('sv-SE', { timeZone: 'Asia/Tokyo' }).format(new Date());

export const eventContentRepository = {
  async all(): Promise<Event[]> {
    return eventRepository.all();
  },
  async grouped(): Promise<EventGroups> {
    return groupEvents(await eventRepository.all(), today());
  },
};
