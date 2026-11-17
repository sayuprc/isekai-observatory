#!/usr/bin/env bash
# phpmetrics のクラス別メトリクスと git の変更履歴を掛け合わせ、次に手を入れる候補 (ホットスポット) を Markdown で出力する
# 変更が多く、かつ複雑なクラスほど改善の効果が大きいので、スコア = 変更回数 × WMC で並べる
# 使い方: build-hotspot-report.sh <phpmetrics の --report-json 出力> <サーバのディレクトリ (リポジトリルートからの相対パス)>
# 環境変数:
#   HOTSPOT_DAYS  変更回数を数える期間 (日)。既定 90
#   HOTSPOT_TOP   表に載せる件数。既定 15
#   GITHUB_SERVER_URL / GITHUB_REPOSITORY / GITHUB_SHA  ファイルへのリンクに使う
set -euo pipefail

metrics_json="$1"
server_dir="${2%/}"
days="${HOTSPOT_DAYS:-90}"
top="${HOTSPOT_TOP:-15}"
script_dir="$(cd "$(dirname "$0")" && pwd)"

if [ "$(git rev-parse --is-shallow-repository)" = "true" ]; then
  echo "shallow clone では変更回数を正しく数えられません (actions/checkout の fetch-depth: 0 が必要です)" >&2
  exit 1
fi

# main へは squash マージなので、コミット数はおおむねそのファイルを変更した PR の数になる
churn=$(git log --since="${days} days ago" --no-merges --format= --name-only -- "${server_dir}" \
  | { grep -F '.php' || true; } | sort | uniq -c \
  | jq -R -s --arg prefix "${server_dir}/" '
      split("\n") | map(select(length > 0) | capture("^\\s*(?<count>\\d+)\\s+(?<path>.+)$"))
      | map({key: (.path | ltrimstr($prefix)), value: (.count | tonumber)}) | from_entries')

jq -r \
  --argjson churn "${churn}" \
  --slurpfile composer "${server_dir}/composer.json" \
  --arg days "${days}" \
  --argjson top "${top}" \
  --arg server_dir "${server_dir}" \
  --arg blob "${GITHUB_SERVER_URL:-https://github.com}/${GITHUB_REPOSITORY:-}/blob/${GITHUB_SHA:-HEAD}" \
  --arg commits "${GITHUB_SERVER_URL:-https://github.com}/${GITHUB_REPOSITORY:-}/commits/${GITHUB_SHA:-HEAD}" \
  -f "${script_dir}/hotspot-report.jq" "${metrics_json}"
