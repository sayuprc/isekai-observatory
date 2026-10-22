import type { Meta, Resource } from '../shared/layout.ts';
import { chunkPath, metaPath } from '../shared/layout.ts';
import { rechunk } from './pages.ts';

export type WriteFile = (path: string, content: string) => Promise<void>;

/**
 * 一覧の全件を塊ごとのファイルと件数のメタデータとして書き出す
 * 全件をメモリに載せないよう、塊が埋まるたびに書き出す
 */
export const writeResource = async (
  resource: Resource,
  pages: AsyncIterable<unknown[]>,
  writeFile: WriteFile,
): Promise<Meta> => {
  let count = 0;
  let chunkIndex = 0;

  for await (const chunk of rechunk(pages)) {
    await writeFile(chunkPath(resource, chunkIndex), JSON.stringify(chunk));
    count += chunk.length;
    chunkIndex++;
  }

  const meta: Meta = { count };
  await writeFile(metaPath(resource), JSON.stringify(meta));

  return meta;
};
