<?php

declare(strict_types=1);

namespace Support\Pagination;

use JsonException;
use Support\Domain\Exceptions\BusinessRuleViolationException;

/**
 * キーセットページングのカーソル (キー名 => 値 の JSON を base64 にしたもの) を読み書きする
 *
 * 各一覧のカーソルクラスはキーの組み立てと型付きの取り出しだけを担う
 *
 * カーソルの中身は契約 (JSON Schema) では検証できないため、読めない値は業務ルール違反 (400) とする (ADR-0014)
 */
final readonly class KeysetCursor
{
    private const string INVALID_MESSAGE = 'カーソルが不正です。';

    /**
     * @param array<string, mixed> $keys
     */
    private function __construct(private array $keys)
    {
    }

    /**
     * @param array<string, int|string|null> $keys
     */
    public static function encode(array $keys): string
    {
        return base64_encode(json_encode($keys, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws BusinessRuleViolationException カーソルとして読めない値のとき
     */
    public static function decode(string $value): self
    {
        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            throw new BusinessRuleViolationException(self::INVALID_MESSAGE);
        }

        try {
            $keys = json_decode($decoded, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new BusinessRuleViolationException(self::INVALID_MESSAGE, previous: $e);
        }

        if (! is_array($keys)) {
            throw new BusinessRuleViolationException(self::INVALID_MESSAGE);
        }

        /** @var array<string, mixed> $keys */
        return new self($keys);
    }

    /**
     * @throws BusinessRuleViolationException
     */
    public function string(string $key): string
    {
        $value = $this->keys[$key] ?? null;

        if (! is_string($value)) {
            throw new BusinessRuleViolationException(self::INVALID_MESSAGE);
        }

        return $value;
    }

    /**
     * キーは必須で、値に null を許す
     *
     * @throws BusinessRuleViolationException
     */
    public function nullableString(string $key): ?string
    {
        if (! array_key_exists($key, $this->keys)) {
            throw new BusinessRuleViolationException(self::INVALID_MESSAGE);
        }

        $value = $this->keys[$key];

        if ($value !== null && ! is_string($value)) {
            throw new BusinessRuleViolationException(self::INVALID_MESSAGE);
        }

        return $value;
    }

    /**
     * @throws BusinessRuleViolationException
     */
    public function int(string $key): int
    {
        $value = $this->keys[$key] ?? null;

        if (! is_int($value)) {
            throw new BusinessRuleViolationException(self::INVALID_MESSAGE);
        }

        return $value;
    }
}
