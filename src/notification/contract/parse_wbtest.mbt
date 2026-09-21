///|
test "parse accepts content only" {
  let n = parse_notification_json(
    "{\"action\":\"viewer.deploy\",\"status\":\"succeeded\",\"content\":\"ok\"}",
  )
  assert_eq(n.action, "viewer.deploy")
  assert_eq(n.status, "succeeded")
  assert_eq(n.content, Some("ok"))
  assert_eq(n.embeds, None)
}

///|
test "parse accepts embeds only" {
  let n = parse_notification_json(
    "{\"action\":\"viewer.deploy\",\"status\":\"succeeded\",\"embeds\":[{\"title\":\"ok\"}]}",
  )
  assert_eq(n.content, None)
  guard n.embeds is Some(embeds) else { fail("expected embeds") }
  guard embeds is Array(items) else { fail("expected array") }
  assert_eq(items.length(), 1)
}

///|
test "parse ignores empty content when embeds present" {
  let n = parse_notification_json(
    "{\"action\":\"viewer.deploy\",\"status\":\"succeeded\",\"content\":\"\",\"embeds\":[{\"title\":\"ok\"}]}",
  )
  assert_eq(n.content, None)
  guard n.embeds is Some(_) else { fail("expected embeds") }
}

///|
test "parse ignores empty embeds when content present" {
  let n = parse_notification_json(
    "{\"action\":\"viewer.deploy\",\"status\":\"succeeded\",\"content\":\"ok\",\"embeds\":[]}",
  )
  assert_eq(n.content, Some("ok"))
  assert_eq(n.embeds, None)
}

///|
test "parse rejects missing content and embeds" {
  let err = @test.expect_error(() => {
    parse_notification_json(
      "{\"action\":\"viewer.deploy\",\"status\":\"succeeded\"}",
    )
    |> ignore
  })
  guard err is InvalidPayload("content or non-empty embeds is required") else {
    fail("expected InvalidPayload")
  }
}

///|
test "parse rejects empty embeds" {
  let err = @test.expect_error(() => {
    parse_notification_json(
      "{\"action\":\"viewer.deploy\",\"status\":\"succeeded\",\"embeds\":[]}",
    )
    |> ignore
  })
  guard err is InvalidPayload("content or non-empty embeds is required") else {
    fail("expected InvalidPayload")
  }
}

///|
test "parse rejects empty action" {
  let err = @test.expect_error(() => {
    parse_notification_json(
      "{\"action\":\"\",\"status\":\"succeeded\",\"content\":\"ok\"}",
    )
    |> ignore
  })
  guard err is InvalidPayload("action must not be empty") else {
    fail("expected InvalidPayload")
  }
}

///|
test "parse rejects empty status" {
  let err = @test.expect_error(() => {
    parse_notification_json(
      "{\"action\":\"viewer.deploy\",\"status\":\"\",\"content\":\"ok\"}",
    )
    |> ignore
  })
  guard err is InvalidPayload("status must not be empty") else {
    fail("expected InvalidPayload")
  }
}

///|
test "parse rejects invalid json" {
  let err = @test.expect_error(() => parse_notification_json("{") |> ignore)
  guard err is InvalidJson(_) else { fail("expected InvalidJson") }
}

///|
test "validate accepts valid payload" {
  validate_notification_json(
    "{\"action\":\"viewer.deploy\",\"status\":\"succeeded\",\"content\":\"ok\"}",
  )
}
