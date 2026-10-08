import { Elysia, t } from 'elysia';
import {
  mediaServiceSearchMedia,
  songServiceCreateSong,
  songServiceDeleteSong,
  songServiceGetSong,
  songServiceSearchSongs,
  songServiceUpdateSong,
  songTagServiceListSongTags,
} from '../../generated';
import type { PerPage, SongSearchSortBy, SongSearchTypeValue, SortOrder } from '../../generated';
import { requestWithAuth, withAuthRetry } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

const SongTypeValueSchema = t.Union([t.Literal(1), t.Literal(2)]);
const NullableStringSchema = t.Union([t.String(), t.Null()]);

const SongPersonRefSchema = t.Array(
  t.Object({ personId: t.String(), role: t.Union([t.Literal(1), t.Literal(2), t.Literal(3)]), orderNo: t.Number() }),
);
const SongTagRefSchema = t.Array(t.Object({ songTagId: t.String() }));
const SongMediaRefSchema = t.Array(t.Object({ mediaId: t.String(), orderNo: t.Number() }));

export const songs = new Elysia({ prefix: '/songs' })
  .use(authGuard)
  .get('/create-form', async ({ authSession }) => {
    return withAuthRetry(authSession, async (client) => {
      const [tags, media] = await Promise.all([
        songTagServiceListSongTags({ client }),
        mediaServiceSearchMedia({ client, query: { per_page: 50 } }),
      ]);

      return {
        tags: resolveApiResponse(tags).tags,
        media: resolveApiResponse(media).media,
      };
    });
  })
  .get(
    '/search',
    async ({ query, authSession }) => {
      return requestWithAuth(authSession, (client) =>
        songServiceSearchSongs({
          client,
          query: {
            title: query.title || undefined,
            type: query.type as SongSearchTypeValue | undefined,
            is_display: query.is_display,
            sort: (query.sort ?? 'order_no') as SongSearchSortBy,
            order: (query.order ?? 'asc') as SortOrder,
            page: query.page ?? 1,
            per_page: (query.per_page ?? 25) as PerPage,
          },
        }),
      );
    },
    {
      query: t.Object({
        title: t.String(),
        type: t.Optional(t.Union([t.Literal('1'), t.Literal('2')])),
        is_display: t.Optional(t.Boolean()),
        sort: t.Optional(t.Union([t.Literal('title'), t.Literal('order_no')])),
        order: t.Optional(t.Union([t.Literal('asc'), t.Literal('desc')])),
        page: t.Optional(t.Number()),
        per_page: t.Optional(t.Number()),
      }),
    },
  )
  .get(
    '/:songId/edit-form',
    async ({ params: { songId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        const [song, tags, media] = await Promise.all([
          songServiceGetSong({ client, path: { songId } }),
          songTagServiceListSongTags({ client }),
          mediaServiceSearchMedia({ client, query: { per_page: 50 } }),
        ]);

        return {
          ...resolveApiResponse(song),
          tags: resolveApiResponse(tags).tags,
          media: resolveApiResponse(media).media,
        };
      });
    },
    {
      params: t.Object({
        songId: t.String(),
      }),
    },
  )
  .get(
    '/:songId',
    async ({ params: { songId }, authSession }) => {
      return requestWithAuth(authSession, (client) => songServiceGetSong({ client, path: { songId } }));
    },
    {
      params: t.Object({
        songId: t.String(),
      }),
    },
  )
  .post(
    '/',
    async ({ body: { title, description, lyricsLink, type, isDisplay, persons, tags, media }, authSession }) => {
      return requestWithAuth(authSession, (client) =>
        songServiceCreateSong({
          client,
          body: {
            title,
            description,
            lyricsLink,
            type,
            isDisplay,
            persons,
            tags,
            media,
          },
        }),
      );
    },
    {
      body: t.Object({
        title: t.String(),
        description: t.String(),
        lyricsLink: NullableStringSchema,
        type: SongTypeValueSchema,
        isDisplay: t.Boolean(),
        persons: SongPersonRefSchema,
        tags: SongTagRefSchema,
        media: SongMediaRefSchema,
      }),
    },
  )
  .put(
    '/:songId',
    async ({
      params: { songId },
      body: { title, description, lyricsLink, type, isDisplay, orderNo, persons, tags, media },
      authSession,
    }) => {
      return requestWithAuth(authSession, (client) =>
        songServiceUpdateSong({
          client,
          path: { songId },
          body: {
            title,
            description,
            lyricsLink,
            type,
            isDisplay,
            orderNo,
            persons,
            tags,
            media,
          },
        }),
      );
    },
    {
      params: t.Object({
        songId: t.String(),
      }),
      body: t.Object({
        title: t.String(),
        description: t.String(),
        lyricsLink: NullableStringSchema,
        type: SongTypeValueSchema,
        isDisplay: t.Boolean(),
        orderNo: t.Number(),
        persons: SongPersonRefSchema,
        tags: SongTagRefSchema,
        media: SongMediaRefSchema,
      }),
    },
  )
  .delete(
    '/:songId',
    async ({ params: { songId }, authSession }) => {
      // 削除は本文を返さない
      await requestWithAuth(authSession, (client) => songServiceDeleteSong({ client, path: { songId } }));
    },
    {
      params: t.Object({
        songId: t.String(),
      }),
    },
  );
