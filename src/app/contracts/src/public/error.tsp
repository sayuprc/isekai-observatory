import "@typespec/http";

using Http;

namespace IsekaiObservatory.Public;

// Public のエラーは 400 と 500 だけで、分類はステータスで足りるため code を持たない
@doc("エラーのレスポンス")
@friendlyName("ErrorResponse")
model ErrorResponse {
  @doc("エラーの内容")
  message: string;
}

@doc("`pageSize` が範囲外、`pageToken` が不正、または絞り込みの値が定義にありません")
model BadRequest {
  @statusCode _: 400;
  @body body: ErrorResponse;
}

@doc("サーバー側でエラーが発生しました")
model InternalServerError {
  @statusCode _: 500;
  @body body: ErrorResponse;
}
