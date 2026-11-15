import { Elysia, t } from 'elysia';
import {
  releaseServiceCreateRelease,
  releaseServiceDeleteRelease,
  releaseServiceGetRelease,
  releaseServiceSearchReleases,
  releaseServiceUpdateRelease,
} from '../../generated';
import type { PerPage } from '../../generated';
import { requestWithAuth } from '../client';
import { authGuard } from '../middleware';

const formatsSchema = t.Array(t.Union([t.Literal(1), t.Literal(2), t.Literal(3), t.Literal(4), t.Literal(99)]), {
  minItems: 1,
});

const mediaSchema = t.Array(
  t.Object({
    position: t.Number(),
    name: t.Union([t.String(), t.Null()]),
    tracks: t.Array(
      t.Object({
        songIds: t.Array(t.String()),
        title: t.Union([t.String(), t.Null()]),
        trackNo: t.Number(),
      }),
    ),
  }),
);

export const releases = new Elysia({ prefix: '/releases' })
  .use(authGuard)
  .post(
    '/',
    async ({ body, authSession }) => {
      return requestWithAuth(authSession, (client) => releaseServiceCreateRelease({ client, body }));
    },
    {
      body: t.Object({
        releaseGroupId: t.String(),
        name: t.String(),
        releasedOn: t.String(),
        description: t.String(),
        note: t.String(),
        isDisplay: t.Boolean(),
        orderNo: t.Number(),
        formats: formatsSchema,
        media: mediaSchema,
      }),
    },
  )
  .get(
    '/search',
    async ({ query, authSession }) => {
      return requestWithAuth(authSession, (client) =>
        releaseServiceSearchReleases({
          client,
          query: {
            title: query.title || undefined,
            page: query.page ?? 1,
            per_page: (query.per_page ?? 25) as PerPage,
          },
        }),
      );
    },
    {
      query: t.Object({
        title: t.Optional(t.String()),
        page: t.Optional(t.Number()),
        per_page: t.Optional(t.Number()),
      }),
    },
  )
  .get(
    '/:releaseId',
    async ({ params: { releaseId }, authSession }) => {
      return requestWithAuth(authSession, (client) => releaseServiceGetRelease({ client, path: { releaseId } }));
    },
    {
      params: t.Object({
        releaseId: t.String(),
      }),
    },
  )
  .put(
    '/:releaseId',
    async ({ params: { releaseId }, body, authSession }) => {
      return requestWithAuth(authSession, (client) =>
        releaseServiceUpdateRelease({
          client,
          path: { releaseId },
          body,
        }),
      );
    },
    {
      params: t.Object({
        releaseId: t.String(),
      }),
      body: t.Object({
        name: t.String(),
        releasedOn: t.String(),
        description: t.String(),
        note: t.String(),
        isDisplay: t.Boolean(),
        orderNo: t.Number(),
        formats: formatsSchema,
        media: mediaSchema,
      }),
    },
  )
  .delete(
    '/:releaseId',
    async ({ params: { releaseId }, authSession }) => {
      return requestWithAuth(authSession, (client) =>
        releaseServiceDeleteRelease({
          client,
          path: { releaseId },
        }),
      );
    },
    {
      params: t.Object({
        releaseId: t.String(),
      }),
    },
  );
