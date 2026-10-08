import "@typespec/http";
import "@typespec/versioning";
import "../packages/media";
import "../packages/song";
import "../shared";
import "./admin-users";
import "./audit-logs";
import "./auth";
import "./events";
import "./media";
import "./persons";
import "./person-groups";
import "./releases";
import "./song-tags";
import "./songs";
import "./venues";

using Http;
using IsekaiObservatory.Shared;
using Versioning;

@service(#{ title: "ヰ世界観測所 管理画面向け API" })
@server(
  "https://local.api.isekaijoucho.fan/admin/{version}",
  "ローカル環境",
  {
    version: Version,
  }
)
@versioned(Version)
namespace IsekaiObservatory.Admin;

@enumExtensions
enum Version {
  @doc("バージョン 1")
  v1,
}
