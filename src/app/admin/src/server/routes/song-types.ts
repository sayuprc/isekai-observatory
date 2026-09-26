import Elysia from 'elysia';
import { songTypeServiceListSongTypes } from '../../generated';
import { requestWithAuth } from '../client';
import { authGuard } from '../middleware';

export const songTypes = new Elysia({ prefix: '/song-types' }).use(authGuard).get('/', async ({ authSession }) => {
  return requestWithAuth(authSession, client =>
    songTypeServiceListSongTypes({ client }));
});
