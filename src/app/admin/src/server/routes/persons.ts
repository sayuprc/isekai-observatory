import { Elysia, t } from 'elysia';
import {
  personServiceCreatePerson,
  personServiceDeletePerson,
  personServiceGetPerson,
  personServiceSearchPersons,
  personServiceUpdatePerson,
} from '../../generated';
import type { PerPage, PersonSearchSortBy, SortOrder } from '../../generated';
import { requestWithAuth } from '../client';
import { authGuard } from '../middleware';

export const persons = new Elysia({ prefix: '/persons' })
  .use(authGuard)
  .get(
    '/search',
    async ({ query, authSession }) => {
      return requestWithAuth(authSession, (client) =>
        personServiceSearchPersons({
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
      return requestWithAuth(authSession, (client) => personServiceGetPerson({ client, path: { personId } }));
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
      return requestWithAuth(authSession, (client) => personServiceCreatePerson({ client, body: { name } }));
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
      return requestWithAuth(authSession, (client) =>
        personServiceUpdatePerson({ client, path: { personId }, body: { name, orderNo } }),
      );
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
      // 削除は本文を返さない
      await requestWithAuth(authSession, (client) => personServiceDeletePerson({ client, path: { personId } }));
    },
    {
      params: t.Object({
        personId: t.String(),
      }),
    },
  );
