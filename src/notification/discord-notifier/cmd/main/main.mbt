///|
async fn main {
  let config = @lib.Config::from_env() catch {
    err => {
      println("invalid config: \{err}")
      return
    }
  }
  let app = @lib.App::new(config)
  @lib.serve(app) catch {
    err => println("server stopped: \{err}")
  }
}
