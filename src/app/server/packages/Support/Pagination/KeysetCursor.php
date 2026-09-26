<?php

declare(strict_types=1);

namespace Support\Pagination;

use InvalidArgumentException;
use JsonException;

/**
 * キーセットページングのカーソル (キー名 => 値 の JSON を base64 にしたもの) を読み書きする
 *
 * 各一覧のカーソルクラスはキーの組み立てと型付きの取り出しだけを担う
 */
final readonly class KeysetCursor
{
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
     * @throws InvalidArgumentException カーソルとして読めない値のとき
     */
    public static function decode(string $value): self
    {
        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            throw new InvalidArgumentException('Invalid cursor.');
        }

        try {
            $keys = json_decode($decoded, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException('Invalid cursor.', previous: $e);
        }

        if (! is_array($keys)) {
            throw new InvalidArgumentException('Invalid cursor.');
        }

        /** @var array<string, mixed> $keys */
        return new self($keys);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function string(string $key): string
    {
        $value = $this->keys[$key] ?? null;

        if (! is_string($value)) {
            throw new InvalidArgumentException('Invalid cursor.');
        }

        return $value;
    }

    /**
     * キーは必須で、値に null を許す
     *
     * @throws InvalidArgumentException
     */
    public function nullableString(string $key): ?string
    {
        if (! array_key_exists($key, $this->keys)) {
            throw new InvalidArgumentException('Invalid cursor.');
        }

        $value = $this->keys[$key];

        if ($value !== null && ! is_string($value)) {
            throw new InvalidArgumentException('Invalid cursor.');
        }

        return $value;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function int(string $key): int
    {
        $value = $this->keys[$key] ?? null;

        if (! is_int($value)) {
            throw new InvalidArgumentException('Invalid cursor.');
        }

        return $value;
    }
}
