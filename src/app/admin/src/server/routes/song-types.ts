import Elysia from 'elysia';
import { songTypeServiceListSongTypes } from '../../generated';
import { withAuthRetry } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const songTypes = new Elysia({ prefix: '/song-types' }).use(authGuard).get('/', async ({ authSession }) => {
  return withAuthRetry(authSession, async (client) => {
    return resolveApiResponse(await songTypeServiceListSongTypes({ client }));
  });
});
