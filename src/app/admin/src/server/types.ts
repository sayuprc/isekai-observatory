export type Credential = {
  accessToken: string;
  refreshTokenId: string;
  refreshToken: string;
  csrfToken: string;
};

export type AuthSession = {
  credential: Credential;
  storeCredential: (credential: Credential) => Promise<Credential | null>;
  clearCredential: () => Promise<void>;
  acquireRefreshLock: () => Promise<boolean>;
  waitForCredentialUpdate: (previousAccessToken: string) => Promise<Credential | null>;
  releaseRefreshLock: () => Promise<void>;
};
