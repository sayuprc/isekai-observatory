<?php

declare(strict_types=1);

namespace Support\Infrastructures\Database;

use UnexpectedValueException;

/**
 * DB から取得した行(array<string, mixed>)や集約値(mixed)を
 * 静的解析が追える型へ安全に変換するためのヘルパ
 *
 * PDO はカラム値を string で返すことがあるため、ドメインへ渡す前にここで型を確定させる
 */
final class Row
{
    /**
     * @param array<string, mixed> $row
     */
    public static function string(array $row, string $key): string
    {
        $value = $row[$key] ?? null;

        if (! is_scalar($value)) {
            throw new UnexpectedValueException(sprintf('列 %s は scalar である必要があります。', $key));
        }

        return (string)$value;
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function nullableString(array $row, string $key): ?string
    {
        $value = $row[$key] ?? null;

        if (is_null($value)) {
            return null;
        }

        if (! is_scalar($value)) {
            throw new UnexpectedValueException(sprintf('列 %s は scalar もしくは null である必要があります。', $key));
        }

        return (string)$value;
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function int(array $row, string $key): int
    {
        $value = $row[$key] ?? null;

        if (! is_numeric($value)) {
            throw new UnexpectedValueException(sprintf('列 %s は数値である必要があります。', $key));
        }

        return (int)$value;
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function bool(array $row, string $key): bool
    {
        return (bool)self::int($row, $key);
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function nullableBool(array $row, string $key): ?bool
    {
        $value = $row[$key] ?? null;

        if (is_null($value)) {
            return null;
        }

        if (! is_numeric($value)) {
            throw new UnexpectedValueException(sprintf('列 %s は bool 相当の数値もしくは null である必要があります。', $key));
        }

        return (bool)(int)$value;
    }

    /**
     * 集約関数などの単一スカラ値を int に変換する。値が無ければ 0 を返す
     */
    public static function intValue(mixed $value): int
    {
        return is_numeric($value) ? (int)$value : 0;
    }
}
