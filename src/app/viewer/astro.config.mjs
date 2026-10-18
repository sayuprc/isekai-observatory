// @ts-check
import process from 'node:process';
import sitemap from '@astrojs/sitemap';
import solidJs from '@astrojs/solid-js';
import { defineConfig } from 'astro/config';

const port = Number(import.meta.env.PORT ?? '3000');
const site = process.env.SITE_URL ?? 'https://local.isekaijoucho.fan';

/**
 * @fontsource の CSS をビルド前に書き換える
 *
 * @font-face は woff2 と woff を並記するが、woff2 に対応しないブラウザは対象外
 * Vite が URL を解決する前に woff の参照を落とし、ビルド成果物から woff ファイルごと除く
 *
 * font-display: swap だとフォールバックで描画した後にウェブフォントへ差し替わり、表示が一瞬切り替わって見える
 * optional は読み込みが間に合ったときだけ使い、間に合わなければそのページはフォールバックのまま固定する
 * 取得したフォントはキャッシュされるので、次の遷移以降は最初からウェブフォントで描画される
 *
 * @type {() => NonNullable<import('astro').ViteUserConfig['plugins']>[number]}
 */
const tuneFontsourceCss = () => ({
  name: 'tune-fontsource-css',
  enforce: 'pre',
  transform(code, id) {
    if (!id.includes('@fontsource') || !id.endsWith('.css')) {
      return null;
    }

    return code
      .replace(/,\s*url\([^)]+\.woff\)\s*format\('woff'\)/g, '')
      .replace(/font-display:\s*swap/g, 'font-display: optional');
  },
});

// https://astro.build/config
export default defineConfig({
  site: site,
  server: {
    host: true,
    port: port,
    allowedHosts: ['local.isekaijoucho.fan'],
  },
  // 一覧カードのクリックは DetailDrawer が詳細ページを取りに行くので、Astro 側の先読みは
  // 実際に遷移するリンク (ナビ) だけに絞る。opt-in にするため prefetchAll は既定の false のまま
  prefetch: {
    defaultStrategy: 'hover',
  },
  integrations: [solidJs(), sitemap()],
  vite: {
    build: {
      // 小さなスクリプトも全ページへのインライン展開ではなくハッシュ付きファイルとしてキャッシュさせる
      assetsInlineLimit: 0,
    },
    plugins: [tuneFontsourceCss()],
  },
});
