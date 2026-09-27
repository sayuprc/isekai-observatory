#!/usr/bin/env bash
# Claude Code on the web (クラウドセッション) 専用の SessionStart hook
# mise.toml で固定した bun / pnpm を用意し、フロントエンドの依存関係を入れる
# クラウドのネットワーク制限で mise 本体・Docker 経由の PHP 環境は用意できないため、
# ここではフロントエンドの確認に必要なものだけを扱う (docs/operations/cloud-session-verification.md)
set -euo pipefail

if [ "${CLAUDE_CODE_REMOTE:-}" != "true" ]; then
  exit 0
fi

repo_root="${CLAUDE_PROJECT_DIR:-$(cd "$(dirname "$0")/../.." && pwd)}"
cd "$repo_root"

bun_version="$(tools/read-mise-value.sh bun)"
pnpm_version="$(tools/read-mise-value.sh pnpm)"
tools_dir="${HOME}/.local/share/isekai-observatory-cloud"
bun_dir="${tools_dir}/bun-${bun_version}"
pnpm_dir="${tools_dir}/pnpm-${pnpm_version}"

# mise の取得元 (api.github.com / mise.run) はクラウドから届かないため、GitHub Releases と npm から直接入れる
if [ ! -x "${bun_dir}/bin/bun" ]; then
  echo "bun ${bun_version} をインストールします" >&2
  tmp_dir="$(mktemp -d)"
  curl -fsSL -o "${tmp_dir}/bun.zip" \
    "https://github.com/oven-sh/bun/releases/download/bun-v${bun_version}/bun-linux-x64.zip"
  unzip -q "${tmp_dir}/bun.zip" -d "$tmp_dir"
  mkdir -p "${bun_dir}/bin"
  mv "${tmp_dir}/bun-linux-x64/bun" "${bun_dir}/bin/bun"
  ln -sf bun "${bun_dir}/bin/bunx"
  rm -rf "$tmp_dir"
fi

if [ ! -x "${pnpm_dir}/bin/pnpm" ]; then
  echo "pnpm ${pnpm_version} をインストールします" >&2
  npm install --global --silent --prefix "$pnpm_dir" "pnpm@${pnpm_version}" >&2
fi

export PATH="${bun_dir}/bin:${pnpm_dir}/bin:${PATH}"

if [ -n "${CLAUDE_ENV_FILE:-}" ]; then
  echo "export PATH=\"${bun_dir}/bin:${pnpm_dir}/bin:\$PATH\"" >> "$CLAUDE_ENV_FILE"
fi

(cd src/app && pnpm install --frozen-lockfile >&2)
