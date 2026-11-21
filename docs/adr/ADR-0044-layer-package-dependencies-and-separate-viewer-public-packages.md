---
id: ADR-0044
status: accepted
superseded_by: null
applies_to: [api]
---

# パッケージ間の依存を一方向の階層にし、Viewer / Public の読み取りを専用パッケージに分ける

## Context

server は ADOP (ADR-0006) に従い、`packages/<Feature>/` の中を Domain / Application / Infrastructures に分けている

ADOP は 1 つのパッケージの中でどの層に置くかを決めるルールで、パッケージ同士の依存は決めていない

その結果、次の状態になっている

- PHPArkitect の許可リストは手書きで、Release / Event / Infrastructures / app を検査していない
- 最下層のはずの Support が、AdminUser の `Permission` / `AdminUserId` と Auth の `AuthContext` に依存している
- 業務パッケージから AdminUser への依存は、すべて `Permission` のためだけにある
- Song の Domain が Media の `MediaId` を使う一方で、Media の Viewer 向け Query が Song の `SongType` を使い、Media と Song が互いに依存している
- Viewer / Public の UseCase は QueryService を呼ぶだけで、Domain のロジックを使わない。QueryService の SQL は複数パッケージのテーブルをまたぐ
- 削除時の使用中判定は、Media / Release / Song / Tag / Venue では被参照側の Repository が参照元のテーブルを数える
- Person / PersonGroup だけは、Person が定義した interface を Song / Event が実装する。Song の実装は Event のテーブルも読んでいる

検討した案は次のとおり

- パッケージ間の依存は決めず、問題に気づいたら個別に直す: 今の状態がこれで、検査の抜けと循環がすでに出ている
- Viewer / Public を今の場所に残し、Query の DTO に enum ではなく値を持たせて循環だけを消す: 変更は小さいが、パッケージの外のテーブルを読む処理がパッケージの中に残り、別の enum で同じ循環が起きうる
- Admin も専用パッケージに分ける: 業務パッケージに Domain しか残らず、Domain とそれを使う書き込みの UseCase が離れる
- `Permission` を AdminUser に残し、認可と監査ログを Support から AdminUser / Auth へ移す: 監査ログを Support に置いた ADR-0022 を見直すことになり、変更が大きい
- 使用中判定を Person の方式 (被参照側が interface を定義し、参照元が実装する) に揃える: 参照元を足しても被参照側を直さずに済む。しかし一覧の使用件数 (ADR-0031) は契約が参照元の種別ごとに件数を持つので、被参照側は結局参照元を知る

## Decision

ADR-0006 の ADOP は変えず、パッケージ同士の依存のルールを足す

### パッケージの階層

```
Viewer / Public
Event
Release
Song
Media                    Auth
Tag / Person / Venue     AdminUser
──────────────────────────────────
Support
```

- 上のパッケージは、自分より下のパッケージにだけ依存してよい
- 同じ段のパッケージは互いに依存しない
- 左の列 (業務パッケージ) と右の列 (管理者パッケージ) は互いに依存しない
- Viewer / Public は業務パッケージと Support に依存してよく、ほかのどのパッケージからも依存されない
- Support はほかのどのパッケージにも依存しない
- app はすべてのパッケージに依存してよく、パッケージは app に依存しない

### Support を最下層にする

- `Permission` は Support の Domain に移す。`AuditTargetType` と同じく、全機能を並べた一覧として Support が持つ
- 監査ログの操作者は、Auth の `AuthContext` ではなく Support の `AuthorizationContextInterface` から取る
- 監査ログに明示で渡す操作者の ID は、AdminUser の `AdminUserId` ではなく Support の型で受ける

### 階層で縛るのは PHP の依存だけにする

- 業務パッケージ・管理者パッケージへの依存は、相手の Domain だけに向ける
- Support は共通基盤として、どの層からでも依存してよい。Support の中のどの区分にどの層から依存してよいかは、検査の設定で決める
- Infrastructures は、自分のパッケージの Domain と Application の Query、下のパッケージの Domain に依存してよい
- SQL で読むテーブルは縛らない。Infrastructures はどのパッケージのテーブルでも読んでよい
- 削除時の使用中判定は、被参照側の Repository が参照元のテーブルを数える形に揃える
- `PersonUsageCheckerInterface` / `PersonGroupUsageCheckerInterface` はなくし、判定を Person の Repository に移す

### Viewer / Public の読み取りは専用パッケージに置く

- 業務パッケージには、Domain と Admin 向けの Application / Infrastructures / Route を置く
- Viewer / Public 向けの UseCase、Query、QueryService、Route は `packages/Viewer/`、`packages/Public/` に置く
- 専用パッケージの中も ADOP に従い、Application の下はリソースごとに分ける (`Viewer/Application/<Resource>/UseCase/<Action>/`)
- 業務パッケージの `Application/Admin/` の段は残し、ADR-0043 の構成をそのまま使う
- ADR-0021 の、Viewer を Admin と別の契約・route・UseCase・presenter で作る方針は変えない

### 検査

- 階層は PHPArkitect の設定に表として 1 か所だけ持ち、パッケージ間の規則はそこから作る
- Release / Event / Infrastructures / app も検査の対象にする

## Consequences

### Positive

- パッケージ間の依存が一方向になり、循環が構造上起きない
- 業務パッケージが AdminUser / Auth を知らなくなる
- パッケージを足したときに、許可リストの書き忘れで検査から漏れることがなくなる
- Viewer / Public のコードが、`contracts/src/{viewer,public}` や `app/Http/…/{Viewer,Public}` と同じ単位にまとまる
- 使用中判定の書き方が 1 つになる

### Negative

- 被参照側の Infrastructures が参照元のテーブル名を知るので、参照元を足すと被参照側の Repository も直す必要がある
- 楽曲に項目を足すときなどに、業務パッケージと Viewer / Public パッケージをまたいで変えることが増える
- `Permission` が Support にあるので、権限を足すたびに Support を変える
- 移行のために、多くのファイルの namespace を変える
