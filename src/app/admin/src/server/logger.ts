import process from 'node:process';
import pino from 'pino';

/**
 * Cloud Run の stdout を Cloud Logging が構造化ログとして解釈できるよう、
 * severity / message / time / trace を JSON で出力する pino ロガー
 *
 * @see https://cloud.google.com/logging/docs/structured-logging
 */

const GCP_TRACE_FIELD = 'logging.googleapis.com/trace';
const GCP_TRACE_SAMPLED_FIELD = 'logging.googleapis.com/trace_sampled';

// pino のレベル名を Cloud Logging の severity enum に対応付ける
// 単純な大文字化では warn / fatal が Cloud Logging の語彙(WARNING / CRITICAL)と一致しない
const PINO_LEVEL_TO_SEVERITY: Record<string, string> = {
  trace: 'DEBUG',
  debug: 'DEBUG',
  info: 'INFO',
  warn: 'WARNING',
  error: 'ERROR',
  fatal: 'CRITICAL',
};

const projectId = process.env.GOOGLE_CLOUD_PROJECT ?? '';

// テスト時はログ出力をノイズにしないため抑止する
const defaultLevel = process.env.NODE_ENV === 'test' ? 'silent' : 'info';

export const logger = pino({
  level: process.env.LOG_LEVEL ?? defaultLevel,
  messageKey: 'message',
  // Cloud Logging は pid / hostname を必要としないため落とす
  base: undefined,
  formatters: {
    level: label => ({ severity: PINO_LEVEL_TO_SEVERITY[label] ?? 'DEFAULT' }),
  },
  timestamp: () => `,"time":"${new Date().toISOString()}"`,
});

/**
 * `X-Cloud-Trace-Context` (`TRACE_ID/SPAN_ID;o=OPTIONS`) を Cloud Logging の
 * トレースフィールドへ変換する。projectId 未設定・ヘッダー不在では何も付与しない
 *
 * spanId は header 上 uint64 の 10 進数で、Cloud Logging が要求する 16 桁 hex への
 * 変換が安全に行えないため付与しない。リクエスト単位のグルーピングには trace で足りる
 */
const resolveTraceFields = (headers: Headers): Record<string, string | boolean> | null => {
  if (projectId === '') {
    return null;
  }

  const header = headers.get('x-cloud-trace-context');

  if (!header) {
    return null;
  }

  const traceMatch = /^([0-9a-fA-F]+)/.exec(header);

  if (!traceMatch?.[1]) {
    return null;
  }

  const fields: Record<string, string | boolean> = {
    [GCP_TRACE_FIELD]: `projects/${projectId}/traces/${traceMatch[1]}`,
  };

  const sampledMatch = /;o=([01])/.exec(header);

  if (sampledMatch?.[1]) {
    fields[GCP_TRACE_SAMPLED_FIELD] = sampledMatch[1] === '1';
  }

  return fields;
};

/**
 * リクエストのトレース情報を束ねた子ロガーを返す
 * 同一リクエスト内のログが Cloud Logging 上で同じトレースにグルーピングされる
 */
export const createRequestLogger = (headers: Headers): pino.Logger => {
  const trace = resolveTraceFields(headers);

  return trace ? logger.child(trace) : logger;
};
