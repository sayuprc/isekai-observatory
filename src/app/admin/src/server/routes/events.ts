import { Elysia, t } from 'elysia';
import {
  eventServiceCreateEvent,
  eventServiceDeleteEvent,
  eventServiceGetEvent,
  eventServiceSearchEvents,
  eventServiceUpdateEvent,
} from '../../generated';
import { withAuthRetry } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

const nullableString = () => t.Union([t.String(), t.Null()]);
const eventScheduleSchema = t.Object({
  startOn: nullableString(),
  endOn: nullableString(),
});
const performancePersonSchema = t.Object({ personId: t.String(), name: t.String(), creditName: nullableString(), orderNo: t.Number() });
const songPerformanceSchema = t.Object({ performanceId: t.String(), songId: t.String(), songTitle: t.String(), orderNo: t.Number(), coVocalists: t.Array(performancePersonSchema), isDisplay: t.Boolean() });
const eventBodySchema = t.Object({
  title: t.String({ minLength: 1, maxLength: 255 }),
  description: t.String(),
  typeValue: t.Union([t.Literal(1), t.Literal(2), t.Literal(3), t.Literal(99)]),
  schedule: eventScheduleSchema,
  statusValue: t.Union([t.Literal(1), t.Literal(2), t.Literal(3)]),
  isDisplay: t.Boolean(),
  venueIds: t.Array(t.String()),
  mediaIds: t.Array(t.String()),
  sources: t.Array(t.Object({ displayName: t.String({ minLength: 1 }), url: t.String({ minLength: 1 }), orderNo: t.Number() })),
  performances: t.Array(songPerformanceSchema),
  setlist: t.Array(t.Object({ setlistItemId: t.String(), orderNo: t.Number(), label: nullableString(), performances: t.Array(songPerformanceSchema) })),
});

export const events = new Elysia({ prefix: '/events' })
  .use(authGuard)
  .get('/search', async ({ query, authSession }) => withAuthRetry(authSession, async client => resolveApiResponse(await eventServiceSearchEvents({ client, query: { title: query.title || undefined, type: query.type ? Number(query.type) as 1 | 2 | 3 | 99 : undefined, status: query.status !== undefined && query.status !== '' ? Number(query.status) as 1 | 2 | 3 : undefined, is_display: query.is_display === '' ? undefined : query.is_display === 'true', sort: (query.sort ?? 'schedule') as 'schedule' | 'title', order: (query.order ?? 'asc') as 'asc' | 'desc', page: query.page ?? 1, per_page: query.per_page ?? 25 } }))), {
    query: t.Object({ title: t.Optional(t.String()), type: t.Optional(t.String()), status: t.Optional(t.String()), is_display: t.Optional(t.Union([t.Literal('true'), t.Literal('false'), t.Literal('')])), sort: t.Optional(t.Union([t.Literal('schedule'), t.Literal('title')])), order: t.Optional(t.Union([t.Literal('asc'), t.Literal('desc')])), page: t.Optional(t.Number()), per_page: t.Optional(t.Number()) }),
  })
  .get('/:eventId', async ({ params: { eventId }, authSession }) => withAuthRetry(authSession, async client => resolveApiResponse(await eventServiceGetEvent({ client, path: { eventId } }))), { params: t.Object({ eventId: t.String() }) })
  .post('/', async ({ body, authSession }) => withAuthRetry(authSession, async client => resolveApiResponse(await eventServiceCreateEvent({ client, body: body as never }))), { body: eventBodySchema })
  .put('/:eventId', async ({ params: { eventId }, body, authSession }) => withAuthRetry(authSession, async client => resolveApiResponse(await eventServiceUpdateEvent({ client, path: { eventId }, body: body as never }))), { params: t.Object({ eventId: t.String() }), body: eventBodySchema })
  .delete('/:eventId', async ({ params: { eventId }, authSession }) => withAuthRetry(authSession, async client => resolveApiResponse(await eventServiceDeleteEvent({ client, path: { eventId } }))), { params: t.Object({ eventId: t.String() }) });
