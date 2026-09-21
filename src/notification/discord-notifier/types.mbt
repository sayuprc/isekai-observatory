///|
/// 1 メッセージ処理の結果
pub(all) enum Outcome {
  /// Pub/Sub に ack してよい (配達成功 / 意図的スキップ / 恒久失敗)
  Ack
  /// 一時失敗。Pub/Sub に再送させる
  Retry(String)
} derive(Eq, Debug)

///|
pub suberror ParseError {
  InvalidJson(String)
  InvalidBase64(String)
  InvalidPayload(String)
} derive(Eq, Debug)

///|
pub fn ParseError::to_string(self : ParseError) -> String {
  match self {
    InvalidJson(msg) => "invalid json: \{msg}"
    InvalidBase64(msg) => "invalid base64: \{msg}"
    InvalidPayload(msg) => "invalid payload: \{msg}"
  }
}

///|
fn to_parse_error(err : @notify_contract.NotificationError) -> ParseError {
  match err {
    @notify_contract.InvalidJson(msg) => InvalidJson(msg)
    @notify_contract.InvalidPayload(msg) => InvalidPayload(msg)
  }
}

///|
type Notification = @notify_contract.Notification
