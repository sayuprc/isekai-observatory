import { client } from '../../utils/client';
import { toErrorMessage } from '../media/media-utils';

export type BulkTargetKey = 'songs' | 'events' | 'release-groups' | 'media';

export type BulkUpdateBody = {
  ids: string[];
  addTagIds: string[];
  removeTagIds: string[];
  // undefined は表示設定を変えない
  isDisplay: boolean | undefined;
};

export type BulkUpdateResult = { ok: true; updatedCount: number } | { ok: false; message: string };

const toResult = (data: { updatedCount: number } | null, error: unknown, status: number): BulkUpdateResult =>
  data
    ? { ok: true, updatedCount: data.updatedCount }
    : { ok: false, message: toErrorMessage(error, `更新に失敗しました (${status})`) };

// 任意項目は、指定していないものを送らない
const optionalFields = (body: BulkUpdateBody) => ({
  addTagIds: body.addTagIds,
  removeTagIds: body.removeTagIds,
  ...(body.isDisplay === undefined ? {} : { isDisplay: body.isDisplay }),
});

export const bulkUpdate = async (target: BulkTargetKey, body: BulkUpdateBody): Promise<BulkUpdateResult> => {
  switch (target) {
    case 'songs': {
      const { data, error, status } = await client.api.songs['bulk-update'].post({
        songIds: body.ids,
        ...optionalFields(body),
      });
      return toResult(data, error, status);
    }
    case 'events': {
      const { data, error, status } = await client.api.events['bulk-update'].post({
        eventIds: body.ids,
        ...optionalFields(body),
      });
      return toResult(data, error, status);
    }
    case 'release-groups': {
      const { data, error, status } = await client.api['release-groups']['bulk-update'].post({
        releaseGroupIds: body.ids,
        ...optionalFields(body),
      });
      return toResult(data, error, status);
    }
    case 'media': {
      const { data, error, status } = await client.api.media['bulk-update'].post({
        mediaIds: body.ids,
        ...optionalFields(body),
      });
      return toResult(data, error, status);
    }
  }
};
