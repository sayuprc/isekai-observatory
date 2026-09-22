import { eventRepository } from './api.js';
import type { Event } from './types.js';

const sortEvents = (events: Event[]): Event[] => {
  const now = Date.now();
  const timestamp = (event: Event): number | null => {
    const value = event.schedule.startOn;
    if (!value) return null;
    const parsed = new Date(value).getTime();
    return Number.isNaN(parsed) ? null : parsed;
  };

  return [...events].sort((left, right) => {
    const leftTime = timestamp(left);
    const rightTime = timestamp(right);
    if (leftTime === null && rightTime === null) return left.title.localeCompare(right.title, 'ja');
    if (leftTime === null) return -1;
    if (rightTime === null) return 1;
    const leftFuture = leftTime >= now;
    const rightFuture = rightTime >= now;
    if (leftFuture !== rightFuture) return leftFuture ? -1 : 1;
    return leftFuture ? leftTime - rightTime : rightTime - leftTime;
  });
};

export const eventContentRepository = {
  async all(): Promise<Event[]> {
    return sortEvents(await eventRepository.all());
  },
};
