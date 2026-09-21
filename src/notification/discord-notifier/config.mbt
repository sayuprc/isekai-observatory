///|
pub(all) struct Config {
  default_channel : String
  action_routing : Map[String, String]
  cloud_build_trigger_name : String
  port : Int
} derive(Eq, Debug)

///|
pub fn Config::from_env() -> Config raise {
  let default_channel = match @env.get_env_var("DISCORD_DEFAULT_CHANNEL") {
    Some(value) => if value == "" { "app" } else { value }
    None => "app"
  }
  let action_routing = match @env.get_env_var("DISCORD_ACTION_ROUTING") {
    None | Some("") => Map([])
    Some(raw) => {
      let json = @json.parse(raw) catch {
        err => raise Failure("DISCORD_ACTION_ROUTING: \{err}")
      }
      @json.from_json(json) catch {
        err => raise Failure("DISCORD_ACTION_ROUTING: \{err}")
      }
    }
  }
  let cloud_build_trigger_name = match
    @env.get_env_var("CLOUD_BUILD_TRIGGER_NAME") {
    Some(value) => value
    None => ""
  }
  let port = match @env.get_env_var("PORT") {
    None | Some("") => 8080
    Some(raw) =>
      @string.parse_int(raw) catch {
        _ => raise Failure("PORT must be an integer: \{raw}")
      }
  }
  { default_channel, action_routing, cloud_build_trigger_name, port }
}

///|
/// channel `build` -> env `DISCORD_WEBHOOK_BUILD`
pub fn webhook_env_name(channel : String) -> String {
  "DISCORD_WEBHOOK_\{channel.to_upper()}"
}

///|
/// action を channel に解決する
/// ROUTING に無い action は default_channel へ送る
pub fn Config::resolve_channel(self : Config, action : String) -> String {
  match self.action_routing.get(action) {
    Some(channel) => channel
    None => self.default_channel
  }
}

///|
pub fn Config::resolve_webhook_url(self : Config, action : String) -> String? {
  let channel = self.resolve_channel(action)
  let env_name = webhook_env_name(channel)
  match @env.get_env_var(env_name) {
    Some(url) => if url == "" { None } else { Some(url) }
    None => None
  }
}
