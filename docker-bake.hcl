# infra/docker のアプリケーションイメージをまとめてビルドする
# 使い方は infra/README.md の「イメージのビルド (Bake)」を参照

# Artifact Registry の repository。空ならローカル用のタグだけを付ける
variable "REGISTRY" {
  default = ""
}

variable "TAG" {
  default = "local"
}

# 本番に development などの値が紛れないよう、既定値は持たせず呼び出し側で必ず渡す
variable "DEPLOY_ENV" {
  default = ""
}

# Admin の build 時に埋め込む URL
variable "PUBLIC_APP_URL" {
  default = ""
}

# mise.toml の moonbit_version を tools/read-mise-value.sh で読んで渡す
variable "MOONBIT_VERSION" {
  default = ""
}

# Cloud Build では substitution の image 名を渡す
variable "API_IMAGE" {
  default = "api"
}

variable "CLI_IMAGE" {
  default = "cli"
}

variable "DB_MIGRATE_IMAGE" {
  default = "db-migrate"
}

variable "DISCORD_NOTIFIER_IMAGE" {
  default = "discord-notifier"
}

variable "ADMIN_IMAGE" {
  default = "admin"
}

variable "VIEWER_IMAGE" {
  default = "viewer"
}

variable "ADMIN_PROXY_IMAGE" {
  default = "admin-proxy"
}

# 緊急時だけ使う。Dockerfile の FROM / COPY --from に書いたイメージ名 => infra/docker/base 配下のビルドコンテキスト
# 通常は空にして Docker Hub のイメージを使う
# 例: "php:8.5.12-cli-alpine" = "infra/docker/base/php-8.5.12-cli-alpine"
variable "BASE_OVERRIDES" {
  default = {}
}

function "base_target_name" {
  params = [image]
  result = "base-${regex_replace(image, "[^a-zA-Z0-9_-]", "-")}"
}

function "image_tags" {
  params = [name]
  result = REGISTRY == "" ? ["isekai-observatory-${name}:${TAG}"] : ["${REGISTRY}/${name}:${TAG}", "${REGISTRY}/${name}:latest"]
}

function "image_cache_from" {
  params = [name]
  result = REGISTRY == "" ? [] : ["type=registry,ref=${REGISTRY}/${name}:latest"]
}

group "default" {
  targets = [
    "api",
    "cli",
    "db-migrate",
    "discord-notifier",
    "admin",
    "viewer",
    "admin-proxy",
  ]
}

target "base" {
  name    = base_target_name(image)
  matrix  = { image = keys(BASE_OVERRIDES) }
  context = BASE_OVERRIDES[image]
}

target "_common" {
  context  = "."
  contexts = { for image, dir in BASE_OVERRIDES : image => "target:${base_target_name(image)}" }
  # 次回ビルドの --cache-from に使えるよう、push するイメージに cache metadata を埋め込む
  cache-to = ["type=inline"]
}

target "api" {
  inherits   = ["_common"]
  dockerfile = "infra/docker/api/Dockerfile"
  tags       = image_tags(API_IMAGE)
  cache-from = image_cache_from(API_IMAGE)
}

target "cli" {
  inherits   = ["_common"]
  dockerfile = "infra/docker/cli/Dockerfile"
  tags       = image_tags(CLI_IMAGE)
  cache-from = image_cache_from(CLI_IMAGE)
}

target "db-migrate" {
  inherits   = ["_common"]
  dockerfile = "infra/docker/db-migrate/Dockerfile"
  tags       = image_tags(DB_MIGRATE_IMAGE)
  cache-from = image_cache_from(DB_MIGRATE_IMAGE)
  args = {
    DEPLOY_ENV = DEPLOY_ENV
  }
}

target "discord-notifier" {
  inherits   = ["_common"]
  dockerfile = "infra/docker/discord-notifier/Dockerfile"
  tags       = image_tags(DISCORD_NOTIFIER_IMAGE)
  cache-from = image_cache_from(DISCORD_NOTIFIER_IMAGE)
  args = {
    MOONBIT_VERSION = MOONBIT_VERSION
  }
}

target "admin" {
  inherits   = ["_common"]
  dockerfile = "infra/docker/admin/Dockerfile"
  tags       = image_tags(ADMIN_IMAGE)
  cache-from = image_cache_from(ADMIN_IMAGE)
  args = {
    DEPLOY_ENV     = DEPLOY_ENV
    PUBLIC_APP_URL = PUBLIC_APP_URL
  }
}

target "viewer" {
  inherits   = ["_common"]
  dockerfile = "infra/docker/viewer/Dockerfile"
  tags       = image_tags(VIEWER_IMAGE)
  cache-from = image_cache_from(VIEWER_IMAGE)
  args = {
    DEPLOY_ENV      = DEPLOY_ENV
    MOONBIT_VERSION = MOONBIT_VERSION
  }
}

target "admin-proxy" {
  inherits   = ["_common"]
  dockerfile = "infra/docker/admin-proxy/Dockerfile"
  tags       = image_tags(ADMIN_PROXY_IMAGE)
  cache-from = image_cache_from(ADMIN_PROXY_IMAGE)
  args = {
    DEPLOY_ENV = DEPLOY_ENV
  }
}
