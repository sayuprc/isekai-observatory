// 指定 URL のスクリーンショットを撮る (クラウドセッションの動作確認用)
// Usage: node tools/cloud-session/screenshot.mjs <url> <out.png> [mobile|desktop]
import { execSync } from 'node:child_process';
import path from 'node:path';
import { pathToFileURL } from 'node:url';

const [url, out, device = 'mobile'] = process.argv.slice(2);

if (!url || !out) {
  console.error('usage: node tools/cloud-session/screenshot.mjs <url> <out.png> [mobile|desktop]');
  process.exit(2);
}

// playwright はリポジトリの依存にないため、クラウド環境にグローバル導入済みのものを使う
const loadPlaywright = async () => {
  try {
    return await import('playwright');
  } catch {
    const globalRoot = execSync('npm root -g', { encoding: 'utf8' }).trim();
    return import(pathToFileURL(path.join(globalRoot, 'playwright', 'index.mjs')).href);
  }
};

const viewports = {
  mobile: { width: 390, height: 844 },
  desktop: { width: 1440, height: 900 },
};

const { chromium } = await loadPlaywright();
const browser = await chromium.launch();

try {
  const page = await browser.newPage({ viewport: viewports[device] ?? viewports.mobile, deviceScaleFactor: 2 });
  await page.goto(url, { waitUntil: 'networkidle' });
  await page.screenshot({ path: out, fullPage: true });
  console.log(out);
} finally {
  await browser.close();
}
