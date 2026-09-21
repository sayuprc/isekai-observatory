// @ts-check
import process from 'node:process';
import node from '@astrojs/node';
import solidJs from '@astrojs/solid-js';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig, envField } from 'astro/config';

const port = Number(process.env.PORT ?? '4321');
const shouldBundleServerDependencies = process.env.ADMIN_SSR_NO_EXTERNAL === 'true';

// https://astro.build/config
export default defineConfig({
  output: 'server',
  server: {
    host: true,
    port: port,
    allowedHosts: ['local.admin.isekaijoucho.fan'],
  },
  adapter: node({
    mode: 'standalone',
  }),
  security: {
    checkOrigin: false,
  },
  env: {
    schema: {
      API_URL: envField.string({ context: 'server', access: 'secret' }),
      CACHE_URL: envField.string({ context: 'server', access: 'secret' }),
      CACHE_TOKEN: envField.string({ context: 'server', access: 'secret' }),
      // proxy Worker 経由を検証する共有シークレット。未設定の環境では検証しない
      PROXY_SHARED_SECRET: envField.string({ context: 'server', access: 'secret', optional: true }),
      // 環境バッジの表示判定に使う。未設定時は production 扱いでバッジを出さない
      APP_ENV: envField.string({ context: 'server', access: 'public', default: 'production' }),
      PUBLIC_APP_URL: envField.string({ context: 'client', access: 'public' }),
    },
  },
  vite: {
    plugins: [tailwindcss()],
    ssr: {
      noExternal: shouldBundleServerDependencies ? true : undefined,
    },
  },
  integrations: [solidJs()],
});
