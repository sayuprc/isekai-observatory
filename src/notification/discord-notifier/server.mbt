///|
async fn respond(
  conn : @http.ServerConnection,
  code : Int,
  reason : String,
  body? : String = "",
) -> Unit {
  conn.send_response(code, reason, extra_headers={
    "Content-Type": "text/plain; charset=utf-8",
  })
  if body != "" {
    conn.write_string(body)
  }
  conn.end_response()
}

///|
pub async fn serve(app : App) -> Unit {
  let addr = @socket.Addr::parse("[::]:\{app.config.port}")
  let server = @http.Server(addr)
  println("discord-notifier listening on \{server.addr}")
  server.run_forever() <| ((request, body, conn) => {
    match request.meth {
      Get => respond(conn, 200, "OK", body="ok")
      Post => {
        let text = body.read_all().text() catch {
            err => {
              // 一時失敗として Pub/Sub に再送させる (恒久失敗の Ack と区別する)
              println("retry: failed to read body: \{err}")
              respond(
                conn,
                500,
                "Internal Server Error",
                body="failed to read body",
              )
              return
            }
          }
        match app.handle_body(text) {
          Ack => respond(conn, 204, "No Content")
          Retry(msg) => {
            println("retry: \{msg}")
            respond(conn, 500, "Internal Server Error", body=msg)
          }
        }
      }
      _ => respond(conn, 405, "Method Not Allowed")
    }
  })
}
