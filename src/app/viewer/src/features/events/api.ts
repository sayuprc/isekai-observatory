import { eventServiceListEvents } from '../../generated/sdk.gen.js';
import { apiClient } from '../../shared/api/client.js';
import { collectAll } from '../../shared/api/collect-all.js';
import type { Event } from './types.js';

async function all(): Promise<Event[]> {
  return collectAll(
    async (cursor, limit) => {
      const { data, error, response } = await eventServiceListEvents({ client: apiClient, query: { cursor, limit } });
      if (!data) throw new Error(`eventServiceListEvents failed: HTTP ${response.status} ${JSON.stringify(error)}`);
      return data;
    },
    data => ({ items: data.events, nextCursor: data.nextCursor }),
  );
}

export const eventRepository = { all };
