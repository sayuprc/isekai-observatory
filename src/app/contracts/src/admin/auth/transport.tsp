import "@typespec/http";
import "../../shared";
import "../admin-users";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Admin;

model LoginStartRequest {
  email: email;
}

model LoginStartResponse {
  authCeremonyId: authCeremonyId;
  publicKey: webAuthnPublicKeyOptions;
}

model LoginFinishRequest {
  authCeremonyId: authCeremonyId;
  credential: webAuthnCredential;
}

model LoginFinishResponse {
  accessToken: accessToken;
  refreshTokenId: refreshTokenId;
  refreshToken: refreshToken;
}

model RefreshTokenRequest {
  refreshTokenId: refreshTokenId;
  refreshToken: refreshToken;
}

model RefreshTokenResponse {
  accessToken: accessToken;
  refreshTokenId: refreshTokenId;
  refreshToken: refreshToken;
}

model RegisterStartRequest {
  token: registrationToken;
  email: email;
  name: adminUserName;
}

model RegisterStartResponse {
  authCeremonyId: authCeremonyId;
  publicKey: webAuthnPublicKeyOptions;
}

model RegisterFinishRequest {
  authCeremonyId: authCeremonyId;
  token: registrationToken;
  credential: webAuthnCredential;
}

model RegisterFinishResponse {
  accessToken: accessToken;
  refreshTokenId: refreshTokenId;
  refreshToken: refreshToken;
}

model RecoveryStartRequest {
  email: email;
  recoveryCode: recoveryCode;
  name: adminUserName;
}

model RecoveryStartResponse {
  authCeremonyId: authCeremonyId;
  publicKey: webAuthnPublicKeyOptions;
}

model RecoveryFinishRequest {
  authCeremonyId: authCeremonyId;
  credential: webAuthnCredential;
}

model RecoveryFinishResponse {
  accessToken: accessToken;
  refreshTokenId: refreshTokenId;
  refreshToken: refreshToken;
}

model GenerateRecoveryCodesResponse {
  recoveryCodes: recoveryCode[];
}
