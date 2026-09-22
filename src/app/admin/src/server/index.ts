import { Elysia } from 'elysia';
import { ApiError } from './errors';
import { createRequestLogger } from './logger';
import { adminUsers } from './routes/admin-users';
import { auditLogs } from './routes/audit-logs';
import { auth } from './routes/auth';
import { events } from './routes/events';
import { media } from './routes/media';
import { persons } from './routes/persons';
import { recoveryCodes } from './routes/recovery-codes';
import { releaseGroups } from './routes/release-groups';
import { releases } from './routes/releases';
import { songTags } from './routes/song-tags';
import { songTypes } from './routes/song-types';
import { songs } from './routes/songs';
import { venues } from './routes/venues';

export const app = new Elysia({ prefix: '/api', normalize: 'typebox' })
  .onError(({ error, set, request, code }) => {
    if (error instanceof ApiError) {
      // 上流 API の想定内エラーはそのまま透過する(リクエストログ側で結果を記録する)
      set.status = error.status;
      return error.body;
    }

    // NOT_FOUND / VALIDATION 等の想定内クライアントエラーはリクエストログに任せ、
    // 想定外のサーバーエラー(5xx 相当)のみ握りつぶさず error として記録する
    if (code === 'UNKNOWN' || code === 'INTERNAL_SERVER_ERROR') {
      createRequestLogger(request.headers).error({ code, err: error }, 'unhandled api error');
    }
  })
  .use(auth)
  .use(adminUsers)
  .use(auditLogs)
  .use(media)
  .use(persons)
  .use(recoveryCodes)
  .use(releaseGroups)
  .use(releases)
  .use(songTypes)
  .use(songTags)
  .use(songs)
  .use(venues)
  .use(events);

export type App = typeof app;
