<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects\Numeric;

use Override;

abstract readonly class PositiveIntegerValueObject extends IntegerValueObject
{
    #[Override]
    protected static function isValid(int $value): bool
    {
        return 0 < $value;
    }

    #[Override]
    protected static function getMessage(int $value): string
    {
        return "正の整数ではありません: {$value}";
    }
}
