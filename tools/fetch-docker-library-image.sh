#!/usr/bin/env bash
# Docker Official Image のビルドコンテキストを infra/docker/base/<キー>/ に取得する
# イメージは docker-bake.hcl の BASE_IMAGES から読み、取得元は docker-library/official-images から引く
# そのため Docker Hub がビルドしたものと同じソースになる
# 使い方: tools/fetch-docker-library-image.sh [BASE_IMAGES のキー]
#   キーを省くと source = "src" のエントリのうち、infra/docker/base/<キー>/ がまだ無いものをすべて取得する
#   ARCH: official-images のアーキテクチャ名 (既定値: amd64)
#   OFFICIAL_IMAGES_REF: 読み込む official-images の commit かブランチ (既定値: master)
set -euo pipefail

if [[ $# -gt 1 ]]; then
  echo "usage: $0 [BASE_IMAGES key]" >&2
  exit 2
fi

arch=${ARCH:-amd64}
official_images_ref=${OFFICIAL_IMAGES_REF:-master}
repo_root=$(cd "$(dirname "$0")/.." && pwd)
bake_file="${repo_root}/docker-bake.hcl"

# BASE_IMAGES は 1 エントリ 1 行で書く前提で読む
entry_pattern='^[[:space:]]*\([a-z0-9-]*\)[[:space:]]*=[[:space:]]*{[[:space:]]*image[[:space:]]*=[[:space:]]*"\([^"]*\)"[[:space:]]*,[[:space:]]*source[[:space:]]*=[[:space:]]*"\([^"]*\)".*'

fetch() {
  local key=$1
  local dest="${repo_root}/infra/docker/base/${key}"
  local ref

  ref=$(sed -n "s/${entry_pattern}/\1 \2/p" "$bake_file" | awk -v key="$key" '$1 == key { print $2; exit }')

  if [[ -z "$ref" ]]; then
    echo "key not found in BASE_IMAGES of docker-bake.hcl: $key" >&2
    return 1
  fi

  if [[ ! "$ref" =~ ^([a-z0-9._-]+):([A-Za-z0-9._-]+)$ ]]; then
    echo "not a Docker Official Image (expected <library image>:<tag>): $ref" >&2
    echo "prepare infra/docker/base/${key}/Dockerfile by hand instead" >&2
    return 1
  fi

  local image=${BASH_REMATCH[1]}
  local tag=${BASH_REMATCH[2]}

  if [[ -e "$dest" ]]; then
    echo "already exists: $dest" >&2
    return 1
  fi

  # `( fetch ) || ...` の中では set -e が効かないため、失敗は明示的に return する
  library=$(curl -fsSL "https://raw.githubusercontent.com/docker-library/official-images/${official_images_ref}/library/${image}") || return 1

  # 先頭の全体設定の後に、空行区切りでエントリが並ぶ
  # フィールドは エントリのアーキテクチャ別 > エントリ > 全体のアーキテクチャ別 > 全体 の順に探す
  read -r git_repo git_commit directory builder < <(
    printf '%s\n' "$library" | awk -v tag="$tag" -v arch="$arch" '
      function field(name, fallback) {
        if ((arch "-" name) in e) return e[arch "-" name]
        if (name in e) return e[name]
        if ((arch "-" name) in g) return g[arch "-" name]
        if (name in g) return g[name]
        return fallback
      }
      function flush() {
        if (found) {
          print field("GitRepo", ""), field("GitCommit", ""), field("Directory", "."), field("Builder", "buildkit")
          exit
        }
        delete e
      }
      /^[[:space:]]*$/ { if (in_entry) flush(); in_entry = 0; next }
      /^[A-Za-z0-9-]+:/ {
        key = $0; sub(/:.*/, "", key)
        value = $0; sub(/^[^:]+:[[:space:]]*/, "", value)
        if (key == "Tags" || key == "SharedTags") {
          in_entry = 1
          n = split(value, tags, /,[[:space:]]*/)
          for (i = 1; i <= n; i++) if (tags[i] == tag) found = 1
        }
        if (in_entry) e[key] = value; else g[key] = value
      }
      END { if (in_entry) flush() }
    '
  ) || true

  if [[ -z "${git_commit:-}" ]]; then
    echo "tag not found in official-images@${official_images_ref}: ${image}:${tag} (${arch})" >&2
    echo "set OFFICIAL_IMAGES_REF to an older official-images commit if the tag has been removed" >&2
    return 1
  fi

  if [[ ! "${git_repo%.git}" =~ ^https://github\.com/([^/]+/[^/]+)$ ]]; then
    echo "unsupported GitRepo (only github.com is supported): $git_repo" >&2
    return 1
  fi

  github_repo=${BASH_REMATCH[1]}

  # oci-import などは Dockerfile が無く、書き換えてビルドする対象にならない
  if [[ "$builder" != "buildkit" && "$builder" != "classic" ]]; then
    echo "unsupported Builder: ${builder} (${image}:${tag} is not built from a Dockerfile)" >&2
    return 1
  fi

  directory=${directory%/}
  work=$(mktemp -d)
  trap 'rm -rf "$work"' EXIT

  curl -fsSL "https://codeload.github.com/${github_repo}/tar.gz/${git_commit}" | tar -xz -C "$work" || return 1

  src=$(find "$work" -mindepth 1 -maxdepth 1 -type d | head -n1)/${directory}

  if [[ ! -f "${src}/Dockerfile" ]]; then
    echo "Dockerfile not found in ${github_repo}@${git_commit}:${directory}" >&2
    return 1
  fi

  mkdir -p "$dest"
  cp -a "${src}/." "$dest/"

  cat > "${dest}/.docker-library-source" <<EOF
image=${image}:${tag}
arch=${arch}
git_repo=${git_repo}
git_commit=${git_commit}
directory=${directory}
EOF

  echo "fetched ${image}:${tag} from ${github_repo}@${git_commit}:${directory}"
  echo "  -> ${dest#"${repo_root}/"}"
}

if [[ $# -eq 1 ]]; then
  fetch "$1"
  exit
fi

mapfile -t keys < <(sed -n "s/${entry_pattern}/\1 \3/p" "$bake_file" | awk '$2 == "src" { print $1 }')

if [[ ${#keys[@]} -eq 0 ]]; then
  echo "no BASE_IMAGES entry has source = \"src\""
  exit 0
fi

failed=0

for key in "${keys[@]}"; do
  if [[ -e "${repo_root}/infra/docker/base/${key}" ]]; then
    echo "skip ${key}: infra/docker/base/${key} already exists"
    continue
  fi

  # 1 つ失敗しても残りのキーの取得は続ける
  ( fetch "$key" ) || failed=1
done

exit "$failed"
