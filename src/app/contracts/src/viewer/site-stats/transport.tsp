namespace IsekaiObservatory.Viewer;

@example(#{ songCount: 42, releaseCount: 12 })
model SiteStatsResponse {
  @doc("公開対象楽曲数")
  songCount: int32;

  @doc("公開対象リリースグループ数")
  releaseCount: int32;
}
