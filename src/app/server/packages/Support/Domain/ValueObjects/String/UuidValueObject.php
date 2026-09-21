<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects\String;

use Override;

abstract readonly class UuidValueObject extends StringValueObject
{
    #[Override]
    protected static function isValid(string $value): bool
    {
        return (bool)preg_match('/\A[\da-fA-F]{8}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{12}\z/', $value);
    }

    #[Override]
    protected static function getMessage(string $value): string
    {
        return "形式が不正です: {$value}";
    }
}
