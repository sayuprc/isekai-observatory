#!/usr/bin/env bash
# Shared helpers for agent hooks (Codex / Cursor).

hook_input="${hook_input-}"

hook_read_input() {
  if [ -z "${hook_input}" ]; then
    hook_input="$(cat)"
  fi
}

hook_is_cursor() {
  hook_read_input
  jq -e '.cursor_version != null' >/dev/null 2>&1 <<< "$hook_input"
}

hook_file_path() {
  hook_read_input
  jq -r '.file_path // .tool_input.file_path // .tool_input.path // .tool_input.target_notebook // empty' <<< "$hook_input"
}

# Emit lint / format feedback for the active agent host.
hook_emit_context() {
  local msg="$1"
  if hook_is_cursor; then
    jq -n --arg msg "$msg" '{additional_context: $msg}'
  else
    jq -n --arg msg "$msg" '{
      hookSpecificOutput: {
        hookEventName: "PostToolUse",
        additionalContext: $msg
      }
    }'
  fi
}

# Emit a stop-hook follow-up. Cursor consumes followup_message; others use stdout + exit 1.
hook_emit_stop_failure() {
  local msg="$1"
  if hook_is_cursor; then
    jq -n --arg msg "$msg" '{followup_message: $msg}'
    exit 0
  fi

  echo -e "$msg"
  exit 1
}
