/**
 * スナップショットの静的ファイル配置
 * 書き出し (snapshot) と配信 (worker) の両方がこの配置を前提にする
 */

/** 公開する一覧の path。Public 契約の route と一致させる */
export const RESOURCES = ['events', 'songs', 'release-groups', 'media', 'people', 'person-groups', 'venues'] as const;

export type Resource = (typeof RESOURCES)[number];

/** 1 ファイルに入れる件数。pageSize の上限と同じにして、1 ページが最大 2 ファイルにまたがるようにする */
export const CHUNK_SIZE = 100;

export type Meta = {
  count: number;
};

export const isResource = (value: string): value is Resource => (RESOURCES as readonly string[]).includes(value);

export const metaPath = (resource: Resource): string => `/${resource}/meta.json`;

export const chunkPath = (resource: Resource, chunkIndex: number): string => `/${resource}/${chunkIndex}.json`;
