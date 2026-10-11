///|
test "validate accepts content only" {
  validate_notification_json(
    "{\"action\":\"viewer.deploy\",\"status\":\"succeeded\",\"content\":\"ok\"}",
  )
}

///|
test "validate accepts embeds only" {
  validate_notification_json(
    "{\"action\":\"viewer.deploy\",\"status\":\"succeeded\",\"embeds\":[{\"title\":\"ok\"}]}",
  )
}

///|
test "validate rejects missing content and embeds" {
  let err = @test.expect_error(() => {
    validate_notification_json(
      "{\"action\":\"viewer.deploy\",\"status\":\"succeeded\"}",
    )
  })
  guard err
    is @notify_contract.InvalidPayload(
      "content or non-empty embeds is required"
    ) else {
    fail("expected InvalidPayload")
  }
}

///|
test "validate rejects empty embeds" {
  let err = @test.expect_error(() => {
    validate_notification_json(
      "{\"action\":\"viewer.deploy\",\"status\":\"succeeded\",\"embeds\":[]}",
    )
  })
  guard err
    is @notify_contract.InvalidPayload(
      "content or non-empty embeds is required"
    ) else {
    fail("expected InvalidPayload")
  }
}

///|
test "validate rejects invalid json" {
  let err = @test.expect_error(() => validate_notification_json("{"))
  guard err is @notify_contract.InvalidJson(_) else {
    fail("expected InvalidJson")
  }
}

///|
test "build_publish_body encodes payload" {
  let payload = "{\"action\":\"viewer.deploy\",\"status\":\"succeeded\",\"content\":\"ok\"}"
  let body = build_publish_body(payload)
  guard body is { "messages": [{ "data": String(data), .. }], .. } else {
    fail("unexpected publish body shape")
  }
  let decoded = @base64.decode(data)
  let text = @utf8.decode(decoded)
  assert_eq(text, payload)
}

///|
test "config urls" {
  let config : Config = {
    project: "my-project",
    topic: "dev-discord-notify",
    metadata_base_url: "http://metadata.google.internal",
  }
  assert_eq(
    config.publish_url(),
    "https://pubsub.googleapis.com/v1/projects/my-project/topics/dev-discord-notify:publish",
  )
  assert_eq(
    config.access_token_url(),
    "http://metadata.google.internal/computeMetadata/v1/instance/service-accounts/default/token",
  )
}
