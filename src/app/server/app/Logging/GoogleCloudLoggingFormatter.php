<?php

declare(strict_types=1);

namespace App\Logging;

use Monolog\Formatter\GoogleCloudLoggingFormatter as BaseFormatter;
use Monolog\LogRecord;

/**
 * Cloud Logging 互換の JSON を出力するフォーマッタ
 *
 * Monolog 標準の {@see BaseFormatter} に加えて、`extra` に積まれた
 * `logging.googleapis.com/*`(trace など)の特殊フィールドをトップレベルへ引き上げる
 * Cloud Logging はこれらをトップレベルでのみ解釈するため
 */
final class GoogleCloudLoggingFormatter extends BaseFormatter
{
    private const string GCP_FIELD_PREFIX = 'logging.googleapis.com/';

    protected function normalizeRecord(LogRecord $record): array
    {
        $normalized = parent::normalizeRecord($record);

        $extra = $normalized['extra'] ?? [];

        if (! is_array($extra)) {
            return $normalized;
        }

        $promoted = [];

        foreach ($extra as $key => $value) {
            if (is_string($key) && is_scalar($value) && str_starts_with($key, self::GCP_FIELD_PREFIX)) {
                $promoted[$key] = $value;
                unset($extra[$key]);
            }
        }

        if ($promoted === []) {
            return $normalized;
        }

        $normalized['extra'] = $extra;

        return [...$normalized, ...$promoted];
    }
}
