<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects\Numeric;

use Support\Domain\Exceptions\InvalidDomainException;

abstract readonly class IntegerValueObject
{
    /**
     * @throws InvalidDomainException
     */
    final public function __construct(public int $value)
    {
        if (! static::isValid($this->value)) {
            throw new InvalidDomainException(static::getMessage($this->value));
        }
    }

    protected static function isValid(int $value): bool
    {
        return true;
    }

    protected static function getMessage(int $value): string
    {
        return "数値が不正です: {$value}";
    }

    public function equals(self $other): bool
    {
        return $this::class === $other::class
            && $this->value === $other->value;
    }
}
