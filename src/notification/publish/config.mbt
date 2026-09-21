///|
pub(all) struct Config {
  project : String
  topic : String
  metadata_base_url : String
} derive(Eq, Debug)

///|
pub fn Config::from_env() -> Config raise {
  let project = required_env("GOOGLE_CLOUD_PROJECT")
  let topic = required_env("NOTIFICATION_TOPIC")
  let metadata_base_url = match @env.get_env_var("GCE_METADATA_HOST") {
    Some(host) =>
      if host == "" {
        "http://metadata.google.internal"
      } else {
        "http://\{host}"
      }
    None => "http://metadata.google.internal"
  }
  { project, topic, metadata_base_url }
}

///|
fn required_env(name : String) -> String raise {
  match @env.get_env_var(name) {
    Some(value) =>
      if value == "" {
        raise Failure("\{name} must not be empty")
      } else {
        value
      }
    None => raise Failure("\{name} is required")
  }
}

///|
pub fn Config::publish_url(self : Config) -> String {
  "https://pubsub.googleapis.com/v1/projects/\{self.project}/topics/\{self.topic}:publish"
}

///|
pub fn Config::access_token_url(self : Config) -> String {
  "\{self.metadata_base_url}/computeMetadata/v1/instance/service-accounts/default/token"
}
