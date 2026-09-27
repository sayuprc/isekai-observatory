# Cloud Session Verification

Claude Code on the web (クラウドセッション) で変更を動作確認する手順です。通勤中などローカル環境がない場面で、エージェントに確認まで任せるために使う

## セッション開始時に用意されるもの

`.claude/settings.json` の SessionStart hook (`tools/hooks/cloud-session-start.sh`) がクラウドでだけ動き、次を用意する

- `mise.toml` で固定した版の `bun` / `pnpm`
- `src/app` の pnpm 依存関係

ローカルでは `CLAUDE_CODE_REMOTE` が立たないため何もしない

## 確認できること

| 対象 | 方法 |
|---|---|
| Viewer / Admin の lint・typecheck | `cd src/app/<viewer or admin> && bun run prettier:format:check && bun run eslint:check && bun run stylelint:check && bun run typecheck` (`mise run *:check` と同じ内容) |
| contracts | `cd src/app && bun --filter contracts test` など |
| Viewer の画面 | `tools/cloud-session/viewer-preview.sh start` で API モック付きの dev server を起動し、`node tools/cloud-session/screenshot.mjs http://127.0.0.1:3000/<path> <out.png> [mobile\|desktop]` で撮影してユーザーへ送る |

Viewer の画面確認は、PHP API の代わりに Prism が OAS からモックを返す。そのため表示されるデータはダミーで、コンテンツコレクション (楽曲・リリース・イベントの一覧元) は空になる。レイアウトや表示崩れの確認には使えるが、実データでの確認にはならない

## 確認できないこと

クラウド環境のネットワーク制限 (Trusted) で次のホストに届かないため、PHP API サーバーは動かせない

| 止まる処理 | 届かないホスト |
|---|---|
| PHP イメージのビルド (`apt-get`, `install-php-extensions`) | `deb.debian.org`, `pecl.php.net` |
| CI がビルドした GHCR イメージの pull | `pkg-containers.githubusercontent.com` |
| `composer install` の dist 取得 | `api.github.com` |
| `mise install` / atlas の取得 | `api.github.com`, `mise.run`, `release.ariga.io` |

Docker デーモン自体は `dockerd` で起動でき、Docker Hub からの pull もできる。環境のネットワーク設定で上記ホストを許可すれば、`mise run setup` 相当の手順で API テストまで広げられる見込みがある
