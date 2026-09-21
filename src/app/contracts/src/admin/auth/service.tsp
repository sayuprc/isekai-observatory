import "@typespec/http";
import "../../shared";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Admin;

@tag("Authenticate")
@route("/auth")
interface AuthenticateService {
  @doc("認証開始API")
  @route("/login/start")
  loginStart(@body request: LoginStartRequest):
    | LoginStartResponse
    | BadRequest
    | Unauthorized
    | Unprocessable
    | ServerError;

  @doc("認証完了API")
  @route("/login/finish")
  loginFinish(@body request: LoginFinishRequest):
    | LoginFinishResponse
    | BadRequest
    | Unauthorized
    | Unprocessable
    | ServerError;

  @doc("アクセストークン再発行API")
  @route("/refresh")
  refresh(@body request: RefreshTokenRequest): RefreshTokenResponse | Unauthorized | Unprocessable | ServerError;

  @doc("管理ユーザー登録開始API")
  @route("/register/start")
  registerStart(@body request: RegisterStartRequest):
    | RegisterStartResponse
    | BadRequest
    | Unauthorized
    | Unprocessable
    | ServerError;

  @doc("管理ユーザー登録完了API")
  @route("/register/finish")
  registerFinish(@body request: RegisterFinishRequest):
    | RegisterFinishResponse
    | BadRequest
    | Unauthorized
    | Unprocessable
    | ServerError;

  @doc("リカバリー開始API")
  @route("/recovery/start")
  recoveryStart(@body request: RecoveryStartRequest):
    | RecoveryStartResponse
    | BadRequest
    | Unauthorized
    | Unprocessable
    | ServerError;

  @doc("リカバリー完了API")
  @route("/recovery/finish")
  recoveryFinish(@body request: RecoveryFinishRequest):
    | RecoveryFinishResponse
    | BadRequest
    | Unauthorized
    | Unprocessable
    | ServerError;
}

@tag("RecoveryCode")
@route("/recovery-codes")
@useAuth(BearerAuth)
interface RecoveryCodeService {
  @doc("リカバリーコード発行/再生成API")
  @post
  generate(): GenerateRecoveryCodesResponse | Unauthorized | ServerError;
}
