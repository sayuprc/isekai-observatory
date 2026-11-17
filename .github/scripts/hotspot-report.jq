# build-hotspot-report.sh から呼ばれる。入力は phpmetrics の --report-json 出力

# composer.json の PSR-4 定義でクラス名をファイルパスへ変換する (phpmetrics の JSON にはパスが含まれない)
def class_path($psr4):
  . as $class
  | [$psr4[] | select(.key as $ns | $class | startswith($ns))]
  | sort_by(.key | length) | last
  | if . == null then null
    else .key as $ns | .value + ($class | ltrimstr($ns) | gsub("\\\\"; "/")) + ".php"
    end;

# しきい値は一般的な目安。保守性指数は上位のクラスがほぼすべて該当して区別にならないので使わない
def concerns:
  [
    (select(.ccnMethodMax >= 10) | "複雑なメソッドがある (最大 CCN \(.ccnMethodMax))"),
    (select(.lcom >= 3) | "責務が複数ありそう (LCOM \(.lcom))"),
    (select(.efferentCoupling >= 15) | "依存先が多い (\(.efferentCoupling))"),
    (select(.lloc >= 200) | "大きい (\(.lloc) LLOC)")
  ] | if length == 0 then "-" else join("<br>") end;

($composer[0].autoload["psr-4"] | to_entries) as $psr4
| [
    .[]
    | select(._type == "Hal\\Metric\\ClassMetric")
    | (.name | class_path($psr4)) as $path
    | select($path != null)
    | . + {path: $path, churn: ($churn[$path] // 0)}
    | . + {score: (.churn * .wmc)}
  ] as $classes
| ($classes | map(select(.score > 0)) | sort_by([-.score, -.wmc, .name])) as $ranked
| "### 次に手を入れる候補 (ホットスポット)",
  "",
  "直近 \($days) 日の変更回数と複雑度 (WMC) を掛けたスコアの上位 \($top) 件です。"
  + "変更が多く複雑なクラスほど、分割や単純化の効果が大きくなります",
  "",
  (if ($ranked | length) == 0 then "対象期間に変更されたクラスはありません"
   else
     "| # | クラス | 変更回数 | WMC | スコア | 気になる点 |",
     "| ---: | --- | ---: | ---: | ---: | --- |",
     ($ranked[:$top] | to_entries[]
      | .value as $c
      | "| \(.key + 1) | [`\($c.name | split("\\") | last)`](\($blob)/\($server_dir)/\($c.path))<br>"
        + "<sub>\($c.path | split("/") | .[:-1] | join("/")) ・ [履歴](\($commits)/\($server_dir)/\($c.path))</sub>"
        + " | \($c.churn) | \($c.wmc) | \($c.score) | \($c | concerns) |")
   end),
  "",
  "<details><summary>指標の見方</summary>",
  "",
  "- 変更回数: 直近 \($days) 日にそのファイルを変更したコミット数 (squash マージなのでおおむね PR 数)",
  "- WMC: クラス内の全メソッドの循環的複雑度の合計",
  "- スコア: 変更回数 × WMC",
  "- 対象期間に変更されたクラス: \($ranked | length) / \($classes | length)",
  "",
  "</details>"
