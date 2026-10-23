import { copyFile, mkdir, rm, writeFile } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { OPENAPI_PATH, renderIndexHtml, SCALAR_SCRIPT_PATH } from './html.ts';

const publicApiUrl = process.env.PUBLIC_API_URL;

if (publicApiUrl === undefined) {
  throw new Error('PUBLIC_API_URL is required');
}

const outDir = process.env.DOCS_DIR ?? 'docs';

const openapiFile = fileURLToPath(
  new URL('../../../contracts/generated/oas/IsekaiObservatory.Public.v1.yaml', import.meta.url),
);

// standalone ビルドは package の exports に無いため、entry の位置からたどる
const scalarFile = join(dirname(fileURLToPath(import.meta.resolve('@scalar/api-reference'))), 'browser/standalone.js');

await rm(outDir, { recursive: true, force: true });
await mkdir(outDir, { recursive: true });
await writeFile(join(outDir, 'index.html'), renderIndexHtml(publicApiUrl));
await copyFile(openapiFile, join(outDir, OPENAPI_PATH));
await copyFile(scalarFile, join(outDir, SCALAR_SCRIPT_PATH));

console.log(`docs: ${outDir}`);
