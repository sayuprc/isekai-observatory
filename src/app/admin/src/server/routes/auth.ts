import { randomBytes } from 'node:crypto';
import { Elysia, t } from 'elysia';
import {
  authenticateServiceLoginFinish,
  authenticateServiceLoginStart,
  authenticateServiceRecoveryFinish,
  authenticateServiceRecoveryStart,
  authenticateServiceRegisterFinish,
  authenticateServiceRegisterStart,
} from '../../generated';
import type { LoginFinishRequest, RecoveryFinishRequest, RegisterFinishRequest } from '../../generated';
import { client } from '../client';
import { AUTH_RATE_LIMITS, SESSION_TTL_SECONDS } from '../constants';
import { resolveApiResponse } from '../errors';
import { enforceAuthRateLimit } from '../rate-limit';
import { storeSessionCredential } from '../session';

const generateRandomBytes = (): string => {
  return randomBytes(32).toString('base64url');
};

const webAuthnCredentialSchema = t.Record(t.String(), t.Any());

const setAuthCookies = async (
  session: { set: (value: Record<string, unknown>) => Promise<unknown> | unknown } | undefined,
  csrf: { set: (value: Record<string, unknown>) => Promise<unknown> | unknown } | undefined,
  sessionId: string,
  csrfToken: string,
): Promise<void> => {
  await session?.set({
    value: sessionId,
    httpOnly: true,
    secure: true,
    sameSite: 'strict',
    path: '/',
    maxAge: SESSION_TTL_SECONDS,
  });

  await csrf?.set({
    value: csrfToken,
    httpOnly: false,
    secure: true,
    sameSite: 'strict',
    path: '/',
    maxAge: SESSION_TTL_SECONDS,
  });
};

export const auth = new Elysia({ prefix: '/auth' })
  .post(
    '/login/start',
    async ({ body: { email } }) => {
      return resolveApiResponse(await authenticateServiceLoginStart({ client: client, body: { email } }));
    },
    {
      beforeHandle: ({ request }) => enforceAuthRateLimit(request, 'login/start', AUTH_RATE_LIMITS.loginStart),
      body: t.Object({
        email: t.String(),
      }),
    },
  )
  .post(
    '/login/finish',
    async ({ body: { authCeremonyId, credential }, cookie: { session, csrf } }) => {
      const body = { authCeremonyId, credential } satisfies LoginFinishRequest;
      const data = resolveApiResponse(await authenticateServiceLoginFinish({ client: client, body: body }));

      const sessionId = generateRandomBytes();
      const csrfToken = generateRandomBytes();

      await storeSessionCredential(sessionId, { ...data, csrfToken });

      await setAuthCookies(session, csrf, sessionId, csrfToken);
    },
    {
      beforeHandle: ({ request }) => enforceAuthRateLimit(request, 'login/finish', AUTH_RATE_LIMITS.loginFinish),
      body: t.Object({
        authCeremonyId: t.String(),
        credential: webAuthnCredentialSchema,
      }),
    },
  )
  .post(
    '/register/start',
    async ({ body: { token, email, name } }) => {
      return resolveApiResponse(
        await authenticateServiceRegisterStart({ client: client, body: { token, email, name } }),
      );
    },
    {
      beforeHandle: ({ request }) => enforceAuthRateLimit(request, 'register/start', AUTH_RATE_LIMITS.registerStart),
      body: t.Object({
        token: t.String(),
        email: t.String(),
        name: t.String(),
      }),
    },
  )
  .post(
    '/register/finish',
    async ({ body: { authCeremonyId, token, credential }, cookie: { session, csrf } }) => {
      const body = { authCeremonyId, token, credential } satisfies RegisterFinishRequest;
      const data = resolveApiResponse(await authenticateServiceRegisterFinish({ client: client, body: body }));

      const sessionId = generateRandomBytes();
      const csrfToken = generateRandomBytes();

      await storeSessionCredential(sessionId, { ...data, csrfToken });

      await setAuthCookies(session, csrf, sessionId, csrfToken);
    },
    {
      beforeHandle: ({ request }) => enforceAuthRateLimit(request, 'register/finish', AUTH_RATE_LIMITS.registerFinish),
      body: t.Object({
        authCeremonyId: t.String(),
        token: t.String(),
        credential: webAuthnCredentialSchema,
      }),
    },
  )
  .post(
    '/recovery/start',
    async ({ body: { email, recoveryCode, name } }) => {
      return resolveApiResponse(
        await authenticateServiceRecoveryStart({ client: client, body: { email, recoveryCode, name } }),
      );
    },
    {
      beforeHandle: ({ request }) => enforceAuthRateLimit(request, 'recovery/start', AUTH_RATE_LIMITS.recoveryStart),
      body: t.Object({
        email: t.String(),
        recoveryCode: t.String(),
        name: t.String(),
      }),
    },
  )
  .post(
    '/recovery/finish',
    async ({ body: { authCeremonyId, credential }, cookie: { session, csrf } }) => {
      const body = { authCeremonyId, credential } satisfies RecoveryFinishRequest;
      const data = resolveApiResponse(await authenticateServiceRecoveryFinish({ client: client, body: body }));

      const sessionId = generateRandomBytes();
      const csrfToken = generateRandomBytes();

      await storeSessionCredential(sessionId, { ...data, csrfToken });

      await setAuthCookies(session, csrf, sessionId, csrfToken);
    },
    {
      beforeHandle: ({ request }) => enforceAuthRateLimit(request, 'recovery/finish', AUTH_RATE_LIMITS.recoveryFinish),
      body: t.Object({
        authCeremonyId: t.String(),
        credential: webAuthnCredentialSchema,
      }),
    },
  );
