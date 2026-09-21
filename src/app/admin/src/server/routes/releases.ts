import { Elysia, t } from 'elysia';
import {
  releaseServiceCreateRelease,
  releaseServiceDeleteRelease,
  releaseServiceGetRelease,
  releaseServiceUpdateRelease,
} from '../../generated';
import { withAuthRetry } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

const formatValuesSchema = t.Array(t.Numeric(), { minItems: 1 });

const mediaSchema = t.Array(
  t.Object({
    position: t.Number(),
    name: t.Union([t.String(), t.Null()]),
    tracks: t.Array(
      t.Object({
        songId: t.Union([t.String(), t.Null()]),
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
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await releaseServiceCreateRelease({ client, body }));
      });
    },
    {
      body: t.Object({
        releaseGroupId: t.String(),
        name: t.String(),
        releasedOn: t.String(),
        description: t.String(),
        color: t.String(),
        isDisplay: t.Boolean(),
        orderNo: t.Number(),
        formatValues: formatValuesSchema,
        media: mediaSchema,
      }),
    },
  )
  .get(
    '/:releaseId',
    async ({ params: { releaseId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await releaseServiceGetRelease({ client, path: { releaseId } }));
      });
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
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await releaseServiceUpdateRelease({
            client,
            path: { releaseId },
            body,
          }),
        );
      });
    },
    {
      params: t.Object({
        releaseId: t.String(),
      }),
      body: t.Object({
        name: t.String(),
        releasedOn: t.String(),
        description: t.String(),
        color: t.String(),
        isDisplay: t.Boolean(),
        orderNo: t.Number(),
        formatValues: formatValuesSchema,
        media: mediaSchema,
      }),
    },
  )
  .delete(
    '/:releaseId',
    async ({ params: { releaseId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await releaseServiceDeleteRelease({
            client,
            path: { releaseId },
          }),
        );
      });
    },
    {
      params: t.Object({
        releaseId: t.String(),
      }),
    },
  );
