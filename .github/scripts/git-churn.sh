#!/usr/bin/env bash
# 直近の期間に各 PHP ファイルを変更したコミット数を、{"<サーバのディレクトリからの相対パス>": <回数>} の JSON で出力する
# main へは squash マージなので、コミット数はおおむねそのファイルを変更した PR の数になる
# 使い方: git-churn.sh <サーバのディレクトリ (リポジトリルートからの相対パス)> <日数>
set -euo pipefail

server_dir="${1%/}"
days="$2"

if [ "$(git rev-parse --is-shallow-repository)" = "true" ]; then
  echo "shallow clone では変更回数を正しく数えられません (actions/checkout の fetch-depth: 0 が必要です)" >&2
  exit 1
fi

git log --since="${days} days ago" --no-merges --format= --name-only -- "${server_dir}" \
  | { grep -F '.php' || true; } | sort | uniq -c \
  | jq -R -s --arg prefix "${server_dir}/" '
      split("\n") | map(select(length > 0) | capture("^\\s*(?<count>\\d+)\\s+(?<path>.+)$"))
      | map({key: (.path | ltrimstr($prefix)), value: (.count | tonumber)}) | from_entries'
