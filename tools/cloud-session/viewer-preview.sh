#!/usr/bin/env bash
# クラウドセッションで Viewer を API モック付きで起動する
# PHP API の代わりに、契約 (OAS) から Prism がモックレスポンスを返す
# Usage: tools/cloud-session/viewer-preview.sh start|stop|status
set -euo pipefail

repo_root="$(cd "$(dirname "$0")/../.." && pwd)"
script_dir="${repo_root}/tools/cloud-session"
log_dir="${TMPDIR:-/tmp}/isekai-observatory-cloud"
prism_version="5.16.0"
prism_port=4010
proxy_port=4011
viewer_port=3000
viewer_oas_file="$(sed -n 's/^VIEWER_OAS_FILE = "\(.*\)"$/\1/p' "${repo_root}/mise.toml")"

mkdir -p "$log_dir"

wait_for() {
  local url=$1
  local name=$2

  for _ in $(seq 1 90); do
    if curl -s -o /dev/null "$url"; then
      return 0
    fi
    sleep 1
  done

  echo "${name} が起動しませんでした (ログ: ${log_dir})" >&2
  return 1
}

start() {
  if ! curl -s -o /dev/null "http://127.0.0.1:${prism_port}/"; then
    nohup bunx "@stoplight/prism-cli@${prism_version}" mock \
      "${repo_root}/src/app/contracts/generated/oas/${viewer_oas_file}" \
      -h 127.0.0.1 -p "$prism_port" > "${log_dir}/prism.log" 2>&1 &
    wait_for "http://127.0.0.1:${prism_port}/" "Prism"
  fi

  if ! curl -s -o /dev/null "http://127.0.0.1:${proxy_port}/"; then
    PROXY_PORT="$proxy_port" UPSTREAM="127.0.0.1:${prism_port}" \
      nohup bun "${script_dir}/strip-v1-proxy.ts" > "${log_dir}/proxy.log" 2>&1 &
    wait_for "http://127.0.0.1:${proxy_port}/" "API プロキシ"
  fi

  # コンテンツコレクションの事前取得はモックデータだと起動タイムアウトするため省略する
  (
    cd "${repo_root}/src/app/viewer"
    bunx astro dev stop > /dev/null 2>&1 || true
    SKIP_CONTENT_FETCH=true API_URL="http://127.0.0.1:${proxy_port}" \
      nohup bun run dev -- --port "$viewer_port" > "${log_dir}/viewer.log" 2>&1 &
  )
  wait_for "http://127.0.0.1:${viewer_port}/" "Viewer"

  echo "Viewer: http://127.0.0.1:${viewer_port}/ (API モック: http://127.0.0.1:${prism_port}/)"
}

stop() {
  (cd "${repo_root}/src/app/viewer" && bunx astro dev stop > /dev/null 2>&1) || true
  pkill -f "strip-v1-proxy.ts" || true
  pkill -f "prism-cli@${prism_version} mock" || true
}

status() {
  local name url

  for entry in "Prism|http://127.0.0.1:${prism_port}/" "API プロキシ|http://127.0.0.1:${proxy_port}/" "Viewer|http://127.0.0.1:${viewer_port}/"; do
    name=${entry%%|*}
    url=${entry#*|}
    if curl -s -o /dev/null "$url"; then
      echo "${name}: 起動中 (${url})"
    else
      echo "${name}: 停止"
    fi
  done
}

case "${1:-}" in
  start) start ;;
  stop) stop ;;
  status) status ;;
  *)
    echo "usage: $0 start|stop|status" >&2
    exit 2
    ;;
esac
