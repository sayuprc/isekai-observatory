import { Elysia, t } from 'elysia';
import {
  personServiceCreatePerson,
  personServiceDeletePerson,
  personServiceGetPerson,
  personServiceSearchPersons,
  personServiceUpdatePerson,
} from '../../generated';
import type { PerPage, PersonSearchSortBy, SortOrder } from '../../generated';
import { withAuthRetry } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const persons = new Elysia({ prefix: '/persons' })
  .use(authGuard)
  .get(
    '/search',
    async ({ query, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await personServiceSearchPersons({
            client,
            query: {
              name: query.name || undefined,
              sort: (query.sort ?? 'order_no') as PersonSearchSortBy,
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
        sort: t.Optional(t.Union([t.Literal('name'), t.Literal('order_no')])),
        order: t.Optional(t.Union([t.Literal('asc'), t.Literal('desc')])),
        page: t.Optional(t.Number()),
        per_page: t.Optional(t.Number()),
      }),
    },
  )
  .get(
    '/:personId',
    async ({ params: { personId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await personServiceGetPerson({ client, path: { personId } }));
      });
    },
    {
      params: t.Object({
        personId: t.String(),
      }),
    },
  )
  .post(
    '/',
    async ({ body: { name }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await personServiceCreatePerson({ client, body: { name } }));
      });
    },
    {
      body: t.Object({
        name: t.String(),
      }),
    },
  )
  .put(
    '/:personId',
    async ({ params: { personId }, body: { name, orderNo }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await personServiceUpdatePerson({ client, path: { personId }, body: { name, orderNo } }),
        );
      });
    },
    {
      params: t.Object({
        personId: t.String(),
      }),
      body: t.Object({
        name: t.String(),
        orderNo: t.Number(),
      }),
    },
  )
  .delete(
    '/:personId',
    async ({ params: { personId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        resolveApiResponse(await personServiceDeletePerson({ client, path: { personId } }));
      });
    },
    {
      params: t.Object({
        personId: t.String(),
      }),
    },
  );
