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

# Dockerfile の FROM / COPY --from / RUN --mount from= は base-<キー> で参照し、ここで実体を決める
# source = "dockerhub": image を registry から取得する
# source = "src": infra/docker/base/<キー>/ をビルドして使う。緊急時だけ切り替える
# Renovate が image を更新できるよう、1 エントリ 1 行の書式を崩さない
variable "BASE_IMAGES" {
  default = {
    alpine     = { image = "alpine:3.24", source = "dockerhub" }
    bun        = { image = "oven/bun:1.4.4", source = "dockerhub" }
    bun-slim   = { image = "oven/bun:1.4.4-slim", source = "dockerhub" }
    composer   = { image = "composer:2.11.2", source = "dockerhub" }
    debian     = { image = "debian:bookworm-slim", source = "dockerhub" }
    frankenphp = { image = "dunglas/frankenphp:php8.5.12-alpine", source = "dockerhub" }
    mise       = { image = "jdxcode/mise:2026.11.8-debian", source = "dockerhub" }
    node       = { image = "node:26.13.1-slim", source = "dockerhub" }
    php-cli    = { image = "php:8.5.12-cli-alpine", source = "dockerhub" }
    pie        = { image = "ghcr.io/php/pie:1.5.5-bin", source = "dockerhub" }
  }

  validation {
    condition     = length([for k, v in BASE_IMAGES : k if !contains(["dockerhub", "src"], v.source)]) == 0
    error_message = "BASE_IMAGES の source は dockerhub か src にする"
  }
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

target "src" {
  name    = "src-${key}"
  matrix  = { key = [for k, v in BASE_IMAGES : k if v.source == "src"] }
  context = "infra/docker/base/${key}"
}

target "_common" {
  context  = "."
  contexts = { for k, v in BASE_IMAGES : "base-${k}" => v.source == "src" ? "target:src-${k}" : "docker-image://${v.image}" }
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

# -- ローカル開発 / server-qa 用イメージ (infra/local/docker) --
# image 名は mise.toml / mise.ci.toml の [env] から渡す
# ベースイメージは BASE_IMAGES を通さない。mise.toml がタグを Dockerfile の FROM から読むため

variable "PROXY_IMAGE" {
  default = "isekai-observatory-proxy:local"
}

variable "SERVER_IMAGE" {
  default = "isekai-observatory-api:local"
}

variable "OPENAPI_IMAGE" {
  default = "openapi-generator:local"
}

variable "MYSQL_IMAGE" {
  default = "isekai-observatory-mysql:local"
}

# bash の UID は readonly で `UID=... docker buildx bake` と渡せないため HOST_ を付ける
variable "HOST_UID" {
  default = "1000"
}

variable "HOST_GID" {
  default = "1000"
}

variable "HOST_USERNAME" {
  default = "user"
}

variable "HOST_GROUPNAME" {
  default = "user"
}

group "local" {
  targets = [
    "local-proxy",
    "local-php",
    "local-openapi-generator",
    "local-mysql",
  ]
}

target "local-proxy" {
  context = "infra/local/docker/nginx"
  tags    = [PROXY_IMAGE]
}

target "local-php" {
  context = "infra/local/docker/php"
  tags    = [SERVER_IMAGE]
  args = {
    UID       = HOST_UID
    GID       = HOST_GID
    USERNAME  = HOST_USERNAME
    GROUPNAME = HOST_GROUPNAME
  }
}

target "local-openapi-generator" {
  context = "infra/local/docker/openapi-generator"
  tags    = [OPENAPI_IMAGE]
}

target "local-mysql" {
  context = "infra/local/docker/mysql"
  tags    = [MYSQL_IMAGE]
}
