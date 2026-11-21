---
id: ADR-0043
status: accepted
superseded_by: null
applies_to: [api]
---

# Application 層は種別と Action で分け、Query は Action の中に置く

## Context

server の Application 層は `<Feature>/Application/<Type>/{UseCase,Query,Assemble}` の構成だった

`UseCase` の段は Action を束ねるだけで、階層を 1 段深くしていた

Query (QueryService の interface と DTO) の大半は 1 つの Action からしか使われないのに、Action から離れた `<Type>/Query/` に置かれていた

`<Type>` (Admin / Public / Viewer) の段は、種別ごとに中身の違う同名クラス (`List` のユースケース、`EventListItem` など) の名前空間として機能している

## Decision

Application 層を `<Feature>/Application/<Type>/<Action>/` の構成にする

- `UseCase` の段は置かない。ADR-0006 (ADOP) の層名に合わせ、`Application` を残す
- `<Type>` の段は残す
- `Group` のようなサブ集約は Action の手前に置く (例: `Person/Application/Admin/Group/Create/`)
- 1 つの Action だけが使う Query は `<Action>/Query/` に置く
- 複数の Action で共有する Query は、それらの Action の共通の親に `Query/` として置く (例: `Person/Application/Admin/Group/Query/`)
- 複数の Action で共有する Assemble は `<Type>/Assemble/` に置く
- Query の DTO (`*Item`、`*Page`、ページトークン、カーソル) は、それを返す QueryService の interface と同じ場所に置く

tests は `tests/{Unit,Integration}/<Feature>/Application/<Type>/` 以下に、この構成と同じ形で置く

## Consequences

### Positive

- Action のディレクトリを見れば、ユースケースと、それが使う Query がまとめて分かる
- 階層が 1 段浅くなる

### Negative

- `<Type>/` の直下には Action と共有物 (`Assemble`、`Query`) が並ぶので、ディレクトリ名だけでは Action かどうか区別できない
- Query を新しい Action でも使うようになったら、共通の親へ移す必要がある
- Infrastructures の QueryService の実装から、Action の奥にある interface を参照する
