import { Elysia, t } from 'elysia';
import { auditLogServiceGetAuditLog, auditLogServiceSearchAuditLogs } from '../../generated';
import type { AuditAction, AuditTargetType, PerPage } from '../../generated';
import { withAuthRetry } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

const AuditActionSchema = t.Union([
  t.Literal('create'),
  t.Literal('update'),
  t.Literal('delete'),
  t.Literal('register'),
  t.Literal('login'),
  t.Literal('refresh'),
  t.Literal('recovery_code_issue'),
  t.Literal('recovery_code_use'),
]);

const AuditTargetTypeSchema = t.Union([
  t.Literal('AdminUser'),
  t.Literal('Media'),
  t.Literal('Person'),
  t.Literal('Release'),
  t.Literal('Song'),
  t.Literal('SongTag'),
]);

export const auditLogs = new Elysia({ prefix: '/audit-logs' })
  .use(authGuard)
  .get(
    '/search',
    async ({ query, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await auditLogServiceSearchAuditLogs({
            client,
            query: {
              from: query.from || undefined,
              to: query.to || undefined,
              action: (query.action as AuditAction | undefined) || undefined,
              target_type: (query.target_type as AuditTargetType | undefined) || undefined,
              target_id: query.target_id || undefined,
              admin_user_name: query.admin_user_name || undefined,
              page: query.page ?? 1,
              per_page: (query.per_page ?? 50) as PerPage,
            },
          }),
        );
      });
    },
    {
      query: t.Object({
        from: t.Optional(t.String()),
        to: t.Optional(t.String()),
        action: t.Optional(AuditActionSchema),
        target_type: t.Optional(AuditTargetTypeSchema),
        target_id: t.Optional(t.String()),
        admin_user_name: t.Optional(t.String()),
        page: t.Optional(t.Number()),
        per_page: t.Optional(t.Number()),
      }),
    },
  )
  .get(
    '/:auditLogId',
    async ({ params: { auditLogId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await auditLogServiceGetAuditLog({ client, path: { auditLogId } }));
      });
    },
    {
      params: t.Object({
        auditLogId: t.String(),
      }),
    },
  );
