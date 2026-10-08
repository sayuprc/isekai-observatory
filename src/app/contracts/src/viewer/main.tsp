import "@typespec/http";
import "@typespec/versioning";
import "../packages/media";
import "../packages/song";
import "../shared";
import "./events";
import "./media";
import "./releases";
import "./site-stats";
import "./songs";

using Http;
using IsekaiObservatory.Shared;
using Versioning;

@service(#{ title: "ヰ世界観測所 API" })
@server(
  "https://local.api.isekaijoucho.fan/{version}",
  "ローカル環境",
  {
    version: Version,
  }
)
@versioned(Version)
namespace IsekaiObservatory.Viewer;

@enumExtensions
enum Version {
  @doc("バージョン 1")
  v1,
}
