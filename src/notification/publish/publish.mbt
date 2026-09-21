///|
/// stdin の通知 JSON を読み、Pub/Sub へ publish する
pub async fn run() -> Unit {
  let config = Config::from_env()
  let payload_text = read_stdin_text()
  guard payload_text != "" else {
    raise Failure("stdin is empty; expected notification JSON")
  }
  validate_notification_json(payload_text) catch {
    err => raise Failure(err.to_string())
  }
  publish_notification(config, payload_text)
}

///|
async fn read_stdin_text() -> String {
  let data = @stdio.stdin.read_all()
  data.text() catch {
    err => raise Failure("stdin is not valid utf8: \{err}")
  }
}
