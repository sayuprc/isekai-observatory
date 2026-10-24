import { mkdir, readdir, readFile, writeFile } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { RESOURCES } from '../shared/layout.ts';
import type { Resource } from '../shared/layout.ts';
import { buildIndex, SEARCH_SPECS, searchIndexPath } from '../shared/search.ts';

const outDir = process.env.SNAPSHOT_DIR ?? 'snapshot';

const readItems = async (resource: Resource): Promise<Record<string, unknown>[]> => {
  const files = (await readdir(join(outDir, resource)))
    .filter((f) => /^\d+\.json$/.test(f))
    .sort((a, b) => parseInt(a) - parseInt(b));
  const chunks = await Promise.all(
    files.map(async (f) => JSON.parse(await readFile(join(outDir, resource, f), 'utf8'))),
  );

  return chunks.flat();
};

for (const resource of RESOURCES) {
  const spec = SEARCH_SPECS[resource];

  if (spec === undefined) {
    continue;
  }

  const items = await readItems(resource);
  const path = join(outDir, searchIndexPath(resource));
  await mkdir(dirname(path), { recursive: true });
  await writeFile(path, JSON.stringify(buildIndex(spec, items)));
  console.log(`${resource}: indexed ${items.length} items`);
}
