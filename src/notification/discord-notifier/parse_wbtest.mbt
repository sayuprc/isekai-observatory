///|
fn encode_pubsub(inner : String) -> String {
  let data = @base64.encode(@utf8.encode(inner))
  "{\"message\":{\"data\":\"\{data}\"}}"
}

///|
test "parse app notification raw" {
  let body = "{\"action\":\"media.youtube_import\",\"status\":\"failed\",\"embeds\":[{\"title\":\"boom\",\"fields\":[{\"name\":\"executed_at\",\"value\":\"2026-01-01T00:00:00Z\"}]}]}"
  let notification = parse_request_body(
    body,
    cloud_build_trigger_name="dev-isekai-ci",
  )
  guard notification is Some(n) else { fail("expected notification") }
  assert_eq(n.action, "media.youtube_import")
  assert_eq(n.status, "failed")
  assert_eq(n.content, None)
  guard n.embeds is Some(embeds) else { fail("expected embeds") }
  guard embeds is Array(items) else { fail("expected array") }
  assert_eq(items.length(), 1)
}

///|
test "parse app notification with content" {
  let body = "{\"action\":\"media.youtube_import\",\"status\":\"failed\",\"content\":\"取り込み失敗\",\"embeds\":[{\"title\":\"boom\"}]}"
  let notification = parse_request_body(
    body,
    cloud_build_trigger_name="dev-isekai-ci",
  )
  guard notification is Some(n) else { fail("expected notification") }
  assert_eq(n.content, Some("取り込み失敗"))
  guard n.embeds is Some(_) else { fail("expected embeds") }
}

///|
test "parse app notification content only" {
  let body = "{\"action\":\"media.youtube_import\",\"status\":\"failed\",\"content\":\"取り込み失敗\"}"
  let notification = parse_request_body(
    body,
    cloud_build_trigger_name="dev-isekai-ci",
  )
  guard notification is Some(n) else { fail("expected notification") }
  assert_eq(n.content, Some("取り込み失敗"))
  assert_eq(n.embeds, None)
}

///|
test "parse app notification ignores empty content when embeds present" {
  let body = "{\"action\":\"media.youtube_import\",\"status\":\"failed\",\"content\":\"\",\"embeds\":[{\"title\":\"boom\"}]}"
  let notification = parse_request_body(
    body,
    cloud_build_trigger_name="dev-isekai-ci",
  )
  guard notification is Some(n) else { fail("expected notification") }
  assert_eq(n.content, None)
  guard n.embeds is Some(_) else { fail("expected embeds") }
}

///|
test "parse app notification ignores empty embeds when content present" {
  let body = "{\"action\":\"media.youtube_import\",\"status\":\"failed\",\"content\":\"取り込み失敗\",\"embeds\":[]}"
  let notification = parse_request_body(
    body,
    cloud_build_trigger_name="dev-isekai-ci",
  )
  guard notification is Some(n) else { fail("expected notification") }
  assert_eq(n.content, Some("取り込み失敗"))
  assert_eq(n.embeds, None)
}

///|
test "parse app notification via pubsub envelope" {
  let inner = "{\"action\":\"admin.invite\",\"status\":\"succeeded\",\"embeds\":[{\"title\":\"ok\"}]}"
  let body = encode_pubsub(inner)
  let notification = parse_request_body(
    body,
    cloud_build_trigger_name="dev-isekai-ci",
  )
  guard notification is Some(n) else { fail("expected notification") }
  assert_eq(n.action, "admin.invite")
  assert_eq(n.status, "succeeded")
}

///|
test "parse app notification requires action" {
  let err = @test.expect_error(() => {
    parse_request_body(
      "{\"status\":\"failed\",\"embeds\":[{\"title\":\"boom\"}]}",
      cloud_build_trigger_name="dev-isekai-ci",
    )
    |> ignore
  })
  guard err is InvalidPayload(_) else { fail("expected InvalidPayload") }
}

///|
test "parse app notification requires content or embeds" {
  let err = @test.expect_error(() => {
    parse_request_body(
      "{\"action\":\"admin.invite\",\"status\":\"succeeded\"}",
      cloud_build_trigger_name="dev-isekai-ci",
    )
    |> ignore
  })
  guard err is InvalidPayload(_) else { fail("expected InvalidPayload") }
}

///|
test "parse app notification rejects empty content and empty embeds" {
  let err = @test.expect_error(() => {
    parse_request_body(
      "{\"action\":\"admin.invite\",\"status\":\"succeeded\",\"content\":\"\",\"embeds\":[]}",
      cloud_build_trigger_name="dev-isekai-ci",
    )
    |> ignore
  })
  guard err is InvalidPayload(_) else { fail("expected InvalidPayload") }
}

///|
test "parse cloud build working" {
  let inner = "{\"id\":\"build-1\",\"status\":\"WORKING\",\"logUrl\":\"https://example.com/log\",\"substitutions\":{\"TRIGGER_NAME\":\"dev-isekai-ci\"}}"
  let notification = parse_request_body(
    encode_pubsub(inner),
    cloud_build_trigger_name="dev-isekai-ci",
  )
  guard notification is Some(n) else { fail("expected notification") }
  assert_eq(n.action, "cloud_build")
  assert_eq(n.status, "started")
  assert_eq(n.content, None)
  guard n.embeds is Some(embeds) else { fail("expected embeds") }
  guard embeds
    is [
      {
        "title": String(title),
        "url": String(url),
        "fields": Array(fields),
        ..
      },
    ] else {
    fail("expected embed")
  }
  assert_eq(title, "Cloud Build が開始しました")
  assert_eq(url, "https://example.com/log")
  assert_eq(fields.length(), 2)
}

///|
test "parse cloud build without log url" {
  let inner = "{\"id\":\"build-2\",\"status\":\"SUCCESS\",\"substitutions\":{\"TRIGGER_NAME\":\"dev-isekai-ci\"}}"
  let notification = parse_request_body(
    encode_pubsub(inner),
    cloud_build_trigger_name="dev-isekai-ci",
  )
  guard notification is Some(n) else { fail("expected notification") }
  assert_eq(n.status, "succeeded")
  guard n.embeds is Some(embeds) else { fail("expected embeds") }
  guard embeds is Array(items) else { fail("expected array") }
  assert_eq(items.length(), 1)
  guard items[0] is Object(map) else { fail("expected object") }
  guard map.get("title") is Some(String(title)) else { fail("expected title") }
  assert_eq(title, "Cloud Build が成功しました")
  assert_eq(map.get("url"), None)
  guard map.get("fields") is Some(Array(fields)) else {
    fail("expected fields")
  }
  assert_eq(fields.length(), 2)
}

///|
test "parse cloud build ignores other trigger" {
  let inner = "{\"id\":\"build-1\",\"status\":\"SUCCESS\",\"substitutions\":{\"TRIGGER_NAME\":\"other\"}}"
  let notification = parse_request_body(
    encode_pubsub(inner),
    cloud_build_trigger_name="dev-isekai-ci",
  )
  assert_eq(notification, None)
}

///|
test "parse cloud build failed statuses" {
  let statuses = ["FAILURE", "INTERNAL_ERROR", "TIMEOUT", "CANCELLED"]
  for status in statuses {
    let inner = "{\"id\":\"b\",\"status\":\"\{status}\",\"substitutions\":{\"TRIGGER_NAME\":\"dev-isekai-ci\"}}"
    let notification = parse_request_body(
      encode_pubsub(inner),
      cloud_build_trigger_name="dev-isekai-ci",
    )
    guard notification is Some(n) else { fail("expected \{status}") }
    assert_eq(n.action, "cloud_build")
    assert_eq(n.status, "failed")
  }
}

///|
test "parse cloud run job log entry via pubsub" {
  let inner = "{\"resource\":{\"type\":\"cloud_run_job\",\"labels\":{\"job_name\":\"prod-media-youtube-import\",\"location\":\"asia-northeast1\"}},\"labels\":{\"run.googleapis.com/execution_name\":\"prod-media-youtube-import-xxxxx\"},\"protoPayload\":{\"status\":{\"message\":\"Execution prod-media-youtube-import-xxxxx has failed to complete.\"}}}"
  let notification = parse_request_body(
    encode_pubsub(inner),
    cloud_build_trigger_name="dev-isekai-ci",
  )
  guard notification is Some(n) else { fail("expected notification") }
  assert_eq(n.action, "cloud_run_job")
  assert_eq(n.status, "failed")
  assert_eq(n.content, None)
  guard n.embeds is Some(embeds) else { fail("expected embeds") }
  guard embeds is [{ "title": String(title), "fields": Array(fields), .. }] else {
    fail("expected embed")
  }
  assert_eq(title, "Cloud Run Job failed: prod-media-youtube-import")
  assert_eq(fields.length(), 4)
  guard fields[0] is { "name": String("job"), "value": String(job), .. } else {
    fail("expected job field")
  }
  assert_eq(job, "prod-media-youtube-import")
  guard fields[1]
    is { "name": String("execution"), "value": String(execution), .. } else {
    fail("expected execution field")
  }
  assert_eq(execution, "prod-media-youtube-import-xxxxx")
  guard fields[2]
    is { "name": String("location"), "value": String(location), .. } else {
    fail("expected location field")
  }
  assert_eq(location, "asia-northeast1")
  guard fields[3] is { "name": String("message"), "value": String(message), .. } else {
    fail("expected message field")
  }
  assert_eq(
    message, "Execution prod-media-youtube-import-xxxxx has failed to complete.",
  )
}

///|
test "parse cloud run job prefers textPayload when proto message missing" {
  let inner = "{\"resource\":{\"type\":\"cloud_run_job\",\"labels\":{\"job_name\":\"dev-job\",\"location\":\"asia-northeast1\"}},\"labels\":{\"run.googleapis.com/execution_name\":\"dev-job-1\"},\"textPayload\":\"task failed\"}"
  let notification = parse_request_body(
    encode_pubsub(inner),
    cloud_build_trigger_name="dev-isekai-ci",
  )
  guard notification is Some(n) else { fail("expected notification") }
  guard n.embeds is Some([{ "fields": Array(fields), .. }]) else {
    fail("expected embeds")
  }
  guard fields[3] is { "name": String("message"), "value": String(message), .. } else {
    fail("expected message")
  }
  assert_eq(message, "task failed")
}

///|
test "parse cloud run job truncates long message" {
  let long = String::make(1200, 'a')
  let inner = "{\"resource\":{\"type\":\"cloud_run_job\",\"labels\":{\"job_name\":\"dev-job\",\"location\":\"asia-northeast1\"}},\"labels\":{\"run.googleapis.com/execution_name\":\"dev-job-1\"},\"textPayload\":\"\{long}\"}"
  let notification = parse_request_body(
    encode_pubsub(inner),
    cloud_build_trigger_name="dev-isekai-ci",
  )
  guard notification is Some(n) else { fail("expected notification") }
  guard n.embeds is Some([{ "fields": Array(fields), .. }]) else {
    fail("expected embeds")
  }
  guard fields[3] is { "name": String("message"), "value": String(message), .. } else {
    fail("expected message")
  }
  assert_eq(message.length(), 1003)
  assert_true(message.has_suffix("..."))
}

///|
test "parse ignores non cloud_run_job log entry" {
  let inner = "{\"resource\":{\"type\":\"cloud_run_revision\",\"labels\":{\"service_name\":\"api\"}},\"textPayload\":\"hello\"}"
  let notification = parse_request_body(
    encode_pubsub(inner),
    cloud_build_trigger_name="dev-isekai-ci",
  )
  assert_eq(notification, None)
}

///|
test "resolve channel from action routing" {
  let config : Config = {
    default_channel: "app",
    action_routing: {
      "cloud_build": "build",
      "cloud_run_job": "job_fail",
      "ops.alert": "ops",
    },
    cloud_build_trigger_name: "dev-isekai-ci",
    port: 8080,
  }
  assert_eq(config.resolve_channel("cloud_build"), "build")
  assert_eq(config.resolve_channel("cloud_run_job"), "job_fail")
  assert_eq(config.resolve_channel("ops.alert"), "ops")
  assert_eq(config.resolve_channel("media.youtube_import"), "app")
  assert_eq(config.resolve_channel(""), "app")
  assert_eq(webhook_env_name("build"), "DISCORD_WEBHOOK_BUILD")
  assert_eq(webhook_env_name("app"), "DISCORD_WEBHOOK_APP")
  assert_eq(webhook_env_name("job_fail"), "DISCORD_WEBHOOK_JOB_FAIL")
}

///|
test "dedupe started builds" {
  let deduper = StartedBuildDeduper::new()
  assert_true(deduper.should_notify_started("build-1"))
  assert_false(deduper.should_notify_started("build-1"))
  assert_true(deduper.should_notify_started("build-2"))
}

///|
test "discord payload fills status color when missing" {
  let embeds : Json = [
    {
      "title": "取り込みでエラーが発生しました",
      "fields": [
        {
          "name": "executed_at",
          "value": "2026-01-01T00:00:00Z",
          "inline": true,
        },
      ],
    },
  ]
  let payload = build_discord_payload({
    action: "media.youtube_import",
    status: "failed",
    content: None,
    embeds: Some(embeds),
  })
  guard payload
    is {
      "embeds": [
        {
          "title": String(title),
          "color": Number(color, ..),
          "fields": fields,
          ..
        },
      ],
      ..
    } else {
    fail("expected colored embed")
  }
  assert_eq(title, "取り込みでエラーが発生しました")
  assert_eq(color.to_int(), 0xED4245)
  guard fields is Array(items) else { fail("expected fields") }
  assert_eq(items.length(), 1)
  assert_eq(
    match payload {
      { "content": _, .. } => true
      _ => false
    },
    false,
  )
}

///|
test "discord payload includes content when present" {
  let embeds : Json = [{ "title": "boom" }]
  let payload = build_discord_payload({
    action: "media.youtube_import",
    status: "failed",
    content: Some("取り込み失敗"),
    embeds: Some(embeds),
  })
  guard payload is { "content": String(content), "embeds": Array(items), .. } else {
    fail("expected content and embeds")
  }
  assert_eq(content, "取り込み失敗")
  assert_eq(items.length(), 1)
}

///|
test "discord payload content only" {
  let payload = build_discord_payload({
    action: "media.youtube_import",
    status: "failed",
    content: Some("取り込み失敗"),
    embeds: None,
  })
  guard payload is { "content": String(content), .. } else {
    fail("expected content")
  }
  assert_eq(content, "取り込み失敗")
  assert_eq(
    match payload {
      { "embeds": _, .. } => true
      _ => false
    },
    false,
  )
}

///|
test "discord payload keeps explicit embed color" {
  let embeds : Json = [{ "title": "custom", "color": 1 }]
  let payload = build_discord_payload({
    action: "media.youtube_import",
    status: "failed",
    content: None,
    embeds: Some(embeds),
  })
  guard payload is { "embeds": [{ "color": Number(color, ..), .. }], .. } else {
    fail("expected embed")
  }
  assert_eq(color.to_int(), 1)
}

///|
test "discord payload colors by status" {
  let ok_embeds : Json = [{ "title": "ok" }]
  let succeeded = build_discord_payload({
    action: "cloud_build",
    status: "succeeded",
    content: None,
    embeds: Some(ok_embeds),
  })
  guard succeeded is { "embeds": [{ "color": Number(color, ..), .. }], .. } else {
    fail("expected embed")
  }
  assert_eq(color.to_int(), 0x57F287)

  let working_embeds : Json = [{ "title": "working" }]
  let started = build_discord_payload({
    action: "cloud_build",
    status: "started",
    content: None,
    embeds: Some(working_embeds),
  })
  guard started
    is { "embeds": [{ "color": Number(started_color, ..), .. }], .. } else {
    fail("expected embed")
  }
  assert_eq(started_color.to_int(), 0x3498DB)
}
