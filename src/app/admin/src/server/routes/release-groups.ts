import { Elysia, t } from 'elysia';
import {
  releaseGroupServiceCreateReleaseGroup,
  releaseGroupServiceDeleteReleaseGroup,
  releaseGroupServiceGetReleaseGroup,
  releaseGroupServiceSearchReleaseGroups,
  releaseGroupServiceUpdateReleaseGroup,
} from '../../generated';
import type { PerPage, ReleaseGroupSearchSortBy, ReleaseGroupTypeValue, SortOrder } from '../../generated';
import { requestWithAuth } from '../client';
import { authGuard } from '../middleware';

export const releaseGroups = new Elysia({ prefix: '/release-groups' })
  .use(authGuard)
  .post(
    '/',
    async ({ body, authSession }) => {
      return requestWithAuth(authSession, (client) =>
        releaseGroupServiceCreateReleaseGroup({
          client,
          body: {
            ...body,
            typeValue: body.typeValue as ReleaseGroupTypeValue,
          },
        }),
      );
    },
    {
      body: t.Object({
        title: t.String(),
        typeValue: t.Numeric(),
        description: t.String(),
        isDisplay: t.Boolean(),
        orderNo: t.Number(),
      }),
    },
  )
  .get(
    '/search',
    async ({ query, authSession }) => {
      return requestWithAuth(authSession, (client) =>
        releaseGroupServiceSearchReleaseGroups({
          client,
          query: {
            title: query.title || undefined,
            type: query.type as ReleaseGroupTypeValue | undefined,
            is_display: query.is_display,
            sort: (query.sort ?? 'first_released_on') as ReleaseGroupSearchSortBy,
            order: (query.order ?? 'desc') as SortOrder,
            page: query.page ?? 1,
            per_page: (query.per_page ?? 25) as PerPage,
          },
        }),
      );
    },
    {
      query: t.Object({
        title: t.Optional(t.String()),
        type: t.Optional(t.Union([t.Literal('1'), t.Literal('2'), t.Literal('3'), t.Literal('99')])),
        is_display: t.Optional(t.Boolean()),
        sort: t.Optional(t.Union([t.Literal('first_released_on'), t.Literal('title')])),
        order: t.Optional(t.Union([t.Literal('asc'), t.Literal('desc')])),
        page: t.Optional(t.Number()),
        per_page: t.Optional(t.Number()),
      }),
    },
  )
  .get(
    '/:releaseGroupId',
    async ({ params: { releaseGroupId }, authSession }) => {
      return requestWithAuth(authSession, (client) =>
        releaseGroupServiceGetReleaseGroup({ client, path: { releaseGroupId } }),
      );
    },
    {
      params: t.Object({
        releaseGroupId: t.String(),
      }),
    },
  )
  .put(
    '/:releaseGroupId',
    async ({ params: { releaseGroupId }, body, authSession }) => {
      return requestWithAuth(authSession, (client) =>
        releaseGroupServiceUpdateReleaseGroup({
          client,
          path: { releaseGroupId },
          body,
        }),
      );
    },
    {
      params: t.Object({
        releaseGroupId: t.String(),
      }),
      body: t.Object({
        title: t.String(),
        typeValue: t.Numeric(),
        description: t.String(),
        isDisplay: t.Boolean(),
        orderNo: t.Number(),
      }),
    },
  )
  .delete(
    '/:releaseGroupId',
    async ({ params: { releaseGroupId }, authSession }) => {
      return requestWithAuth(authSession, (client) =>
        releaseGroupServiceDeleteReleaseGroup({
          client,
          path: { releaseGroupId },
        }),
      );
    },
    {
      params: t.Object({
        releaseGroupId: t.String(),
      }),
    },
  );
