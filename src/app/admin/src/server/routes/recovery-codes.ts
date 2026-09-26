import Elysia from 'elysia';
import { recoveryCodeServiceGenerate } from '../../generated';
import { requestWithAuth } from '../client';
import { authGuard } from '../middleware';

export const recoveryCodes = new Elysia({ prefix: '/recovery-codes' })
  .use(authGuard)
  .post('/', async ({ authSession }) => {
    return requestWithAuth(authSession, client =>
      recoveryCodeServiceGenerate({ client }));
  });
