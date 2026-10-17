import { Elysia, t } from 'elysia';
import {
  tagServiceCreateTag,
  tagServiceDeleteTag,
  tagServiceGetTag,
  tagServiceListTags,
  tagServiceSearchTags,
  tagServiceUpdateTag,
} from '../../generated';
import type { PerPage, TagSearchSortBy, SortOrder } from '../../generated';
import { requestWithAuth } from '../client';
import { authGuard } from '../middleware';

export const tags = new Elysia({ prefix: '/tags' })
  .use(authGuard)
  .get('/', async ({ authSession }) => {
    return requestWithAuth(authSession, (client) => tagServiceListTags({ client }));
  })
  .get(
    '/:tagId',
    async ({ params: { tagId }, authSession }) => {
      return requestWithAuth(authSession, (client) => tagServiceGetTag({ client, path: { tagId } }));
    },
    {
      params: t.Object({
        tagId: t.String(),
      }),
    },
  )
  .get(
    '/search',
    async ({ query, authSession }) => {
      return requestWithAuth(authSession, (client) =>
        tagServiceSearchTags({
          client,
          query: {
            name: query.name || undefined,
            sort: (query.sort ?? 'order_no') as TagSearchSortBy,
            order: (query.order ?? 'asc') as SortOrder,
            page: query.page ?? 1,
            per_page: (query.per_page ?? 50) as PerPage,
          },
        }),
      );
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
      return requestWithAuth(authSession, (client) => tagServiceCreateTag({ client, body: { name } }));
    },
    {
      body: t.Object({
        name: t.String(),
      }),
    },
  )
  .put(
    '/:tagId',
    async ({ params: { tagId }, body: { name, orderNo }, authSession }) => {
      return requestWithAuth(authSession, (client) =>
        tagServiceUpdateTag({ client, path: { tagId }, body: { name, orderNo } }),
      );
    },
    {
      params: t.Object({
        tagId: t.String(),
      }),
      body: t.Object({
        name: t.String(),
        orderNo: t.Number(),
      }),
    },
  )
  .delete(
    '/:tagId',
    async ({ params: { tagId }, authSession }) => {
      return requestWithAuth(authSession, (client) => tagServiceDeleteTag({ client, path: { tagId } }));
    },
    {
      params: t.Object({
        tagId: t.String(),
      }),
    },
  );
