import "@typespec/http";

using Http;

namespace IsekaiObservatory.Shared;

model Ok<T> {
  @statusCode _: 200;
  @body body: T;
}

model NoContent {
  @statusCode _: 204;
}

model BadRequest {
  @statusCode _: 400;
  @body body: ErrorResponse;
}

model Unauthorized {
  @statusCode _: 401;
  @body body: ErrorResponse;
}

model Forbidden {
  @statusCode _: 403;
  @body body: ErrorResponse;
}

model NotFound {
  @statusCode _: 404;
  @body body: ErrorResponse;
}

model Unprocessable {
  @statusCode _: 422;
  @body body: ErrorResponse;
}

model InternalServerError {
  @statusCode _: 500;
  @body body: ErrorResponse;
}

model Unavailable {
  @statusCode _: 503;
}

model GatewayTimeout {
  @statusCode _: 504;
}

@doc("エラー分類を表す機械可読なコード")
@friendlyName("ErrorCode")
enum ErrorCode {
  Unauthenticated: "unauthenticated",
  PermissionDenied: "permission_denied",
  NotFound: "not_found",
  ValidationFailed: "validation_failed",
  BusinessRuleViolation: "business_rule_violation",
  InternalError: "internal_error",
}

@doc("フィールド単位のエラー詳細")
@friendlyName("ErrorDetail")
model ErrorDetail {
  field: string;
  message: string;
}

@doc("全エラーステータス共通のレスポンス")
@friendlyName("ErrorResponse")
model ErrorResponse {
  code: ErrorCode;
  message: string;
  details?: ErrorDetail[];
}

alias ServerError = InternalServerError | Unavailable | GatewayTimeout;
