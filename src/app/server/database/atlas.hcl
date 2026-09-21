variable "table_schemas" {
  type = list(string)
  default = [
    "file://schemas/schema.my.hcl",
    "file://schemas/admin-users.my.hcl",
    "file://schemas/admin-user-permissions.my.hcl",
    "file://schemas/admin-user-passkeys.my.hcl",
    "file://schemas/admin-user-recovery-codes.my.hcl",
    "file://schemas/persons.my.hcl",
    "file://schemas/venues.my.hcl",
    "file://schemas/songs.my.hcl",
    "file://schemas/media.my.hcl",
    "file://schemas/song-tags.my.hcl",
    "file://schemas/song-taggings.my.hcl",
    "file://schemas/song-persons.my.hcl",
    "file://schemas/song-media-links.my.hcl",
    "file://schemas/release-groups.my.hcl",
    "file://schemas/releases.my.hcl",
    "file://schemas/release-formats.my.hcl",
    "file://schemas/release-media.my.hcl",
    "file://schemas/release-tracks.my.hcl",
    "file://schemas/refresh-tokens.my.hcl",
    "file://schemas/admin-user-registration-tokens.my.hcl",
    "file://schemas/admin-user-registration-token-permissions.my.hcl",
    "file://schemas/audit-logs.my.hcl",
    "file://schemas/youtube-channels.my.hcl",
  ]
}

env "local" {
  src = var.table_schemas
  url = "mysql://${getenv("DB_USERNAME")}:${getenv("DB_PASSWORD")}@localhost:${getenv("ATLAS_DB_PORT")}/${getenv("DB_DATABASE")}"
}

env "testing" {
  src = var.table_schemas
  url = "mysql://${getenv("DB_USERNAME")}:${getenv("DB_PASSWORD")}@localhost:${getenv("ATLAS_DB_PORT")}/${getenv("DB_DATABASE")}"
}

env "dev" {
  src = var.table_schemas
  url = "mysql://${getenv("DB_USERNAME")}:${urlescape(getenv("DB_PASSWORD"))}@${getenv("DB_HOST")}:${getenv("DB_PORT")}/${getenv("DB_DATABASE")}?tls=true"
}

env "staging" {
  src = var.table_schemas
  url = "mysql://${getenv("DB_USERNAME")}:${urlescape(getenv("DB_PASSWORD"))}@${getenv("DB_HOST")}:${getenv("DB_PORT")}/${getenv("DB_DATABASE")}?tls=true"
}

env "production" {
  src = var.table_schemas
  url = "mysql://${getenv("DB_USERNAME")}:${urlescape(getenv("DB_PASSWORD"))}@${getenv("DB_HOST")}:${getenv("DB_PORT")}/${getenv("DB_DATABASE")}?tls=true"
}
