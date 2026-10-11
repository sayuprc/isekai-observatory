import { getCollection } from 'astro:content';
import { allFromCollection } from '../../shared/content/collection';
import type { Indexed } from '../../shared/content/indexed';
import { groupEvents, upcomingEvents, type EventGroups } from './group.js';
import type { Event } from './types.js';

// SSG のビルド日を基準にする。日付は日本時間の YYYY-MM-DD で比べる
const today = (): string => new Intl.DateTimeFormat('sv-SE', { timeZone: 'Asia/Tokyo' }).format(new Date());

async function all(): Promise<Event[]> {
  return allFromCollection<Indexed<Event>>(await getCollection('events'));
}

export const eventContentRepository = {
  all,
  async grouped(): Promise<EventGroups> {
    return groupEvents(await all());
  },
  async upcoming(): Promise<Event[]> {
    return upcomingEvents(await all(), today());
  },
};
