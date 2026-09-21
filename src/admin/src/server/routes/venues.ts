import { Elysia, t } from 'elysia';
import {
  venueServiceCreateVenue,
  venueServiceDeleteVenue,
  venueServiceGetVenue,
  venueServiceSearchVenues,
  venueServiceUpdateVenue,
} from '../../generated';
import type { PerPage, SortOrder, VenueKindValue, VenueSearchSortBy } from '../../generated';
import { withAuthRetry } from '../client';
import { ApiError, resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

const venueNameSchema = t.String({ minLength: 1, maxLength: 255 });

const validateVenueName = (name: string): void => {
  if (name.trim().normalize('NFC').length === 0) {
    throw new ApiError(422, {
      code: 'validation_failed',
      message: '入力内容に誤りがあります',
      details: [{ field: 'name', message: '開催先名を入力してください' }],
    });
  }

  if ([...name].some((character) => {
    const codePoint = character.codePointAt(0) ?? 0;
    return codePoint <= 0x1f || codePoint === 0x7f;
  })) {
    throw new ApiError(422, {
      code: 'validation_failed',
      message: '入力内容に誤りがあります',
      details: [{ field: 'name', message: '開催先名に改行や制御文字は含められません' }],
    });
  }
};

export const venues = new Elysia({ prefix: '/venues' })
  .use(authGuard)
  .get(
    '/search',
    async ({ query, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await venueServiceSearchVenues({
            client,
            query: {
              name: query.name || undefined,
              kind: query.kind ? Number(query.kind) as VenueKindValue : undefined,
              sort: (query.sort ?? 'name') as VenueSearchSortBy,
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
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await venueServiceGetVenue({ client, path: { venueId } }));
      });
    },
    { params: t.Object({ venueId: t.String() }) },
  )
  .post(
    '/',
    async ({ body, authSession }) => {
      validateVenueName(body.name);

      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await venueServiceCreateVenue({ client, body }));
      });
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
      validateVenueName(body.name);

      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await venueServiceUpdateVenue({ client, path: { venueId }, body }));
      });
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
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await venueServiceDeleteVenue({ client, path: { venueId } }));
      });
    },
    { params: t.Object({ venueId: t.String() }) },
  );
