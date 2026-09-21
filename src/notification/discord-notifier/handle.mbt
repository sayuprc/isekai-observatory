///|
pub struct App {
  config : Config
  deduper : StartedBuildDeduper
}

///|
pub fn App::new(config : Config) -> App {
  { config, deduper: StartedBuildDeduper::new() }
}

///|
pub async fn App::handle_body(self : App, body : String) -> Outcome {
  let notification = parse_request_body(
    body,
    cloud_build_trigger_name=self.config.cloud_build_trigger_name,
  ) catch {
    err => {
      println("ack permanent parse error: \{err.to_string()}")
      return Ack
    }
  }
  guard notification is Some(notification) else { return Ack }

  if notification.action == "cloud_build" && notification.status == "started" {
    let build_id = match cloud_build_id(body) {
      Some(id) => id
      None => ""
    }
    guard self.deduper.should_notify_started(build_id) else {
      println("skip duplicate cloud_build started: \{build_id}")
      return Ack
    }
  }

  let channel = self.config.resolve_channel(notification.action)
  let webhook_url = match self.config.resolve_webhook_url(notification.action) {
    Some(url) => url
    None => {
      println(
        "ack missing webhook for channel=\{channel} action=\{notification.action} status=\{notification.status}",
      )
      return Ack
    }
  }

  post_discord_webhook(webhook_url, notification) catch {
    err => return Retry(err.to_string())
  }
  println(
    "delivered action=\{notification.action} status=\{notification.status} channel=\{channel}",
  )
  Ack
}
