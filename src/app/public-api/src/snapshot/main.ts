import { mkdir, rm, writeFile } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { CHUNK_SIZE, RESOURCES } from '../shared/layout.ts';
import { getGoogleIdToken } from './google-id-token.ts';
import type { ListPage } from './pages.ts';
import { fetchAllPages } from './pages.ts';
import { writeResource } from './write.ts';

const apiUrl = process.env.API_URL;

if (apiUrl === undefined) {
  throw new Error('API_URL is required');
}

const outDir = process.env.SNAPSHOT_DIR ?? 'snapshot';

const fetchPage = async (resource: string, pageToken: string | undefined): Promise<ListPage> => {
  const url = new URL(`${apiUrl}/public/v1/${resource}`);
  url.searchParams.set('pageSize', String(CHUNK_SIZE));

  if (pageToken !== undefined) {
    url.searchParams.set('pageToken', pageToken);
  }

  const idToken = await getGoogleIdToken(apiUrl);
  const response = await fetch(url, {
    headers: idToken === null ? {} : { 'X-Serverless-Authorization': `Bearer ${idToken}` },
  });

  if (!response.ok) {
    throw new Error(`Failed to fetch ${url}: ${response.status}`);
  }

  return (await response.json()) as ListPage;
};

const writeSnapshotFile = async (path: string, content: string): Promise<void> => {
  const fullPath = join(outDir, path);
  await mkdir(dirname(fullPath), { recursive: true });
  await writeFile(fullPath, content);
};

await rm(outDir, { recursive: true, force: true });

for (const resource of RESOURCES) {
  const meta = await writeResource(
    resource,
    fetchAllPages((pageToken) => fetchPage(resource, pageToken)),
    writeSnapshotFile,
  );
  console.log(`${resource}: ${meta.count} items`);
}
