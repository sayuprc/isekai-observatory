///|
/// アプリ通知 JSON を検証する (共有契約)
pub fn validate_notification_json(
  text : String,
) -> Unit raise @notify_contract.NotificationError {
  @notify_contract.validate_notification_json(text)
}

///|
/// Pub/Sub PublishRequest の body を作る
pub fn build_publish_body(payload_text : String) -> Json {
  let data = @base64.encode(@utf8.encode(payload_text))
  { "messages": [{ "data": data }] }
}
