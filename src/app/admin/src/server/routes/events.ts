import { Elysia, t } from 'elysia';
import {
  eventServiceCreateEvent,
  eventServiceDeleteEvent,
  eventServiceGetEvent,
  eventServiceSearchEvents,
  eventServiceUpdateEvent,
} from '../../generated';
import type { EventSearchSortBy, EventStatusValue, EventTypeValue, PerPage, SortOrder } from '../../generated';
import { withAuthRetry } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

const nullableString = () => t.Union([t.String(), t.Null()]);
const eventScheduleSchema = t.Object({
  startOn: nullableString(),
  endOn: nullableString(),
});
const performancePersonSchema = t.Object({
  personId: t.String(),
  creditName: nullableString(),
  orderNo: t.Number(),
});
const songPerformanceSchema = t.Object({
  performanceId: t.String(),
  songId: t.String(),
  orderNo: t.Number(),
  coVocalists: t.Array(performancePersonSchema),
});
const eventSourceSchema = t.Object({
  displayName: t.String({ minLength: 1 }),
  url: t.String({ minLength: 1 }),
  orderNo: t.Number(),
});
const setlistItemSchema = t.Object({
  setlistItemId: t.String(),
  orderNo: t.Number(),
  label: nullableString(),
  performanceIds: t.Array(t.String()),
});
const eventBodySchema = t.Object({
  title: t.String({ minLength: 1, maxLength: 255 }),
  description: t.String(),
  typeValue: t.Union([t.Literal(1), t.Literal(2), t.Literal(3), t.Literal(99)]),
  schedule: eventScheduleSchema,
  statusValue: t.Union([t.Literal(1), t.Literal(2), t.Literal(3)]),
  isDisplay: t.Boolean(),
  venueIds: t.Array(t.String()),
  mediaIds: t.Array(t.String()),
  sources: t.Array(eventSourceSchema),
  performances: t.Array(songPerformanceSchema),
  setlist: t.Array(setlistItemSchema),
});

export const events = new Elysia({ prefix: '/events' })
  .use(authGuard)
  .get(
    '/search',
    async ({ query, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await eventServiceSearchEvents({
            client,
            query: {
              title: query.title || undefined,
              type: query.type ? Number(query.type) as EventTypeValue : undefined,
              status: query.status ? Number(query.status) as EventStatusValue : undefined,
              is_display: query.is_display,
              sort: (query.sort ?? 'schedule') as EventSearchSortBy,
              order: (query.order ?? 'asc') as SortOrder,
              page: query.page ?? 1,
              per_page: (query.per_page ?? 25) as PerPage,
            },
          }),
        );
      });
    },
    {
      query: t.Object({
        title: t.Optional(t.String()),
        type: t.Optional(t.Union([t.Literal('1'), t.Literal('2'), t.Literal('3'), t.Literal('99'), t.Literal('')])),
        status: t.Optional(t.Union([t.Literal('1'), t.Literal('2'), t.Literal('3'), t.Literal('')])),
        is_display: t.Optional(t.Boolean()),
        sort: t.Optional(t.Union([t.Literal('schedule'), t.Literal('title')])),
        order: t.Optional(t.Union([t.Literal('asc'), t.Literal('desc')])),
        page: t.Optional(t.Number()),
        per_page: t.Optional(t.Number()),
      }),
    },
  )
  .get(
    '/:eventId',
    async ({ params: { eventId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await eventServiceGetEvent({ client, path: { eventId } }));
      });
    },
    { params: t.Object({ eventId: t.String() }) },
  )
  .post(
    '/',
    async ({ body, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await eventServiceCreateEvent({ client, body }));
      });
    },
    { body: eventBodySchema },
  )
  .put(
    '/:eventId',
    async ({ params: { eventId }, body, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await eventServiceUpdateEvent({ client, path: { eventId }, body }));
      });
    },
    {
      params: t.Object({ eventId: t.String() }),
      body: eventBodySchema,
    },
  )
  .delete(
    '/:eventId',
    async ({ params: { eventId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await eventServiceDeleteEvent({ client, path: { eventId } }));
      });
    },
    { params: t.Object({ eventId: t.String() }) },
  );
