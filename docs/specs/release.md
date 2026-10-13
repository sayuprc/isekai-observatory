# Release Spec

関連: ADR-0015 (画像)、ADR-0019 (階層と formats)、ADR-0034 (代表色を持たない)

## 用語

- **ReleaseGroup** (作品): `releaseGroupId` / `title` / `type` (Single / Album / Ep / Other) / `description` / `note` / `isDisplay` / `orderNo`
- **Release** (版): `releaseId` / 所属 Group / `name` (空可) / `releasedOn` / `description` / `note` / `isDisplay` / `orderNo` / `formats` (非空集合)
- **Medium** (トラックリストの区切り): `position` / `name?` / `tracks[]`
  契約上のフィールド名は `media` だが、Media パッケージの Media とは別物
- **Track**: `songIds` (順序付き、0 件以上) + `title?` (少なくとも一方必須) + `trackNo`
  Song を 1 件参照するトラックは表示名を `title` で上書きできる。タイトルのみの行は Song 非参照
  Song を 2 件以上参照するトラックはメドレーで、`title` はメドレーの表示名 (任意) になる。同じ Song を 1 トラックに重複して参照できない
- **ReleaseFormat**: Digital / Cd / Dvd / BluRay / Other。順序なし集合。Medium には載せない

## できること

- Admin: ReleaseGroup / Release の CRUD。一覧・検索の主語は ReleaseGroup
- Admin: Event のセットリスト順に楽曲披露を Track として取り込める
  メドレーの項目は 1 つのメドレー Track にまとめ、項目の表示名をメドレーの表示名にする
  セットリスト外の楽曲披露は末尾に並べ、表示名だけの項目はタイトルのみの Track として選んだときだけ取り込む
- Viewer: 公開 Group の cursor 一覧 (ネストで公開 Release / Medium / Track)
  公開 Release を 1 件以上持つ Group のみ返す

## できないこと

- 備考 (`note`) は管理用のメモとして Viewer に出さない
- ジャケットなど権利者画像を保存・配信しない
- 代表色を持たない
- formats を空にできない。Medium に format を持たせない
- 傘下 Release がある ReleaseGroup は削除できない
- Event から関連リリースとして参照されている Release は削除できない (Event 側がリンクを所有する。`event.md` 参照)
- Viewer に個別 get や `isDisplay` を出さない

## 主な関係

```mermaid
classDiagram
  direction TB
  ReleaseGroup "1" --> "N" Release : 別集約
  Release "1" --> "N" Medium
  Medium "1" --> "N" Track
  Track ..> Song : 0..N (順序付き)
```
