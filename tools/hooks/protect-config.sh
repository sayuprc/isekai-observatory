#!/usr/bin/env bash
set -euo pipefail

# PreToolUse hook: リンター・フォーマッター設定ファイルへの編集をブロックする
# エージェントがリンターエラーを設定変更で回避することを防止する

script_dir="$(cd "$(dirname "$0")" && pwd)"
# shellcheck source=lib.sh
source "$script_dir/lib.sh"

hook_read_input
file="$(hook_file_path)"

[ -z "$file" ] && exit 0

basename_file="$(basename "$file")"

PROTECTED_FILES=(
  "mago.toml"
  "phpstan.neon"
  "phparkitect.php"
  "eslint.config.mjs"
  "stylelint.config.mjs"
  "biome.json"
  ".editorconfig"
  "lefthook.yml"
  "tsconfig.json"
  "infection.json5"
  "phpunit.xml"
  "baseline.php"
)

for p in "${PROTECTED_FILES[@]}"; do
  if [ "$basename_file" = "$p" ]; then
    msg="BLOCKED: $file is a protected config file. Fix the code, not the linter/formatter config."
    if hook_is_cursor; then
      jq -n --arg msg "$msg" '{
        permission: "deny",
        user_message: $msg,
        agent_message: $msg
      }'
      exit 0
    fi

    echo "$msg" >&2
    exit 2
  fi
done
