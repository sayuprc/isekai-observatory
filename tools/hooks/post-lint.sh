#!/usr/bin/env bash
set -euo pipefail

# Post-edit hook: ファイル編集後に自動リント・フォーマットを実行し、
# 残った違反を additional context としてエージェントにフィードバックする

script_dir="$(cd "$(dirname "$0")" && pwd)"
# shellcheck source=lib.sh
source "$script_dir/lib.sh"

hook_read_input
file="$(hook_file_path)"

[ -z "$file" ] && exit 0

repo_root="$(cd "$script_dir/../.." && pwd)"
hook_state_dir="$repo_root/.git/agent-hooks"
contracts_stop_marker="$hook_state_dir/contracts-stop-verify"
contexts=()

add_context() {
  contexts+=("$1")
}

emit_collected_contexts() {
  if [ "${#contexts[@]}" -eq 0 ]; then
    return 0
  fi

  local msg
  msg="$(printf '%s\n\n' "${contexts[@]}")"
  hook_emit_context "$msg"
}

# 自動生成ファイルはスキップ
case "$file" in
  */Generated/*|*/generated/*|*/vendor/*|*/node_modules/*|*/.astro/*|*/dist/*)
    exit 0
    ;;
esac

case "$file" in
  src/app/contracts/*.tsp|*/src/app/contracts/*.tsp|src/app/contracts/scripts/*.ts|*/src/app/contracts/scripts/*.ts|src/app/contracts/scripts/*.js|*/src/app/contracts/scripts/*.js|src/app/contracts/package.json|*/src/app/contracts/package.json|src/app/pnpm-lock.yaml|*/src/app/pnpm-lock.yaml|src/app/pnpm-workspace.yaml|*/src/app/pnpm-workspace.yaml|src/app/contracts/tspconfig.yaml|*/src/app/contracts/tspconfig.yaml)
    mkdir -p "$hook_state_dir"
    : > "$contracts_stop_marker"
    ;;
esac

case "$file" in
  */src/app/server/*.php)
    cd "$repo_root"

    # Docker コンテナが起動していなければエラーフィードバック
    if ! docker compose exec -T php true 2>/dev/null; then
      add_context "ERROR: PHP コンテナが起動していません。
FIX: mise run up を実行してコンテナを起動してください。"
      emit_collected_contexts
      exit 0
    fi

    # コンテナ内パスに変換
    container_path="${file#*src/app/server/}"

    # 自動修正
    mise run api:ecs:fix -- "$container_path" >/dev/null 2>&1 || true

    # 残った違反をチェック
    diag="$(mise run api:ecs -- "$container_path" 2>&1 | head -30)" || true

    if [ -n "$diag" ] && echo "$diag" | grep -qiE 'error|found'; then
      add_context "ECS violations in ${container_path}:
${diag}"
    fi

    # mago lint(ホスト上で高速実行)
    mago_diag="$(mago lint "$file" 2>&1 | head -30)" || true

    if [ -n "$mago_diag" ] && echo "$mago_diag" | grep -qiE 'warning|error|help'; then
      add_context "mago lint:
${mago_diag}"
    fi
    ;;

  */src/app/admin/*.ts|*/src/app/admin/*.tsx|*/src/app/admin/*.js|*/src/app/admin/*.jsx|*/src/app/admin/*.mjs)
    cd "$repo_root/src/app/admin"

    # Oxlint 自動修正
    bunx oxlint --fix "$file" >/dev/null 2>&1 || true

    # 残った違反をチェック
    diag="$(bunx oxlint "$file" 2>&1 | head -20)" || true

    if [ -n "$diag" ] && echo "$diag" | grep -qiE 'error|warning'; then
      add_context "$diag"
    fi
    ;;

  */src/app/admin/*.astro)
    cd "$repo_root/src/app/admin"

    # ESLint
    bunx eslint --fix "$file" >/dev/null 2>&1 || true
    diag_eslint="$(bunx eslint "$file" 2>&1 | head -10)" || true

    # Stylelint
    bunx stylelint --fix "$file" >/dev/null 2>&1 || true
    diag_style="$(bunx stylelint "$file" 2>&1 | head -10)" || true

    diag=""
    if [ -n "$diag_eslint" ] && echo "$diag_eslint" | grep -qiE 'error|warning'; then
      diag="$diag_eslint"
    fi
    if [ -n "$diag_style" ] && echo "$diag_style" | grep -qiE 'error|warning'; then
      diag="${diag:+$diag
}$diag_style"
    fi

    if [ -n "$diag" ]; then
      add_context "$diag"
    fi
    ;;

  */src/app/admin/*.css)
    cd "$repo_root/src/app/admin"

    # Stylelint 自動修正
    bunx stylelint --fix "$file" >/dev/null 2>&1 || true

    # 残った違反
    diag="$(bunx stylelint "$file" 2>&1 | head -20)" || true

    if [ -n "$diag" ] && echo "$diag" | grep -qiE 'error|warning'; then
      add_context "$diag"
    fi
    ;;

  */src/app/viewer/*.ts|*/src/app/viewer/*.tsx|*/src/app/viewer/*.js|*/src/app/viewer/*.jsx|*/src/app/viewer/*.mjs)
    cd "$repo_root/src/app/viewer"

    # Biome フォーマット + ESLint 自動修正
    bunx biome format --write "$file" >/dev/null 2>&1 || true
    bunx eslint --fix "$file" >/dev/null 2>&1 || true

    # 残った違反をチェック
    diag="$(bunx eslint "$file" 2>&1 | head -20)" || true

    if [ -n "$diag" ] && echo "$diag" | grep -qiE 'error|warning'; then
      add_context "$diag"
    fi
    ;;

  */src/app/viewer/*.astro)
    cd "$repo_root/src/app/viewer"

    # Biome フォーマット
    bunx biome format --write "$file" >/dev/null 2>&1 || true

    # ESLint
    bunx eslint --fix "$file" >/dev/null 2>&1 || true
    diag_eslint="$(bunx eslint "$file" 2>&1 | head -10)" || true

    # Stylelint
    bunx stylelint --fix "$file" >/dev/null 2>&1 || true
    diag_style="$(bunx stylelint "$file" 2>&1 | head -10)" || true

    diag=""
    if [ -n "$diag_eslint" ] && echo "$diag_eslint" | grep -qiE 'error|warning'; then
      diag="$diag_eslint"
    fi
    if [ -n "$diag_style" ] && echo "$diag_style" | grep -qiE 'error|warning'; then
      diag="${diag:+$diag
}$diag_style"
    fi

    if [ -n "$diag" ]; then
      add_context "$diag"
    fi
    ;;

  */src/app/viewer/*.css)
    cd "$repo_root/src/app/viewer"

    # Biome フォーマット + Stylelint 自動修正
    bunx biome format --write "$file" >/dev/null 2>&1 || true
    bunx stylelint --fix "$file" >/dev/null 2>&1 || true

    # 残った違反
    diag="$(bunx stylelint "$file" 2>&1 | head -20)" || true

    if [ -n "$diag" ] && echo "$diag" | grep -qiE 'error|warning'; then
      add_context "$diag"
    fi
    ;;

  src/app/contracts/*.tsp|*/src/app/contracts/*.tsp)
    cd "$repo_root"

    mise run contract:format >/dev/null 2>&1 || true

    if ! diag="$(mise run contract:format:check 2>&1)"; then
      diag="$(printf '%s\n' "$diag" | head -20)"
      add_context "TypeSpec format violations:
${diag}"
    fi
    ;;
esac

emit_collected_contexts
