#!/usr/bin/env bash
# Fetch the build context of a Docker Official Image tag into infra/docker/base/.
# The source is resolved from docker-library/official-images, so the result matches what Docker Hub built.
# Usage: tools/fetch-docker-library-image.sh <image>:<tag> [arch]
#   arch: official-images architecture name (default: amd64)
#   OFFICIAL_IMAGES_REF: official-images commit or branch to read (default: master)
set -euo pipefail

if [[ $# -lt 1 || $# -gt 2 ]]; then
  echo "usage: $0 <image>:<tag> [arch]" >&2
  exit 2
fi

ref=$1
arch=${2:-amd64}
official_images_ref=${OFFICIAL_IMAGES_REF:-master}

if [[ ! "$ref" =~ ^([a-z0-9._-]+):([A-Za-z0-9._-]+)$ ]]; then
  echo "invalid image reference (expected <library image>:<tag>): $ref" >&2
  exit 1
fi

image=${BASH_REMATCH[1]}
tag=${BASH_REMATCH[2]}
repo_root=$(cd "$(dirname "$0")/.." && pwd)
dest="${repo_root}/infra/docker/base/${image}-${tag}"

if [[ -e "$dest" ]]; then
  echo "already exists: $dest" >&2
  exit 1
fi

library=$(curl -fsSL "https://raw.githubusercontent.com/docker-library/official-images/${official_images_ref}/library/${image}")

# Each entry is a blank-line separated paragraph after the global one
# Field lookup order: entry arch-prefixed, entry, global arch-prefixed, global
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
  exit 1
fi

if [[ ! "${git_repo%.git}" =~ ^https://github\.com/([^/]+/[^/]+)$ ]]; then
  echo "unsupported GitRepo (only github.com is supported): $git_repo" >&2
  exit 1
fi

github_repo=${BASH_REMATCH[1]}

# oci-import などは Dockerfile が無く、書き換えてビルドする対象にならない
if [[ "$builder" != "buildkit" && "$builder" != "classic" ]]; then
  echo "unsupported Builder: ${builder} (${image}:${tag} is not built from a Dockerfile)" >&2
  exit 1
fi

directory=${directory%/}
work=$(mktemp -d)
trap 'rm -rf "$work"' EXIT

curl -fsSL "https://codeload.github.com/${github_repo}/tar.gz/${git_commit}" | tar -xz -C "$work"

src=$(find "$work" -mindepth 1 -maxdepth 1 -type d | head -n1)/${directory}

if [[ ! -f "${src}/Dockerfile" ]]; then
  echo "Dockerfile not found in ${github_repo}@${git_commit}:${directory}" >&2
  exit 1
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
