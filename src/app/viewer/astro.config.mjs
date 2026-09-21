// @ts-check
import process from 'node:process';
import sitemap from '@astrojs/sitemap';
import solidJs from '@astrojs/solid-js';
import { defineConfig } from 'astro/config';

const port = Number(import.meta.env.PORT ?? '3000');
const site = process.env.SITE_URL ?? 'https://local.isekaijoucho.fan';

// @fontsource の @font-face は woff2 と woff を並記するが、woff2 に対応しないブラウザは対象外
// Vite が URL を解決する前に woff の参照を落とし、ビルド成果物から woff ファイルごと除く
/** @type {() => NonNullable<import('astro').ViteUserConfig['plugins']>[number]} */
const dropLegacyWoffSource = () => ({
  name: 'drop-legacy-woff-source',
  enforce: 'pre',
  transform(code, id) {
    if (!id.includes('@fontsource') || !id.endsWith('.css')) {
      return null;
    }

    return code.replace(/,\s*url\([^)]+\.woff\)\s*format\('woff'\)/g, '');
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
  integrations: [
    solidJs(),
    sitemap(),
  ],
  vite: {
    build: {
      // 小さなスクリプトも全ページへのインライン展開ではなくハッシュ付きファイルとしてキャッシュさせる
      assetsInlineLimit: 0,
    },
    plugins: [dropLegacyWoffSource()],
  },
});
