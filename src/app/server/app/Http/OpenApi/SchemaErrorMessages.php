<?php

declare(strict_types=1);

namespace App\Http\OpenApi;

/**
 * JSON Schema の keyword → 日本語メッセージの変換表 (ADR-0014)
 *
 * validator の英語文言をそのまま API へ出さないための唯一の置き場
 */
final class SchemaErrorMessages
{
    /**
     * @param array<string, mixed> $args
     */
    public static function translate(string $keyword, array $args): string
    {
        return match ($keyword) {
            'minLength' => sprintf('%d 文字以上で入力してください', self::intOf($args['min'] ?? null, 1)),
            'maxLength' => sprintf('%d 文字以内で入力してください', self::intOf($args['max'] ?? null, 0)),
            'minimum', 'exclusiveMinimum' => sprintf('%s 以上の値を指定してください', self::stringify($args['min'] ?? '')),
            'maximum', 'exclusiveMaximum' => sprintf('%s 以下の値を指定してください', self::stringify($args['max'] ?? '')),
            'minItems' => sprintf('%d 件以上指定してください', self::intOf($args['min'] ?? null, 1)),
            'maxItems' => sprintf('%d 件以内で指定してください', self::intOf($args['max'] ?? null, 0)),
            'type' => '型が不正です',
            'enum', 'const' => '許可されていない値です',
            'required' => '必須です',
            'format' => self::formatMessage(self::stringify($args['format'] ?? '')),
            default => '値が不正です',
        };
    }

    private static function formatMessage(string $format): string
    {
        return match ($format) {
            'uuid' => 'ID の形式が不正です',
            'date' => '日付は YYYY-MM-DD 形式で指定してください',
            'date-time' => '日時の形式が不正です',
            'uri' => 'URL の形式が不正です',
            default => '形式が不正です',
        };
    }

    private static function intOf(mixed $value, int $default): int
    {
        return is_int($value) ? $value : $default;
    }

    private static function stringify(mixed $value): string
    {
        return is_scalar($value) ? (string)$value : '';
    }
}
