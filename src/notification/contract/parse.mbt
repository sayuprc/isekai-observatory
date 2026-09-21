///|
/// 文字列からアプリ通知をパースする
pub fn parse_notification_json(
  text : String,
) -> Notification raise NotificationError {
  let root = @json.parse(text) catch {
    err => raise InvalidJson(err.to_string())
  }
  parse_notification(root)
}

///|
/// JSON オブジェクトからアプリ通知をパースする
/// action / status と content または非空 embeds が必須
pub fn parse_notification(
  payload : Json,
) -> Notification raise NotificationError {
  match payload {
    { "action": String(action), "status": String(status), .. } => {
      guard action != "" else {
        raise InvalidPayload("action must not be empty")
      }
      guard status != "" else {
        raise InvalidPayload("status must not be empty")
      }
      let content = extract_content(payload)
      let embeds = extract_embeds(payload)
      guard content is Some(_) || embeds is Some(_) else {
        raise InvalidPayload("content or non-empty embeds is required")
      }
      { action, status, content, embeds }
    }
    _ => raise InvalidPayload("expected notification with action/status")
  }
}

///|
/// 検証のみ。成功時は Unit
pub fn validate_notification_json(
  text : String,
) -> Unit raise NotificationError {
  ignore(parse_notification_json(text))
}

///|
fn extract_content(payload : Json) -> String? {
  match payload {
    { "content": String(content), .. } =>
      if content == "" {
        None
      } else {
        Some(content)
      }
    _ => None
  }
}

///|
fn extract_embeds(payload : Json) -> Json? raise NotificationError {
  match payload {
    { "embeds": embeds, .. } => {
      guard embeds is Array(_) else {
        raise InvalidPayload("embeds must be an array")
      }
      if has_embeds(embeds) {
        Some(embeds)
      } else {
        None
      }
    }
    _ => None
  }
}

///|
pub fn has_embeds(embeds : Json) -> Bool {
  match embeds {
    Array(items) => items.length() > 0
    _ => false
  }
}
