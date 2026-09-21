import type {
  LoginFinishRequest,
  LoginStartResponse,
  RegisterFinishRequest,
  RegisterStartResponse,
} from '../generated';

export type WebAuthnPublicKeyOptions = LoginStartResponse['publicKey'] | RegisterStartResponse['publicKey'];
export type WebAuthnCredential = LoginFinishRequest['credential'] | RegisterFinishRequest['credential'];

type RegistrationOptionsJson = {
  challenge: string;
  rp: PublicKeyCredentialRpEntity;
  user: {
    id: string;
    name: string;
    displayName: string;
  };
  pubKeyCredParams: PublicKeyCredentialParameters[];
  timeout?: number;
  excludeCredentials?: Array<{
    id: string;
    type: PublicKeyCredentialType;
    transports?: AuthenticatorTransport[];
  }>;
  authenticatorSelection?: AuthenticatorSelectionCriteria;
  attestation?: AttestationConveyancePreference;
  extensions?: AuthenticationExtensionsClientInputs;
};

type AuthenticationOptionsJson = {
  challenge: string;
  timeout?: number;
  rpId?: string;
  allowCredentials?: Array<{
    id: string;
    type: PublicKeyCredentialType;
    transports?: AuthenticatorTransport[];
  }>;
  userVerification?: UserVerificationRequirement;
  extensions?: AuthenticationExtensionsClientInputs;
};

const decodeBase64Url = (value: string): ArrayBuffer => {
  const normalized = value.replace(/-/g, '+').replace(/_/g, '/');
  const padded = normalized + '='.repeat((4 - (normalized.length % 4)) % 4);
  const binary = atob(padded);
  const bytes = Uint8Array.from(binary, char => char.charCodeAt(0));

  return bytes.buffer.slice(bytes.byteOffset, bytes.byteOffset + bytes.byteLength);
};

const encodeBase64Url = (value: ArrayBuffer | Uint8Array): string => {
  const bytes = value instanceof Uint8Array ? value : new Uint8Array(value);
  const binary = Array.from(bytes, byte => String.fromCharCode(byte)).join('');

  return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/u, '');
};

type PasskeyAuthenticationOptions = {
  mediation?: CredentialMediationRequirement;
  signal?: AbortSignal;
};

const toRegistrationOptions = (publicKey: WebAuthnPublicKeyOptions): PublicKeyCredentialCreationOptions => {
  const options = publicKey as unknown as RegistrationOptionsJson;
  const excludeCredentials = options.excludeCredentials ?? [];

  return {
    challenge: decodeBase64Url(options.challenge),
    rp: options.rp,
    user: {
      name: options.user.name,
      displayName: options.user.displayName,
      id: decodeBase64Url(options.user.id),
    },
    pubKeyCredParams: options.pubKeyCredParams,
    timeout: options.timeout,
    authenticatorSelection: options.authenticatorSelection,
    attestation: options.attestation,
    excludeCredentials: excludeCredentials.map((item) => {
      return {
        type: item.type,
        id: decodeBase64Url(item.id),
        transports: item.transports,
      };
    }),
    extensions: options.extensions,
  } satisfies PublicKeyCredentialCreationOptions;
};

const toAuthenticationOptions = (publicKey: WebAuthnPublicKeyOptions): PublicKeyCredentialRequestOptions => {
  const options = publicKey as unknown as AuthenticationOptionsJson;
  const allowCredentials = options.allowCredentials ?? [];

  return {
    challenge: decodeBase64Url(options.challenge),
    timeout: options.timeout,
    rpId: options.rpId,
    allowCredentials: allowCredentials.map((item) => {
      return {
        type: item.type,
        id: decodeBase64Url(item.id),
        transports: item.transports,
      };
    }),
    userVerification: options.userVerification,
    extensions: options.extensions,
  } satisfies PublicKeyCredentialRequestOptions;
};

type JsonCredential = WebAuthnCredential & {
  id: string;
  rawId: string;
  type: string;
  response: WebAuthnCredential;
  clientExtensionResults: AuthenticationExtensionsClientOutputs;
  authenticatorAttachment?: string | null;
};

const credentialToJson = (credential: PublicKeyCredential): JsonCredential => {
  const response = credential.response;

  if (response instanceof AuthenticatorAttestationResponse) {
    return {
      id: credential.id,
      rawId: encodeBase64Url(credential.rawId),
      type: credential.type,
      response: {
        clientDataJSON: encodeBase64Url(response.clientDataJSON),
        attestationObject: encodeBase64Url(response.attestationObject),
        transports: response.getTransports?.() ?? [],
      },
      clientExtensionResults: credential.getClientExtensionResults(),
      authenticatorAttachment: credential.authenticatorAttachment,
    };
  }

  if (response instanceof AuthenticatorAssertionResponse) {
    return {
      id: credential.id,
      rawId: encodeBase64Url(credential.rawId),
      type: credential.type,
      response: {
        clientDataJSON: encodeBase64Url(response.clientDataJSON),
        authenticatorData: encodeBase64Url(response.authenticatorData),
        signature: encodeBase64Url(response.signature),
        userHandle: response.userHandle ? encodeBase64Url(response.userHandle) : null,
      },
      clientExtensionResults: credential.getClientExtensionResults(),
      authenticatorAttachment: credential.authenticatorAttachment,
    };
  }

  throw new Error('未対応の credential response です');
};

export const passkeyErrorMessage = (error: unknown, fallbackMessage: string): string => {
  if (typeof DOMException !== 'undefined' && error instanceof DOMException) {
    switch (error.name) {
      case 'AbortError':
        return 'パスキー操作を中断しました';
      case 'InvalidStateError':
        return 'このパスキーはすでに登録されています';
      case 'NotAllowedError':
        return 'パスキー操作がキャンセルされたか、許可されませんでした';
      case 'NotSupportedError':
        return 'このブラウザまたは端末はパスキーに対応していません';
      case 'SecurityError':
        return 'この環境ではパスキーを利用できません';
      case 'TimeoutError':
        return 'パスキー操作がタイムアウトしました';
      default:
        return fallbackMessage;
    }
  }

  return error instanceof Error ? error.message : fallbackMessage;
};

export const isPasskeyConditionalMediationAvailable = async (): Promise<boolean> => {
  if (
    typeof window === 'undefined'
    || typeof window.PublicKeyCredential === 'undefined'
    || typeof window.PublicKeyCredential.isConditionalMediationAvailable !== 'function'
  ) {
    return false;
  }

  return await window.PublicKeyCredential.isConditionalMediationAvailable();
};

export const registerPasskey = async (publicKey: WebAuthnPublicKeyOptions): Promise<JsonCredential> => {
  if (typeof window.PublicKeyCredential === 'undefined') {
    throw new Error('このブラウザはパスキー登録に対応していません');
  }

  const credential = await navigator.credentials.create({
    publicKey: toRegistrationOptions(publicKey),
  });

  if (!(credential instanceof PublicKeyCredential)) {
    throw new Error('パスキー登録に失敗しました');
  }

  return credentialToJson(credential);
};

export const authenticatePasskey = async (
  publicKey: WebAuthnPublicKeyOptions,
  options: PasskeyAuthenticationOptions = {},
): Promise<JsonCredential> => {
  if (typeof window.PublicKeyCredential === 'undefined') {
    throw new Error('このブラウザはパスキーログインに対応していません');
  }

  const requestOptions: CredentialRequestOptions & { signal?: AbortSignal } = {
    publicKey: toAuthenticationOptions(publicKey),
    mediation: options.mediation,
    signal: options.signal,
  };

  const credential = await navigator.credentials.get({
    ...requestOptions,
  });

  if (!(credential instanceof PublicKeyCredential)) {
    throw new Error('パスキーログインに失敗しました');
  }

  return credentialToJson(credential);
};
