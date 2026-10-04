import type { AuditAction } from '../../generated';

export const ACTION_LABEL: Record<AuditAction, string> = {
  create: '作成',
  update: '更新',
  delete: '削除',
  register: '登録',
  login: 'ログイン',
  refresh: 'リフレッシュ',
  recovery_code_issue: 'リカバリーコード発行',
  recovery_code_use: 'リカバリーコード使用',
};
