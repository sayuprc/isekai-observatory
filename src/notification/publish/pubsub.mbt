///|
/// GCE metadata から default SA の access token を取る
pub async fn fetch_access_token(config : Config) -> String {
  let (response, body) = @http.get(config.access_token_url(), headers={
    "Metadata-Flavor": "Google",
  })
  defer ignore(body)
  guard response.code is (200..<300) else {
    let body_text = body.text() catch { _ => "" }
    raise Failure(
      "metadata token request failed: \{response.code} \{response.reason} \{body_text}",
    )
  }
  let json = body.json() catch {
    err => raise Failure("metadata token response is not json: \{err}")
  }
  match json {
    { "access_token": String(token), .. } =>
      if token == "" {
        raise Failure("metadata token response has empty access_token")
      } else {
        token
      }
    _ => raise Failure("metadata token response missing access_token")
  }
}

///|
/// 検証済み通知 JSON を Pub/Sub topic へ publish する
pub async fn publish_notification(
  config : Config,
  payload_text : String,
) -> Unit {
  let token = fetch_access_token(config)
  let body = build_publish_body(payload_text)
  let (response, response_body) = @http.post(config.publish_url(), body, headers={
    "Authorization": "Bearer \{token}",
    "Content-Type": "application/json",
  })
  defer ignore(response_body)
  guard response.code is (200..<300) else {
    let body_text = response_body.text() catch { _ => "" }
    raise Failure(
      "pubsub publish failed: \{response.code} \{response.reason} \{body_text}",
    )
  }
}
