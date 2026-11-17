# build-mutation-report.sh から呼ばれる。入力は「区分 <TAB> パス <TAB> 行 <TAB> ミューテータ」の行

# ログのパスはコンテナ内の絶対パスなので、ソースディレクトリ (app / packages) 以降を取り出す
def relative_path: capture("^.*?/(?<path>(app|packages)/.+)$").path;

def line_links($url; $max):
  (sort_by(.line) | unique_by([.line, .mutator])) as $mutants
  | ($mutants[:$max] | map("[L\(.line)](\($url)#L\(.line)) \(.mutator)") | join("<br>"))
    + (if ($mutants | length) > $max then "<br>ほか \(($mutants | length) - $max) 件" else "" end);

[
  inputs | split("\t")
  | {section: .[0], path: (.[1] | relative_path), line: (.[2] | tonumber), mutator: .[3]}
  | select(.section == "Escaped" or .section == "Not Covered")
] as $mutants
| [
    $mutants | group_by(.path)[]
    | {
        path: .[0].path,
        escaped: map(select(.section == "Escaped")),
        uncovered: map(select(.section == "Not Covered")),
        churn: ($churn[.[0].path] // 0)
      }
    | . + {score: (.churn * ((.escaped | length) + (.uncovered | length)))}
  ] as $files
| ($files | map(select(.score > 0)) | sort_by([-.score, .path])) as $ranked
| "### テストを足す候補",
  "",
  "直近 \($days) 日の変更回数と、テストで検出できなかったミュータント数を掛けたスコアの上位 \($top) 件です。"
  + "変更が多いのにテストで守られていないファイルほど、バグを見逃しやすくなります",
  "",
  (if ($ranked | length) == 0 then "対象期間に変更されたファイルに、検出できなかったミュータントはありません"
   else
     "| # | ファイル | 変更回数 | すり抜け | 未カバー | スコア | すり抜けた箇所 |",
     "| ---: | --- | ---: | ---: | ---: | ---: | --- |",
     ($ranked[:$top] | to_entries[]
      | .value as $f
      | "\($blob)/\($server_dir)/\($f.path)" as $url
      | "| \(.key + 1) | [`\($f.path | split("/") | last | rtrimstr(".php"))`](\($url))<br>"
        + "<sub>\($f.path | split("/") | .[:-1] | join("/"))</sub>"
        + " | \($f.churn) | \($f.escaped | length) | \($f.uncovered | length) | \($f.score)"
        + " | \(if ($f.escaped | length) == 0 then "-" else ($f.escaped | line_links($url; 5)) end) |")
   end),
  "",
  "<details><summary>指標の見方</summary>",
  "",
  "- ミュータント: コードを 1 か所だけわざと壊したもの (`>` を `>=` にする、`return` の値を変えるなど)",
  "- すり抜け: 壊したコードでもテストが全部通ったもの。その行は実行されているが、結果を検証するテストがない",
  "- 未カバー: 壊した行を実行するテストがそもそもないもの",
  "- 変更回数: 直近 \($days) 日にそのファイルを変更したコミット数 (squash マージなのでおおむね PR 数)",
  "- スコア: 変更回数 × (すり抜け + 未カバー)",
  "- すり抜けた箇所: 行とミューテータ名。どう壊したかの diff は Artifacts の `infection.html` で見られる",
  "",
  "</details>"
