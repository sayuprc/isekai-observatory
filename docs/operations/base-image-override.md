# ベースイメージの緊急差し替え

上流の修正を待てない脆弱性や Docker Hub 側の不具合があるとき、ベースイメージを手元のソースからビルドして差し替える手順です

背景は ADR-0042 を参照

## 差し替える

1. Dockerfile の `FROM` / `COPY --from` に書いてあるイメージ名をそのまま指定して、ソースを取得する

   ```bash
   ./tools/fetch-docker-library-image.sh php:8.5.12-cli-alpine
   ```

   `infra/docker/base/php-8.5.12-cli-alpine/` に Dockerfile 一式と、取得元を記録した `.docker-library-source` ができる

   タグが official-images から消えている場合は `OFFICIAL_IMAGES_REF=<official-images の commit>` を付けて実行する

2. 取得したら一度そのままコミットし、続けて書き換えを別コミットにする。上流との差分を追えるようにするため

3. `docker-bake.hcl` の `BASE_OVERRIDES` にイメージ名とディレクトリを追加する

   ```hcl
   variable "BASE_OVERRIDES" {
     default = {
       "php:8.5.12-cli-alpine" = "infra/docker/base/php-8.5.12-cli-alpine"
     }
   }
   ```

4. 差し替わることを確認する

   ```bash
   docker buildx bake -f docker-bake.hcl --print cli
   ```

   対象ターゲットの `contexts` に `target:base-...` が入っていれば差し替わる

5. 手元でビルドして動作を確認してから PR を出す。マージ後は通常どおり Cloud Build がこの定義でビルドする

## 戻す

上流で修正版が出たら、Renovate の `FROM` 更新を取り込むのと同じ PR で次を行う

- `BASE_OVERRIDES` から該当行を消す
- `infra/docker/base/` 配下の該当ディレクトリを消す

## 注意

- `BASE_OVERRIDES` のキーは `FROM` の文字列と完全に一致しないと差し替わらない。Renovate が `FROM` のタグを上げると黙って Docker Hub に戻る
- 差し替え中に Renovate の `FROM` 更新 PR が来たら、取得し直すか差し替えをやめるかを決めてからマージする
- `infra/docker/base/` 配下は Renovate の対象外にしている
- 取得スクリプトは GitHub 上の Dockerfile でビルドされる Official Image だけに対応する
- debian (oci-import) や `dunglas/frankenphp` などは、`infra/docker/base/<名前>/Dockerfile` を手で用意して同じように `BASE_OVERRIDES` に書く
- 取得したベースの Dockerfile 自体の `FROM` (例: `alpine:3.24`) は `BASE_OVERRIDES` の対象にならず Docker Hub から取得する。こちらも直すなら、取得したベースの Dockerfile 側を書き換える
