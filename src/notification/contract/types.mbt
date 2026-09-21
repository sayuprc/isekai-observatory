///|
/// アプリ通知の正規化済み形
pub(all) struct Notification {
  action : String
  status : String
  /// Discord webhook の content (無い/空なら payload に載せない)
  content : String?
  /// Discord embed 配列 (無い/空なら payload に載せない)
  /// content と embeds のどちらか一方は必須
  embeds : Json?
} derive(Eq, Debug)

///|
/// アプリ通知 JSON の検証・パースエラー
pub suberror NotificationError {
  InvalidJson(String)
  InvalidPayload(String)
} derive(Eq, Debug)

///|
pub fn NotificationError::to_string(self : NotificationError) -> String {
  match self {
    InvalidJson(msg) => "invalid json: \{msg}"
    InvalidPayload(msg) => "invalid payload: \{msg}"
  }
}
