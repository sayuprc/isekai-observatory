import { describe, expect, it } from 'bun:test';
import { createWithAuthRetry } from './client';
import { ApiError } from './errors';
import type { AuthSession, Credential } from './types';

const baseCredential: Credential = {
  accessToken: 'expired-access-token',
  refreshTokenId: 'refresh-token-id',
  refreshToken: 'refresh-token',
  csrfToken: 'csrf-token',
};

const createAuthSession = (
  overrides: Partial<AuthSession> = {},
  credential: Credential = baseCredential,
): AuthSession => {
  return {
    credential,
    storeCredential: async (nextCredential) => nextCredential,
    clearCredential: async () => {},
    acquireRefreshLock: async () => true,
    waitForCredentialUpdate: async () => credential,
    releaseRefreshLock: async () => {},
    ...overrides,
  };
};

describe('createWithAuthRetry', () => {
  it('401 のときだけ refresh して 1 回だけ再試行する', async () => {
    const createdTokens: string[] = [];
    const executorTokens: string[] = [];
    const refreshedCredentials: Credential[] = [];
    const updatedTokens: string[] = [];

    const withAuthRetry = createWithAuthRetry({
      createClient: (credential) => {
        createdTokens.push(credential.accessToken);
        return credential.accessToken;
      },
      refreshAccessToken: async (credential) => {
        refreshedCredentials.push(credential);
        return {
          accessToken: 'renewed-access-token',
          refreshTokenId: 'renewed-refresh-token-id',
          refreshToken: 'renewed-refresh-token',
        };
      },
    });

    const result = await withAuthRetry(
      createAuthSession({
        storeCredential: async (nextCredential) => {
          updatedTokens.push(nextCredential.accessToken);
          expect(nextCredential.refreshTokenId).toBe('renewed-refresh-token-id');
          expect(nextCredential.refreshToken).toBe('renewed-refresh-token');
          return nextCredential;
        },
      }),
      async (client) => {
        executorTokens.push(client);

        if (client === baseCredential.accessToken) {
          throw new ApiError(401, {});
        }

        return 'ok';
      },
    );

    expect(result).toBe('ok');
    expect(createdTokens).toEqual(['expired-access-token', 'renewed-access-token']);
    expect(executorTokens).toEqual(['expired-access-token', 'renewed-access-token']);
    expect(refreshedCredentials).toEqual([baseCredential]);
    expect(updatedTokens).toEqual(['renewed-access-token']);
  });

  it('別リクエストが refresh 中なら更新済み accessToken を再利用する', async () => {
    const cleared: string[] = [];
    const executorTokens: string[] = [];
    const latestCredential = { ...baseCredential, accessToken: 'already-renewed-access-token' };
    const refreshedCredentials: Credential[] = [];

    const withAuthRetry = createWithAuthRetry({
      createClient: (credential) => credential.accessToken,
      refreshAccessToken: async (credential) => {
        refreshedCredentials.push(credential);
        return latestCredential;
      },
    });

    const result = await withAuthRetry(
      createAuthSession({
        acquireRefreshLock: async () => false,
        waitForCredentialUpdate: async (previousAccessToken) => {
          expect(previousAccessToken).toBe(baseCredential.accessToken);
          return latestCredential;
        },
        clearCredential: async () => {
          cleared.push('cleared');
        },
      }),
      async (client) => {
        executorTokens.push(client);

        if (client === baseCredential.accessToken) {
          throw new ApiError(401, {});
        }

        return 'ok';
      },
    );

    expect(result).toBe('ok');
    expect(executorTokens).toEqual(['expired-access-token', 'already-renewed-access-token']);
    expect(cleared).toEqual([]);
    expect(refreshedCredentials).toEqual([]);
  });

  it('refresh が 401 ならそのまま未認証へフォールバックする', async () => {
    const cleared: string[] = [];
    const withAuthRetry = createWithAuthRetry({
      createClient: (credential) => credential.accessToken,
      refreshAccessToken: async () => {
        throw new ApiError(401, {});
      },
    });

    await expect(
      withAuthRetry(
        createAuthSession({
          clearCredential: async () => {
            cleared.push('cleared');
          },
        }),
        async () => {
          throw new ApiError(401, {});
        },
      ),
    ).rejects.toMatchObject({ status: 401 });
    expect(cleared).toEqual(['cleared']);
  });
});
