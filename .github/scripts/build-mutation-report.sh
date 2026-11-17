#!/usr/bin/env bash
# infection のテキストログと git の変更履歴を掛け合わせ、テストを足す候補を Markdown で出力する
# 変更が多いのにテストで守られていないファイルほどバグを見逃しやすいので、
# スコア = 変更回数 × (すり抜けたミュータント数 + 未カバーのミュータント数) で並べる
# JSON ログは全ミュータントに元ファイルの全文を含み巨大になるため、既存のテキストログを解析する
# 使い方: build-mutation-report.sh <infection のテキストログ> <サーバのディレクトリ (リポジトリルートからの相対パス)>
# 環境変数:
#   HOTSPOT_DAYS  変更回数を数える期間 (日)。既定 90
#   HOTSPOT_TOP   表に載せる件数。既定 15
#   GITHUB_SERVER_URL / GITHUB_REPOSITORY / GITHUB_SHA  ファイルへのリンクに使う
set -euo pipefail

infection_log="$1"
server_dir="${2%/}"
days="${HOTSPOT_DAYS:-90}"
top="${HOTSPOT_TOP:-15}"
script_dir="$(cd "$(dirname "$0")" && pwd)"

churn=$("${script_dir}/git-churn.sh" "${server_dir}" "${days}")

# ログは「<区分> mutants:」の見出しの下に「1) <パス>:<行>    [M] <ミューテータ> [ID] <ハッシュ>」が並ぶ
# diff の行は必ず + - 空白 @ のいずれかで始まるので、行頭が数字の行だけを拾えば混ざらない
awk '
  /^[A-Za-z ]+ mutants:$/ { section = $0; sub(/ mutants:$/, "", section); next }
  /^[0-9]+\) .*:[0-9]+    \[M\] / {
    location = $2
    line = location; sub(/^.*:/, "", line)
    path = location; sub(/:[0-9]+$/, "", path)
    printf "%s\t%s\t%s\t%s\n", section, path, line, $4
  }
' "${infection_log}" \
  | jq -R -r \
      --argjson churn "${churn}" \
      --arg days "${days}" \
      --argjson top "${top}" \
      --arg server_dir "${server_dir}" \
      --arg blob "${GITHUB_SERVER_URL:-https://github.com}/${GITHUB_REPOSITORY:-}/blob/${GITHUB_SHA:-HEAD}" \
      -n -f "${script_dir}/mutation-report.jq"
