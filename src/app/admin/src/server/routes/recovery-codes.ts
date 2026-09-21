import Elysia from 'elysia';
import { recoveryCodeServiceGenerate } from '../../generated';
import { withAuthRetry } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const recoveryCodes = new Elysia({ prefix: '/recovery-codes' })
  .use(authGuard)
  .post('/', async ({ authSession }) => {
    return withAuthRetry(authSession, async (client) => {
      return resolveApiResponse(await recoveryCodeServiceGenerate({ client }));
    });
  });
