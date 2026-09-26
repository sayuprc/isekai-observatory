import { eventServiceListEvents } from '../../generated/sdk.gen.js';
import { apiClient } from '../../shared/api/client.js';
import { collectAll } from '../../shared/api/collect-all.js';
import { requireData } from '../../shared/api/require-data.js';
import type { Event } from './types.js';

async function all(): Promise<Event[]> {
  return collectAll(
    async (cursor, limit) => {
      const result = await eventServiceListEvents({ client: apiClient, query: { limit, cursor } });
      return requireData('eventServiceListEvents', result);
    },
    (data) => ({ items: data.events, nextCursor: data.nextCursor }),
  );
}

export const eventRepository = {
  all,
};
