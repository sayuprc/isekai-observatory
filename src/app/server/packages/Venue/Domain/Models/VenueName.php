<?php

declare(strict_types=1);

namespace Venue\Domain\Models;

use Support\Domain\ValueObjects\String\TextValueObject;

readonly class VenueName extends TextValueObject
{
    public function __construct(string $value)
    {
        parent::__construct(mb_trim($value));
    }

    protected static function isValid(string $value): bool
    {
        $length = mb_strlen($value);

        return $length >= 1
            && $length <= 255
            && ! self::containsControlCharacter($value);
    }

    protected static function getMessage(string $value): string
    {
        return sprintf('開催先名は1〜255文字で、改行や制御文字を含められません: %s', $value);
    }

    private static function containsControlCharacter(string $value): bool
    {
        return preg_match('/[\x{0000}-\x{001F}\x{007F}]/u', $value) === 1;
    }
}
