import Elysia from 'elysia';
import { adminUserServiceListAdminUsers } from '../../generated';
import { withAuthRetry } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

export const adminUsers = new Elysia({ prefix: '/admin-users' }).use(authGuard).get('/', async ({ authSession }) => {
  return withAuthRetry(authSession, async (client) => {
    return resolveApiResponse(await adminUserServiceListAdminUsers({ client }));
  });
});
