import { API_URL } from 'astro:env/server';
import { createClient, createConfig } from '../generated/client';
import type { Client } from '../generated/client';
import { authenticateServiceRefresh } from '../generated/index';
import { ApiError, resolveApiResponse } from './errors';
import { getGoogleIdToken } from './google-id-token';
import type { AuthSession, Credential } from './types';

const apiUrl = API_URL + '/admin/v1';

/**
 * 非公開 API (allow_unauthenticated = false) の IAM 認証を通すための fetch
 * Authorization はアプリの JWT が使うため、Google ID token は
 * Cloud Run が予約している X-Serverless-Authorization ヘッダで送る
 */
const fetchWithServerlessAuth: typeof fetch = async (input, init) => {
  const idToken = await getGoogleIdToken(API_URL);

  if (idToken === null) {
    return fetch(input, init);
  }

  const request = new Request(input, init);
  request.headers.set('X-Serverless-Authorization', `Bearer ${idToken}`);

  return fetch(request);
};

export const createAuthClient = (credential: Credential) => {
  return createClient(
    createConfig({
      baseUrl: apiUrl,
      fetch: fetchWithServerlessAuth,
      headers: {
        Authorization: `Bearer ${credential.accessToken}`,
      },
    }),
  );
};

export const client = createClient(
  createConfig({
    baseUrl: apiUrl,
    fetch: fetchWithServerlessAuth,
  }),
);

type AuthRetryDeps<TClient> = {
  createClient: (credential: Credential) => TClient;
  refreshAccessToken: (
    credential: Credential,
  ) => Promise<Pick<Credential, 'accessToken' | 'refreshTokenId' | 'refreshToken'>>;
};

const refreshAccessToken = async (
  credential: Credential,
): Promise<Pick<Credential, 'accessToken' | 'refreshTokenId' | 'refreshToken'>> => {
  const result = await authenticateServiceRefresh({
    client,
    body: {
      refreshTokenId: credential.refreshTokenId,
      refreshToken: credential.refreshToken,
    },
  });

  const data = resolveApiResponse(result);

  return {
    accessToken: data.accessToken,
    refreshTokenId: data.refreshTokenId,
    refreshToken: data.refreshToken,
  };
};

export const createWithAuthRetry = <TClient>(deps: AuthRetryDeps<TClient>) => {
  return async <T>(authSession: AuthSession, callback: (client: TClient) => Promise<T>): Promise<T> => {
    try {
      return await callback(deps.createClient(authSession.credential));
    } catch (error) {
      if (!(error instanceof ApiError) || error.status !== 401) {
        throw error;
      }
    }

    const refreshLockAcquired = await authSession.acquireRefreshLock();

    if (!refreshLockAcquired) {
      const refreshedCredential = await authSession.waitForCredentialUpdate(authSession.credential.accessToken);

      if (!refreshedCredential) {
        throw new ApiError(401, {});
      }

      return await callback(deps.createClient(refreshedCredential));
    }

    try {
      const nextCredentialTokens = await deps.refreshAccessToken(authSession.credential);
      const refreshedCredential = await authSession.storeCredential({
        ...authSession.credential,
        ...nextCredentialTokens,
      });

      if (!refreshedCredential) {
        throw new ApiError(401, {});
      }

      return await callback(deps.createClient(refreshedCredential));
    } catch (error) {
      if (error instanceof ApiError && error.status === 401) {
        await authSession.clearCredential();
      }

      throw error;
    } finally {
      await authSession.releaseRefreshLock();
    }
  };
};

export const withAuthRetry = createWithAuthRetry<Client>({
  createClient: createAuthClient,
  refreshAccessToken,
});
