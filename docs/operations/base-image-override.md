# ベースイメージの緊急差し替え

上流の修正を待てない脆弱性や registry 側の不具合があるとき、ベースイメージを手元のソースからビルドして差し替える手順です

変更するのは `docker-bake.hcl` の `BASE_IMAGES` と `infra/docker/base/` だけで、Dockerfile は触らない

背景は ADR-0042 を参照

## 差し替える

1. `docker-bake.hcl` の該当エントリの `source` を `src` にする

```hcl
php-cli    = { image = "php:8.5.12-cli-alpine", source = "src" }
```

2. ソースを取得する

```bash
./tools/fetch-docker-library-image.sh
```

`source = "src"` のエントリのうち、`infra/docker/base/<キー>/` がまだ無いものをすべて取得する

ディレクトリが既にあるものは、書き換え済みのソースを上書きしないよう飛ばす

1 つだけ取得したいときは `./tools/fetch-docker-library-image.sh php-cli` のようにキーを渡す

取得元は各ディレクトリの `.docker-library-source` に記録される

タグが official-images から消えている場合は `OFFICIAL_IMAGES_REF=<official-images の commit>` を付けて実行する

3. 取得したら一度そのままコミットし、続けて書き換えを別コミットにする。上流との差分を追えるようにするため

4. 差し替わることを確認する

```bash
docker buildx bake -f docker-bake.hcl --print cli
```

`contexts` の `base-php-cli` が `target:src-php-cli` になっていれば差し替わる

5. 手元でビルドして動作を確認してから PR を出す。マージ後は通常どおり Cloud Build がこの定義でビルドする

## 戻す

上流で修正版が出たら、次を 1 つの PR で行う

- 該当エントリの `source` を `dockerhub` に戻し、必要なら `image` を修正版に上げる
- `infra/docker/base/` 配下の該当ディレクトリを消す

`source = "src"` の間も Renovate は `image` の更新 PR を出す。修正版が出たかどうかの合図に使える

## 注意

- `infra/docker/base/` 配下は Renovate の対象外にしている
- 取得スクリプトは GitHub 上の Dockerfile でビルドされる Docker Official Image だけに対応する
- debian (oci-import) や `dunglas/frankenphp` などは、`infra/docker/base/<キー>/Dockerfile` を手で用意して `source = "src"` にする
- 取得したベースの Dockerfile 自体の `FROM` (例: `alpine:3.24`) は `BASE_IMAGES` を通らず registry から取得する。こちらも直すなら、取得したベースの Dockerfile 側を書き換える
