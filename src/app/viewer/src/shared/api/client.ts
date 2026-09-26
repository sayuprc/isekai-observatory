import { createClient } from '../../generated/client/client.gen.js';
import { createConfig } from '../../generated/client/utils.gen.js';
import { getGoogleIdToken } from './google-id-token.js';

const baseUrl = import.meta.env.API_URL + '/v1';

/**
 * 非公開 API (allow_unauthenticated = false) の IAM 認証を通すための fetch
 * admin と合わせて X-Serverless-Authorization ヘッダで Google ID token を送る
 */
const fetchWithServerlessAuth: typeof fetch = Object.assign(
  async (input: RequestInfo | URL, init?: RequestInit) => {
    const idToken = await getGoogleIdToken(import.meta.env.API_URL);

    if (idToken === null) {
      return fetch(input, init);
    }

    const request = new Request(input, init);
    request.headers.set('X-Serverless-Authorization', `Bearer ${idToken}`);

    return fetch(request);
  },
  // Bun の typeof fetch は preconnect を要求するため、素の fetch へ委譲する
  { preconnect: fetch.preconnect },
);

export const apiClient = createClient(
  createConfig({
    baseUrl,
    fetch: fetchWithServerlessAuth,
  }),
);
