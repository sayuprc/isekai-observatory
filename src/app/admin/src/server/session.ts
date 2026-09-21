import { SESSION_TTL_SECONDS } from './constants';
import { redis } from './redis';
import type { Credential } from './types';

const REFRESH_LOCK_TTL_SECONDS = 5;
const REFRESH_WAIT_TIMEOUT_MS = 5000;
const REFRESH_WAIT_INTERVAL_MS = 100;

const createSessionKey = (sessionId: string): string => {
  return `session:${sessionId}`;
};

const createRefreshLockKey = (sessionId: string): string => {
  return `session-refresh-lock:${sessionId}`;
};

export const getSessionCredential = async (sessionId: string): Promise<Credential | null> => {
  return redis.get<Credential>(createSessionKey(sessionId));
};

export const storeSessionCredential = async (sessionId: string, credential: Credential): Promise<void> => {
  await redis.set(createSessionKey(sessionId), credential, { ex: SESSION_TTL_SECONDS });
};

export const replaceSessionCredential = async (
  sessionId: string,
  credential: Credential,
): Promise<Credential | null> => {
  const result = await redis.set(createSessionKey(sessionId), credential, { xx: true, keepTtl: true });

  if (result === 'OK') {
    return credential;
  }

  return getSessionCredential(sessionId);
};

export const clearSessionCredential = async (sessionId: string): Promise<void> => {
  await redis.del(createSessionKey(sessionId));
};

export const acquireSessionRefreshLock = async (sessionId: string): Promise<boolean> => {
  const result = await redis.set(createRefreshLockKey(sessionId), '1', {
    nx: true,
    ex: REFRESH_LOCK_TTL_SECONDS,
  });

  return result === 'OK';
};

export const releaseSessionRefreshLock = async (sessionId: string): Promise<void> => {
  await redis.del(createRefreshLockKey(sessionId));
};

export const waitForSessionCredentialUpdate = async (
  sessionId: string,
  previousAccessToken: string,
): Promise<Credential | null> => {
  const startedAt = Date.now();

  while (Date.now() - startedAt < REFRESH_WAIT_TIMEOUT_MS) {
    const credential = await getSessionCredential(sessionId);

    if (!credential || credential.accessToken !== previousAccessToken) {
      return credential;
    }

    await new Promise(resolve => setTimeout(resolve, REFRESH_WAIT_INTERVAL_MS));
  }

  return getSessionCredential(sessionId);
};
