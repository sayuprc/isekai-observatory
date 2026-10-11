import { Elysia, t } from 'elysia';
import {
  venueServiceCreateVenue,
  venueServiceDeleteVenue,
  venueServiceGetVenue,
  venueServiceSearchVenues,
  venueServiceUpdateVenue,
} from '../../generated';
import type { PerPage, SortOrder, VenueKindValue, VenueSearchSortBy } from '../../generated';
import { validateVenueName } from '../../schemas/venue';
import { requestWithAuth } from '../client';
import { ApiError } from '../errors';
import { authGuard } from '../middleware';

const venueNameSchema = t.String({ minLength: 1, maxLength: 255 });

const assertVenueName = (name: string): void => {
  const message = validateVenueName(name);

  if (message) {
    throw new ApiError(422, {
      code: 'validation_failed',
      message: '入力内容に誤りがあります',
      details: [{ field: 'name', message }],
    });
  }
};

export const venues = new Elysia({ prefix: '/venues' })
  .use(authGuard)
  .get(
    '/search',
    async ({ query, authSession }) => {
      return requestWithAuth(authSession, (client) =>
        venueServiceSearchVenues({
          client,
          query: {
            name: query.name || undefined,
            kind: query.kind ? (Number(query.kind) as VenueKindValue) : undefined,
            sort: (query.sort ?? 'name') as VenueSearchSortBy,
            order: (query.order ?? 'asc') as SortOrder,
            page: query.page ?? 1,
            per_page: (query.per_page ?? 25) as PerPage,
          },
        }),
      );
    },
    {
      query: t.Object({
        name: t.String(),
        kind: t.Optional(t.Union([t.Literal('1'), t.Literal('2'), t.Literal('')])),
        sort: t.Optional(t.Literal('name')),
        order: t.Optional(t.Union([t.Literal('asc'), t.Literal('desc')])),
        page: t.Optional(t.Number()),
        per_page: t.Optional(t.Number()),
      }),
    },
  )
  .get(
    '/:venueId',
    async ({ params: { venueId }, authSession }) => {
      return requestWithAuth(authSession, (client) => venueServiceGetVenue({ client, path: { venueId } }));
    },
    { params: t.Object({ venueId: t.String() }) },
  )
  .post(
    '/',
    async ({ body, authSession }) => {
      assertVenueName(body.name);

      return requestWithAuth(authSession, (client) => venueServiceCreateVenue({ client, body }));
    },
    {
      body: t.Object({
        name: venueNameSchema,
        kind: t.Union([t.Literal(1), t.Literal(2)]),
      }),
    },
  )
  .put(
    '/:venueId',
    async ({ params: { venueId }, body, authSession }) => {
      assertVenueName(body.name);

      return requestWithAuth(authSession, (client) => venueServiceUpdateVenue({ client, path: { venueId }, body }));
    },
    {
      params: t.Object({ venueId: t.String() }),
      body: t.Object({
        name: venueNameSchema,
        kind: t.Union([t.Literal(1), t.Literal(2)]),
      }),
    },
  )
  .delete(
    '/:venueId',
    async ({ params: { venueId }, authSession }) => {
      return requestWithAuth(authSession, (client) => venueServiceDeleteVenue({ client, path: { venueId } }));
    },
    { params: t.Object({ venueId: t.String() }) },
  );
