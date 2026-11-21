---
id: ADR-0043
status: accepted
superseded_by: null
applies_to: [api]
---

# Action 専用の Query は UseCase の Action の中に置く

## Context

server の Application 層は `<Feature>/Application/<Type>/{UseCase,Query,Assemble}` の構成になっている

Query (QueryService の interface と DTO) の大半は 1 つの Action からしか使われないのに、Action から離れた `<Type>/Query/` に置かれていた

`UseCase` の段をなくして `<Type>/<Action>/` にする案も検討した

しかし `<Type>/` の直下に Action と共有物 (`Query`、`Assemble`) が混ざるうえ、PHPArkitect のコンポーネントは namespace の `UseCase` / `Query` で定義されているため、検査の対象から外れてしまう

## Decision

`UseCase` の段は残し、Action 専用の Query を Action の中に置く

- 1 つの Action だけが使う Query は `<Feature>/Application/<Type>/UseCase/<Action>/Query/` に置く
- 複数の Action で共有する Query は `<Feature>/Application/<Type>/Query/` に置く
- Assemble は今までどおり `<Feature>/Application/<Type>/Assemble/` に置く
- Query の DTO (`*Item`、`*Page`、ページトークン、カーソル) は、それを返す QueryService の interface と同じ場所に置く

PHPArkitect では `UseCase\...\Query\` 配下のクラスが UseCase と Query の両方のコンポーネントにマッチする

UseCase のコンポーネントから Query を除外し、Query のルールだけで検査する (`tools/Arkitect/config.php` の `Define` の第 3 引数)

## Consequences

### Positive

- Action のディレクトリを見れば、ユースケースと、それが使う Query がまとめて分かる
- `<Type>/` の直下は `UseCase` / `Query` / `Assemble` だけになり、`<Type>/Query/` にあるものは共有物だと分かる

### Negative

- Query を別の Action でも使うようになったら、`<Type>/Query/` へ移す必要がある
- Infrastructures の QueryService の実装から、Action の奥にある interface を参照する
- PHPArkitect の設定は Architecture DSL ではなく、同じ意味のルールを直接組み立てる形になる
