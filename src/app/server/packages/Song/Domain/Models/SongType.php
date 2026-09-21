<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use Support\Domain\Exceptions\InvalidDomainException;

enum SongType: int
{
    case Original = 1;

    case Cover = 2;

    /**
     * @throws InvalidDomainException
     */
    public static function fromValue(int $value): self
    {
        return self::tryFrom($value) ?? throw new InvalidDomainException("不正な楽曲種別です: {$value}");
    }

    public function getName(): string
    {
        return match ($this) {
            self::Original => 'オリジナル曲',
            self::Cover => 'カバー曲',
        };
    }

    public function equals(self $other): bool
    {
        return $this === $other;
    }
}
