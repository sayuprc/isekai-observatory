import "@typespec/http";
import "@typespec/versioning";
import "../shared";
import "./pagination.tsp";
import "./events";
import "./media";
import "./people";
import "./person-groups";
import "./release-groups";
import "./songs";
import "./venues";

using Http;
using IsekaiObservatory.Shared;
using Versioning;

@service(#{ title: "ヰ世界観測所 Public API" })
@server(
  "https://local.api.isekaijoucho.fan/{version}",
  "ローカル環境",
  {
    version: Version,
  }
)
@versioned(Version)
namespace IsekaiObservatory.Public;

@enumExtensions
enum Version {
  @doc("バージョン 1")
  v1,
}
