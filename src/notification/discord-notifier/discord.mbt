///|
/// Discord embed の色 (RGB を Int で表す)
fn embed_color_for_status(status : String) -> Int {
  match status {
    "succeeded" => 0x57F287
    "failed" | "alert" => 0xED4245
    "started" => 0x3498DB
    _ => 0x95A5A6
  }
}

///|
/// embed に color が無ければ status 由来の色を付ける
fn ensure_embed_color(embed : Json, color : Int) -> Json {
  guard embed is Object(map) else { return embed }
  match map.get("color") {
    Some(_) => embed
    None => {
      let out : Map[String, Json] = Map([], capacity=map.length() + 1)
      for k, v in map {
        out[k] = v
      }
      out["color"] = color.to_json()
      out.to_json()
    }
  }
}

///|
fn apply_status_colors(embeds : Json, status : String) -> Json {
  let color = embed_color_for_status(status)
  guard embeds is Array(items) else { return embeds }
  let out : Array[Json] = []
  for embed in items {
    out.push(ensure_embed_color(embed, color))
  }
  out.to_json()
}

///|
/// content / embeds を Discord webhook payload にする
/// embeds の color 未指定には status 既定色を付ける
pub fn build_discord_payload(notification : Notification) -> Json {
  match (notification.content, notification.embeds) {
    (Some(content), Some(embeds)) =>
      {
        "content": content,
        "embeds": apply_status_colors(embeds, notification.status),
      }
    (Some(content), None) => { "content": content }
    (None, Some(embeds)) =>
      { "embeds": apply_status_colors(embeds, notification.status) }
    (None, None) =>
      // parse で content / embeds のどちらかを必須にしている
      { "content": "" }
  }
}

///|
pub async fn post_discord_webhook(
  webhook_url : String,
  notification : Notification,
) -> Unit {
  let payload = build_discord_payload(notification)
  let (response, body) = @http.post(webhook_url, payload, headers={
    "Content-Type": "application/json",
  })
  defer ignore(body)
  guard response.code is (200..<300) else {
    let body_text = body.text() catch { _ => "" }
    raise Failure(
      "discord webhook failed: \{response.code} \{response.reason} \{body_text}",
    )
  }
}
