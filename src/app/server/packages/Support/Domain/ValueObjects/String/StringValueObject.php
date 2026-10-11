<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects\String;

use Support\Domain\Exceptions\InvalidDomainException;

/**
 * @phpstan-consistent-constructor
 */
abstract readonly class StringValueObject
{
    /**
     * @throws InvalidDomainException
     */
    public function __construct(public string $value)
    {
        if (! static::isValid($this->value)) {
            throw new InvalidDomainException(static::getMessage($this->value));
        }
    }

    protected static function isValid(string $value): bool
    {
        return true;
    }

    protected static function getMessage(string $value): string
    {
        return "値が不正です: {$value}";
    }

    public function equals(self $other): bool
    {
        return $this::class === $other::class
            && $this->value === $other->value;
    }
}
