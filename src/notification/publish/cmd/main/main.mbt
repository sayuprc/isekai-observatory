///|
async fn main {
  @lib.run() catch {
    err => abort("notify-publish failed: \{err}")
  }
}
