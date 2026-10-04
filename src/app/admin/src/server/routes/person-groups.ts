import { Elysia, t } from 'elysia';
import {
  personGroupServiceCreatePersonGroup,
  personGroupServiceDeletePersonGroup,
  personGroupServiceGetPersonGroup,
  personGroupServiceSearchPersonGroups,
  personGroupServiceUpdatePersonGroup,
} from '../../generated';
import type { PerPage } from '../../generated';
import { requestWithAuth } from '../client';
import { authGuard } from '../middleware';

const memberSchema = t.Object({
  personId: t.String(),
  orderNo: t.Number(),
});

export const personGroups = new Elysia({ prefix: '/person-groups' })
  .use(authGuard)
  .get(
    '/search',
    async ({ query, authSession }) => {
      return requestWithAuth(authSession, (client) =>
        personGroupServiceSearchPersonGroups({
          client,
          query: {
            name: query.name || undefined,
            page: query.page ?? 1,
            per_page: (query.per_page ?? 25) as PerPage,
          },
        }),
      );
    },
    {
      query: t.Object({
        name: t.String(),
        page: t.Optional(t.Number()),
        per_page: t.Optional(t.Number()),
      }),
    },
  )
  .get(
    '/:personGroupId',
    async ({ params: { personGroupId }, authSession }) => {
      return requestWithAuth(authSession, (client) =>
        personGroupServiceGetPersonGroup({ client, path: { personGroupId } }),
      );
    },
    {
      params: t.Object({
        personGroupId: t.String(),
      }),
    },
  )
  .post(
    '/',
    async ({ body: { name, members }, authSession }) => {
      return requestWithAuth(authSession, (client) =>
        personGroupServiceCreatePersonGroup({ client, body: { name, members } }),
      );
    },
    {
      body: t.Object({
        name: t.String(),
        members: t.Array(memberSchema),
      }),
    },
  )
  .put(
    '/:personGroupId',
    async ({ params: { personGroupId }, body: { name, members }, authSession }) => {
      return requestWithAuth(authSession, (client) =>
        personGroupServiceUpdatePersonGroup({ client, path: { personGroupId }, body: { name, members } }),
      );
    },
    {
      params: t.Object({
        personGroupId: t.String(),
      }),
      body: t.Object({
        name: t.String(),
        members: t.Array(memberSchema),
      }),
    },
  )
  .delete(
    '/:personGroupId',
    async ({ params: { personGroupId }, authSession }) => {
      // 削除は本文を返さない
      await requestWithAuth(authSession, (client) =>
        personGroupServiceDeletePersonGroup({ client, path: { personGroupId } }),
      );
    },
    {
      params: t.Object({
        personGroupId: t.String(),
      }),
    },
  );
