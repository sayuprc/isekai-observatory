import { Elysia, t } from 'elysia';
import {
  songTagServiceCreateSongTag,
  songTagServiceDeleteSongTag,
  songTagServiceGetSongTag,
  songTagServiceListSongTags,
  songTagServiceSearchSongTags,
  songTagServiceUpdateSongTag,
} from '../../generated';
import type { PerPage, SongTagSearchSortBy, SortOrder } from '../../generated';
import { withAuthRetry } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const songTags = new Elysia({ prefix: '/song-tags' })
  .use(authGuard)
  .get('/', async ({ authSession }) => {
    return withAuthRetry(authSession, async (client) => {
      return resolveApiResponse(await songTagServiceListSongTags({ client }));
    });
  })
  .get(
    '/:songTagId',
    async ({ params: { songTagId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await songTagServiceGetSongTag({ client, path: { songTagId } }));
      });
    },
    {
      params: t.Object({
        songTagId: t.String(),
      }),
    },
  )
  .get(
    '/search',
    async ({ query, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await songTagServiceSearchSongTags({
            client,
            query: {
              name: query.name || undefined,
              sort: (query.sort ?? 'order_no') as SongTagSearchSortBy,
              order: (query.order ?? 'asc') as SortOrder,
              page: query.page ?? 1,
              per_page: (query.per_page ?? 50) as PerPage,
            },
          }),
        );
      });
    },
    {
      query: t.Object({
        name: t.Optional(t.String()),
        sort: t.Optional(t.Union([t.Literal('name'), t.Literal('order_no')])),
        order: t.Optional(t.Union([t.Literal('asc'), t.Literal('desc')])),
        page: t.Optional(t.Number()),
        per_page: t.Optional(t.Number()),
      }),
    },
  )
  .post(
    '/',
    async ({ body: { name }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await songTagServiceCreateSongTag({ client, body: { name } }));
      });
    },
    {
      body: t.Object({
        name: t.String(),
      }),
    },
  )
  .put(
    '/:songTagId',
    async ({ params: { songTagId }, body: { name, orderNo }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await songTagServiceUpdateSongTag({ client, path: { songTagId }, body: { name, orderNo } }),
        );
      });
    },
    {
      params: t.Object({
        songTagId: t.String(),
      }),
      body: t.Object({
        name: t.String(),
        orderNo: t.Number(),
      }),
    },
  )
  .delete(
    '/:songTagId',
    async ({ params: { songTagId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await songTagServiceDeleteSongTag({ client, path: { songTagId } }));
      });
    },
    {
      params: t.Object({
        songTagId: t.String(),
      }),
    },
  );
