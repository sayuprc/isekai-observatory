import "@typespec/http";
import "../../shared";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Viewer;

@tag("SiteStats")
@route("/site-stats")
interface SiteStatsService {
  @doc("サイト集計取得API")
  getSiteStats(): Ok<SiteStatsResponse> | InternalServerError;
}
