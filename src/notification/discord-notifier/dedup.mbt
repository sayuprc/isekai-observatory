///|
/// プロセスローカルな build started 重複抑止
/// コールドスタート後は消えるので、稀な重複は許容する
pub struct StartedBuildDeduper {
  seen : @hashset.HashSet[String]
}

///|
pub fn StartedBuildDeduper::new() -> StartedBuildDeduper {
  { seen: @hashset.HashSet([]) }
}

///|
/// まだ見ていないなら true (通知すべき)
/// すでに見たなら false (スキップ)
pub fn StartedBuildDeduper::should_notify_started(
  self : StartedBuildDeduper,
  build_id : String,
) -> Bool {
  if build_id == "" {
    return true
  }
  self.seen.add_and_check(build_id)
}
