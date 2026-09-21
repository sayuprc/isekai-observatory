import { Elysia, t } from 'elysia';
import {
  mediaServiceSearchMedia,
  songServiceCreateSong,
  songServiceDeleteSong,
  songServiceGetSong,
  songServiceSearchSongs,
  songServiceUpdateSong,
  songTagServiceListSongTags,
  songTypeServiceListSongTypes,
} from '../../generated';
import type { PerPage, SongSearchSortBy, SongSearchTypeValue, SortOrder } from '../../generated';
import { withAuthRetry } from '../client';
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
      const [types, tags, media] = await Promise.all([
        songTypeServiceListSongTypes({ client }),
        songTagServiceListSongTags({ client }),
        mediaServiceSearchMedia({ client, query: { per_page: 50 } }),
      ]);

      return {
        types: resolveApiResponse(types).types,
        tags: resolveApiResponse(tags).tags,
        media: resolveApiResponse(media).media,
      };
    });
  })
  .get(
    '/search',
    async ({ query, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        const [searchResult, types] = await Promise.all([
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
          songTypeServiceListSongTypes({ client }),
        ]);

        return {
          ...resolveApiResponse(searchResult),
          types: resolveApiResponse(types).types,
        };
      });
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
        const [song, types, tags, media] = await Promise.all([
          songServiceGetSong({ client, path: { songId } }),
          songTypeServiceListSongTypes({ client }),
          songTagServiceListSongTags({ client }),
          mediaServiceSearchMedia({ client, query: { per_page: 50 } }),
        ]);

        return {
          ...resolveApiResponse(song),
          types: resolveApiResponse(types).types,
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
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await songServiceGetSong({ client, path: { songId } }));
      });
    },
    {
      params: t.Object({
        songId: t.String(),
      }),
    },
  )
  .post(
    '/',
    async ({ body: { title, description, lyricsLink, typeValue, isDisplay, persons, tags, media }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await songServiceCreateSong({
            client,
            body: {
              title,
              description,
              lyricsLink,
              typeValue,
              isDisplay,
              persons,
              tags,
              media,
            },
          }),
        );
      });
    },
    {
      body: t.Object({
        title: t.String(),
        description: t.String(),
        lyricsLink: NullableStringSchema,
        typeValue: SongTypeValueSchema,
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
      body: { title, description, lyricsLink, typeValue, isDisplay, orderNo, persons, tags, media },
      authSession,
    }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await songServiceUpdateSong({
            client,
            path: { songId },
            body: {
              title,
              description,
              lyricsLink,
              typeValue,
              isDisplay,
              orderNo,
              persons,
              tags,
              media,
            },
          }),
        );
      });
    },
    {
      params: t.Object({
        songId: t.String(),
      }),
      body: t.Object({
        title: t.String(),
        description: t.String(),
        lyricsLink: NullableStringSchema,
        typeValue: SongTypeValueSchema,
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
      return withAuthRetry(authSession, async (client) => {
        resolveApiResponse(await songServiceDeleteSong({ client, path: { songId } }));
      });
    },
    {
      params: t.Object({
        songId: t.String(),
      }),
    },
  );
