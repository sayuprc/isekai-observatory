#!/usr/bin/env bash
# 定期レポートを、タイトルで特定した open issue へコメントとして追記する
# issue がなければ作成する
# 使い方: post-issue-report.sh <issue タイトル> <本文ファイル>
set -euo pipefail

title="$1"
body_file="$2"

# 検索はあいまい一致なので、タイトル完全一致で絞り込む
number=$(gh issue list --repo "${GITHUB_REPOSITORY}" --state open --search "in:title ${title}" \
  --json number,title --jq "map(select(.title == \"${title}\")) | .[0].number // empty")

if [ -z "$number" ]; then
  url=$(gh issue create --repo "${GITHUB_REPOSITORY}" --title "${title}" \
    --body "このワークフローが定期的に結果をコメントで追記します: .github/workflows/server-quality-report.yaml")
  number="${url##*/}"
fi

gh issue comment "$number" --repo "${GITHUB_REPOSITORY}" --body-file "$body_file"
