///|
/// HTTP body (Pub/Sub push envelope または生 JSON) から Notification を取り出す
/// Cloud Build で対象外 / 未対応 status のときは None
pub fn parse_request_body(
  body : String,
  cloud_build_trigger_name~ : String,
) -> Notification? raise ParseError {
  let root = @json.parse(body) catch {
    _ => raise InvalidJson("failed to parse request body")
  }
  let payload = extract_payload_json(root)
  parse_payload_json(payload, cloud_build_trigger_name~)
}

///|
fn extract_payload_json(root : Json) -> Json raise ParseError {
  match root {
    { "message": { "data": String(data), .. }, .. } => {
      let bytes = @base64.decode(data, ignore_whitespace=true) catch {
        _ => raise InvalidBase64("failed to decode message.data")
      }
      let text = @utf8.decode(bytes) catch {
        _ => raise InvalidPayload("message.data is not valid utf8")
      }
      @json.parse(text) catch {
        _ => raise InvalidJson("failed to parse decoded message.data")
      }
    }
    _ => root
  }
}

///|
fn parse_payload_json(
  payload : Json,
  cloud_build_trigger_name~ : String,
) -> Notification? raise ParseError {
  match payload {
    { "action": String(_), "status": String(_), .. } =>
      Some(
        @notify_contract.parse_notification(payload) catch {
          err => raise to_parse_error(err)
        },
      )
    { "id": String(_), "status": String(status), .. } =>
      parse_cloud_build(payload, status, cloud_build_trigger_name~)
    { "resource": { "type": String(resource_type), .. }, .. } =>
      if resource_type == "cloud_run_job" {
        Some(parse_cloud_run_job(payload))
      } else {
        None
      }
    _ =>
      raise InvalidPayload(
        "expected app notification with action/status, Cloud Build JSON, or cloud_run_job LogEntry",
      )
  }
}

///|
fn parse_cloud_build(
  payload : Json,
  status : String,
  cloud_build_trigger_name~ : String,
) -> Notification? {
  let trigger_name = extract_trigger_name(payload)
  guard cloud_build_trigger_name != "" &&
    trigger_name == cloud_build_trigger_name else {
    return None
  }
  let (notify_status, title) = match status {
    "WORKING" => ("started", "Cloud Build が開始しました")
    "SUCCESS" => ("succeeded", "Cloud Build が成功しました")
    "FAILURE" | "INTERNAL_ERROR" | "TIMEOUT" | "CANCELLED" =>
      ("failed", "Cloud Build が失敗しました")
    _ => return None
  }
  let build_id = match payload {
    { "id": String(id), .. } => id
    _ => "unknown"
  }
  let log_url = match payload {
    { "logUrl": String(url), .. } => url
    _ => ""
  }
  let fields : Json = [
    { "name": "build_id", "value": build_id, "inline": true },
    { "name": "trigger", "value": trigger_name, "inline": true },
  ]
  let embed : Json = if log_url == "" {
    { "title": title, "fields": fields }
  } else {
    { "title": title, "url": log_url, "fields": fields }
  }
  Some({
    action: "cloud_build",
    status: notify_status,
    content: None,
    embeds: Some([embed]),
  })
}

///|
fn extract_trigger_name(payload : Json) -> String {
  match payload {
    { "substitutions": { "TRIGGER_NAME": String(name), .. }, .. } => name
    _ => ""
  }
}

///|
/// Cloud Logging LogEntry (resource.type=cloud_run_job) を正規化する
/// Sink 側で失敗のみ流す前提なので status は常に failed
fn parse_cloud_run_job(payload : Json) -> Notification {
  let job = match payload {
    { "resource": { "labels": labels, .. }, .. } =>
      json_string_field(labels, "job_name")
    _ => "unknown"
  }
  let location = match payload {
    { "resource": { "labels": labels, .. }, .. } =>
      json_string_field(labels, "location")
    _ => "unknown"
  }
  let execution = match payload {
    { "labels": labels, .. } =>
      json_string_field(labels, "run.googleapis.com/execution_name")
    _ => "unknown"
  }
  let message = truncate_text(extract_log_message(payload), 1000)
  let job_label = if job == "" { "unknown" } else { job }
  let execution_label = if execution == "" { "unknown" } else { execution }
  let location_label = if location == "" { "unknown" } else { location }
  let message_label = if message == "" { "(no message)" } else { message }
  let embed : Json = {
    "title": "Cloud Run Job failed: \{job_label}",
    "fields": [
      { "name": "job", "value": job_label, "inline": true },
      { "name": "execution", "value": execution_label, "inline": true },
      { "name": "location", "value": location_label, "inline": true },
      { "name": "message", "value": message_label, "inline": false },
    ],
  }
  {
    action: "cloud_run_job",
    status: "failed",
    content: None,
    embeds: Some([embed]),
  }
}

///|
fn extract_log_message(payload : Json) -> String {
  match payload {
    { "protoPayload": { "status": { "message": String(message), .. }, .. }, .. } =>
      message
    { "textPayload": String(message), .. } => message
    _ => ""
  }
}

///|
fn json_string_field(obj : Json, key : String) -> String {
  guard obj is Object(map) else { return "" }
  match map.get(key) {
    Some(String(value)) => value
    _ => ""
  }
}

///|
fn truncate_text(text : String, max_len : Int) -> String {
  if text.length() <= max_len {
    text
  } else {
    "\{text[:max_len]}..."
  }
}

///|
/// build started の重複抑止用に build id を取り出す
pub fn cloud_build_id(payload_json_text : String) -> String? {
  let root = @json.parse(payload_json_text) catch { _ => return None }
  let payload = extract_payload_json(root) catch { _ => return None }
  match payload {
    { "id": String(id), "status": String(_), .. } => Some(id)
    _ => None
  }
}
