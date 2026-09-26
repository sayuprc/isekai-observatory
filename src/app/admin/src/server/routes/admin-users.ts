import Elysia from 'elysia';
import { adminUserServiceListAdminUsers } from '../../generated';
import { requestWithAuth } from '../client';
import { authGuard } from '../middleware';

export const adminUsers = new Elysia({ prefix: '/admin-users' }).use(authGuard).get('/', async ({ authSession }) => {
  return requestWithAuth(authSession, client =>
    adminUserServiceListAdminUsers({ client }));
});
