#!/usr/bin/env bash

set -euo pipefail

source "$(dirname "$0")/common.sh"

repo_root="$(worktree_repo_root)"
state_dir="$(worktree_state_dir)"
shared_state_dir="$(worktree_shared_state_dir)"
shared_env_file="$(worktree_shared_env_file)"
mise_local_toml_file="$(worktree_mise_local_toml_file)"
template_file="${repo_root}/infra/local/docker/nginx/site.conf.template"

mkdir -p "$state_dir/docker/nginx" "$shared_state_dir"

lock_dir="${shared_state_dir}/lock"
for _ in $(seq 1 200); do
  if mkdir "$lock_dir" 2>/dev/null; then
    break
  fi
  sleep 0.1
done

if [[ ! -d "$lock_dir" ]]; then
  echo "worktree init lock could not be acquired" >&2
  exit 1
fi

cleanup() {
  rmdir "$lock_dir"
}
trap cleanup EXIT

declare -A reserved_ports=()

load_reserved_ports() {
  local env_path

  shopt -s nullglob
  for env_path in "${shared_state_dir}"/*.env; do
    unset WORKTREE_ROOT
    unset WORKTREE_PROXY_HTTP_PORT WORKTREE_PROXY_HTTPS_PORT WORKTREE_ADMIN_PORT
    unset WORKTREE_VIEWER_PORT WORKTREE_MYSQL_PORT WORKTREE_REDIS_PORT WORKTREE_REDIS_HTTP_PORT

    # shellcheck disable=SC1090
    source "$env_path"

    if [[ -z "${WORKTREE_ROOT:-}" || ! -d "${WORKTREE_ROOT}" ]]; then
      rm -f "$env_path"
      continue
    fi

    if [[ "$env_path" == "$shared_env_file" && "$WORKTREE_ROOT" == "$repo_root" ]]; then
      continue
    fi

    reserved_ports["${WORKTREE_PROXY_HTTP_PORT}"]=1
    reserved_ports["${WORKTREE_PROXY_HTTPS_PORT}"]=1
    reserved_ports["${WORKTREE_ADMIN_PORT}"]=1
    reserved_ports["${WORKTREE_VIEWER_PORT}"]=1
    reserved_ports["${WORKTREE_MYSQL_PORT}"]=1
    reserved_ports["${WORKTREE_REDIS_PORT}"]=1
    reserved_ports["${WORKTREE_REDIS_HTTP_PORT}"]=1
  done
  shopt -u nullglob
}

find_free_port() {
  local start="$1"
  local end="$2"
  local preferred="$3"
  local span
  local offset
  local candidate

  span=$((end - start + 1))

  for ((offset = 0; offset < span; offset++)); do
    candidate=$((start + ((preferred - start + offset) % span)))
    if [[ -n "${reserved_ports[$candidate]:-}" ]]; then
      continue
    fi
    if port_is_listening "$candidate"; then
      continue
    fi
    reserved_ports["$candidate"]=1
    printf '%d\n' "$candidate"
    return 0
  done

  echo "no free port in range ${start}-${end}" >&2
  exit 1
}

set_env_value() {
  local file="$1"
  local key="$2"
  local value="$3"
  local tmp_file

  tmp_file="$(mktemp)"

  awk -v key="$key" -v value="$value" '
    BEGIN {
      done = 0
    }
    index($0, key "=") == 1 {
      print key "=" value
      done = 1
      next
    }
    {
      print
    }
    END {
      if (!done) {
        print key "=" value
      }
    }
  ' "$file" >"$tmp_file"

  mv "$tmp_file" "$file"
}

set_mise_local_env_block() {
  local file="$1"
  local tmp_file
  local stripped_file
  local block_file

  tmp_file="$(mktemp)"
  stripped_file="$(mktemp)"
  block_file="$(mktemp)"

  if [[ -f "$file" ]]; then
    cp "$file" "$tmp_file"
  else
    printf '[env]\n' >"$tmp_file"
  fi

  awk '
    /^# >>> worktree:init >>>$/ {
      skip = 1
      next
    }
    /^# <<< worktree:init <<<$/{
      skip = 0
      next
    }
    skip != 1 {
      print
    }
  ' "$tmp_file" >"$stripped_file"

  {
    printf '# >>> worktree:init >>>\n'
    write_toml_string "COMPOSE_PROJECT_NAME" "$COMPOSE_PROJECT_NAME"
    write_toml_string "WORKTREE_PROXY_HTTP_PORT" "$WORKTREE_PROXY_HTTP_PORT"
    write_toml_string "WORKTREE_PROXY_HTTPS_PORT" "$WORKTREE_PROXY_HTTPS_PORT"
    write_toml_string "WORKTREE_ADMIN_PORT" "$WORKTREE_ADMIN_PORT"
    write_toml_string "WORKTREE_VIEWER_PORT" "$WORKTREE_VIEWER_PORT"
    write_toml_string "WORKTREE_MYSQL_PORT" "$WORKTREE_MYSQL_PORT"
    write_toml_string "WORKTREE_REDIS_PORT" "$WORKTREE_REDIS_PORT"
    write_toml_string "WORKTREE_REDIS_HTTP_PORT" "$WORKTREE_REDIS_HTTP_PORT"
    write_toml_string "WORKTREE_NGINX_SITE_CONF" "$WORKTREE_NGINX_SITE_CONF"
    printf '# <<< worktree:init <<<\n'
  } >"$block_file"

  if grep -q '^\[env\]$' "$stripped_file"; then
    awk -v block_file="$block_file" '
      BEGIN {
        inserted = 0
        while ((getline line < block_file) > 0) {
          block = block line "\n"
        }
        close(block_file)
      }
      {
        print
        if (!inserted && $0 == "[env]") {
          printf "%s", block
          inserted = 1
        }
      }
      END {
        if (!inserted) {
          print "[env]"
          printf "%s", block
        }
      }
    ' "$stripped_file" >"$tmp_file"
  else
    cat "$stripped_file" >"$tmp_file"
    if [[ -s "$tmp_file" ]]; then
      printf '\n' >>"$tmp_file"
    fi
    printf '[env]\n' >>"$tmp_file"
    cat "$block_file" >>"$tmp_file"
  fi

  mv "$tmp_file" "$file"
  rm -f "$stripped_file" "$block_file"
}

ensure_env_file() {
  local file="$1"
  local example_file="$2"

  if [[ ! -f "$file" ]]; then
    cp "$example_file" "$file"
  fi
}

sync_shared_file() {
  local source_file="$1"
  local shared_file="$2"
  local target_file="$3"

  mkdir -p "$(dirname "$shared_file")" "$(dirname "$target_file")"

  if [[ -f "$source_file" ]]; then
    cp "$source_file" "$shared_file"
  fi

  if [[ -f "$shared_file" && ! -f "$target_file" ]]; then
    cp "$shared_file" "$target_file"
  fi
}

bootstrap_shared_file_from_worktrees() {
  local relative_path="$1"
  local shared_file="$2"
  local candidate_root
  local candidate_file

  if [[ -f "$shared_file" ]]; then
    return 0
  fi

  git -C "$repo_root" worktree list --porcelain | while IFS= read -r line; do
    case "$line" in
      worktree\ *)
        candidate_root="${line#worktree }"

        if [[ "$candidate_root" == "$repo_root" ]]; then
          continue
        fi

        candidate_file="${candidate_root}/${relative_path}"
        if [[ -f "$candidate_file" ]]; then
          mkdir -p "$(dirname "$shared_file")"
          cp "$candidate_file" "$shared_file"
          break
        fi
        ;;
    esac
  done
}

ensure_worktree_certs() {
  local shared_cert_dir

  shared_cert_dir="${shared_state_dir}/certs"

  bootstrap_shared_file_from_worktrees \
    "infra/local/docker/nginx/certs/server.crt" \
    "${shared_cert_dir}/infra/local/docker/nginx/certs/server.crt"

  bootstrap_shared_file_from_worktrees \
    "infra/local/docker/nginx/certs/server.key" \
    "${shared_cert_dir}/infra/local/docker/nginx/certs/server.key"

  bootstrap_shared_file_from_worktrees \
    "infra/local/docker/php/certs/rootCA.pem" \
    "${shared_cert_dir}/infra/local/docker/php/certs/rootCA.pem"

  sync_shared_file \
    "${repo_root}/infra/local/docker/nginx/certs/server.crt" \
    "${shared_cert_dir}/infra/local/docker/nginx/certs/server.crt" \
    "${repo_root}/infra/local/docker/nginx/certs/server.crt"

  sync_shared_file \
    "${repo_root}/infra/local/docker/nginx/certs/server.key" \
    "${shared_cert_dir}/infra/local/docker/nginx/certs/server.key" \
    "${repo_root}/infra/local/docker/nginx/certs/server.key"

  sync_shared_file \
    "${repo_root}/infra/local/docker/php/certs/rootCA.pem" \
    "${shared_cert_dir}/infra/local/docker/php/certs/rootCA.pem" \
    "${repo_root}/infra/local/docker/php/certs/rootCA.pem"

  if [[ ! -f "${repo_root}/infra/local/docker/nginx/certs/server.crt" || ! -f "${repo_root}/infra/local/docker/nginx/certs/server.key" ]]; then
    echo "nginx certificates are missing; run 'mise run cert:make' once in any worktree" >&2
    exit 1
  fi

  if [[ ! -f "${repo_root}/infra/local/docker/php/certs/rootCA.pem" ]]; then
    echo "rootCA.pem is missing; run 'mise run cert:copy-pem' once in any worktree" >&2
    exit 1
  fi
}

load_reserved_ports

if [[ -f "$shared_env_file" ]]; then
  unset WORKTREE_ROOT
  # shellcheck disable=SC1090
  source "$shared_env_file"
fi

if [[ "${WORKTREE_ROOT:-}" != "$repo_root" ]]; then
  WORKTREE_ROOT="$repo_root"
  WORKTREE_KEY="$(worktree_hash)"
  WORKTREE_SLUG="$(worktree_slug)"
  COMPOSE_PROJECT_NAME="$(compose_project_name)"
  WORKTREE_PROXY_HTTP_PORT="$(find_free_port 10080 10979 "$(worktree_prefers_port 10080 10979)")"
  WORKTREE_PROXY_HTTPS_PORT="$(find_free_port 10443 11342 "$(worktree_prefers_port 10443 11342)")"
  WORKTREE_ADMIN_PORT="$(find_free_port 14321 15220 "$(worktree_prefers_port 14321 15220)")"
  WORKTREE_VIEWER_PORT="$(find_free_port 13000 13899 "$(worktree_prefers_port 13000 13899)")"
  WORKTREE_MYSQL_PORT="$(find_free_port 23306 24205 "$(worktree_prefers_port 23306 24205)")"
  WORKTREE_REDIS_PORT="$(find_free_port 16379 17278 "$(worktree_prefers_port 16379 17278)")"
  WORKTREE_REDIS_HTTP_PORT="$(find_free_port 18079 18978 "$(worktree_prefers_port 18079 18978)")"
fi

WORKTREE_NGINX_SITE_CONF="${repo_root}/.worktree/docker/nginx/site.conf"
WORKTREE_ADMIN_URL="https://local.admin.isekaijoucho.fan:${WORKTREE_PROXY_HTTPS_PORT}"
WORKTREE_VIEWER_URL="https://local.isekaijoucho.fan:${WORKTREE_PROXY_HTTPS_PORT}"
WORKTREE_API_BASE_URL="https://local.api.isekaijoucho.fan:${WORKTREE_PROXY_HTTPS_PORT}"

{
  write_shell_value "WORKTREE_ROOT" "$WORKTREE_ROOT"
  write_shell_value "WORKTREE_KEY" "$WORKTREE_KEY"
  write_shell_value "WORKTREE_SLUG" "$WORKTREE_SLUG"
  write_shell_value "COMPOSE_PROJECT_NAME" "$COMPOSE_PROJECT_NAME"
  write_shell_value "WORKTREE_PROXY_HTTP_PORT" "$WORKTREE_PROXY_HTTP_PORT"
  write_shell_value "WORKTREE_PROXY_HTTPS_PORT" "$WORKTREE_PROXY_HTTPS_PORT"
  write_shell_value "WORKTREE_ADMIN_PORT" "$WORKTREE_ADMIN_PORT"
  write_shell_value "WORKTREE_VIEWER_PORT" "$WORKTREE_VIEWER_PORT"
  write_shell_value "WORKTREE_MYSQL_PORT" "$WORKTREE_MYSQL_PORT"
  write_shell_value "WORKTREE_REDIS_PORT" "$WORKTREE_REDIS_PORT"
  write_shell_value "WORKTREE_REDIS_HTTP_PORT" "$WORKTREE_REDIS_HTTP_PORT"
  write_shell_value "WORKTREE_NGINX_SITE_CONF" "$WORKTREE_NGINX_SITE_CONF"
  write_shell_value "WORKTREE_ADMIN_URL" "$WORKTREE_ADMIN_URL"
  write_shell_value "WORKTREE_VIEWER_URL" "$WORKTREE_VIEWER_URL"
  write_shell_value "WORKTREE_API_BASE_URL" "$WORKTREE_API_BASE_URL"
} >"$shared_env_file"

if [[ -d "$WORKTREE_NGINX_SITE_CONF" ]]; then
  rm -rf "$WORKTREE_NGINX_SITE_CONF"
fi

sed \
  -e "s/__ADMIN_PORT__/${WORKTREE_ADMIN_PORT}/g" \
  -e "s/__VIEWER_PORT__/${WORKTREE_VIEWER_PORT}/g" \
  "$template_file" >"$WORKTREE_NGINX_SITE_CONF"

set_mise_local_env_block "$mise_local_toml_file"
rm -f "${state_dir}/env"
ensure_worktree_certs

ensure_env_file "${repo_root}/src/app/admin/.env" "${repo_root}/src/app/admin/.env.example"
set_env_value "${repo_root}/src/app/admin/.env" "API_URL" "$WORKTREE_API_BASE_URL"
set_env_value "${repo_root}/src/app/admin/.env" "CACHE_URL" "http://127.0.0.1:${WORKTREE_REDIS_HTTP_PORT}"
set_env_value "${repo_root}/src/app/admin/.env" "CACHE_TOKEN" "${REDIS_HTTP_TOKEN:-example_token}"
set_env_value "${repo_root}/src/app/admin/.env" "PUBLIC_APP_URL" "$WORKTREE_ADMIN_URL"

ensure_env_file "${repo_root}/src/app/viewer/.env" "${repo_root}/src/app/viewer/.env.example"
set_env_value "${repo_root}/src/app/viewer/.env" "API_URL" "$WORKTREE_API_BASE_URL"

ensure_env_file "${repo_root}/src/app/server/.env" "${repo_root}/src/app/server/.env.example"
set_env_value "${repo_root}/src/app/server/.env" "APP_URL" "$WORKTREE_API_BASE_URL"
set_env_value "${repo_root}/src/app/server/.env" "DB_PORT" "3306"
set_env_value "${repo_root}/src/app/server/.env" "ATLAS_DB_PORT" "$WORKTREE_MYSQL_PORT"

ensure_env_file "${repo_root}/src/app/server/.env.testing" "${repo_root}/src/app/server/.env.testing.example"
set_env_value "${repo_root}/src/app/server/.env.testing" "APP_URL" "$WORKTREE_API_BASE_URL"
set_env_value "${repo_root}/src/app/server/.env.testing" "DB_PORT" "3306"
set_env_value "${repo_root}/src/app/server/.env.testing" "ATLAS_DB_PORT" "$WORKTREE_MYSQL_PORT"

if [[ "${1:-}" == "--status" ]]; then
  cat <<EOF
worktree: ${WORKTREE_ROOT}
compose project: ${COMPOSE_PROJECT_NAME}
proxy https: ${WORKTREE_PROXY_HTTPS_PORT}
admin dev: ${WORKTREE_ADMIN_PORT}
viewer dev: ${WORKTREE_VIEWER_PORT}
mysql: ${WORKTREE_MYSQL_PORT}
redis: ${WORKTREE_REDIS_PORT}
redis-http: ${WORKTREE_REDIS_HTTP_PORT}
admin url: ${WORKTREE_ADMIN_URL}
viewer url: ${WORKTREE_VIEWER_URL}
api url: ${WORKTREE_API_BASE_URL}
EOF
fi
