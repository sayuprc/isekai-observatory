import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Admin;

@doc("アクセストークン(JWT)")
@secret
scalar accessToken extends string;

@doc("リフレッシュトークン")
@secret
scalar refreshToken extends string;

@doc("リフレッシュトークンID")
scalar refreshTokenId extends uuid;

@doc("管理ユーザー登録トークン(平文)")
@secret
scalar registrationToken extends string;

@doc("リカバリーコード(平文)")
@secret
scalar recoveryCode extends string;

@doc("認証セッションID")
scalar authCeremonyId extends uuid;

// WebAuthn の publicKey オプションは実装ごとの差分が大きいため任意 JSON として扱う
alias webAuthnPublicKeyOptions = Record<unknown>;

// WebAuthn の credential もブラウザから受け取った JSON をそのまま運ぶ
alias webAuthnCredential = Record<unknown>;
