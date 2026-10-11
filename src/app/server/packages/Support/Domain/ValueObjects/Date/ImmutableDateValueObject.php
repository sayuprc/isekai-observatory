<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects\Date;

use DateType\ImmutableDate;
use Support\Domain\Exceptions\InvalidDomainException;

abstract readonly class ImmutableDateValueObject
{
    /**
     * @throws InvalidDomainException
     */
    final public function __construct(public ImmutableDate $value)
    {
        if (! static::isValid($this->value)) {
            throw new InvalidDomainException(static::getMessage($this->value));
        }
    }

    protected static function isValid(ImmutableDate $value): bool
    {
        return true;
    }

    protected static function getMessage(ImmutableDate $value): string
    {
        return "日付が不正です: {$value->format('Y-m-d')}";
    }

    public function equals(self $other): bool
    {
        return $this::class === $other::class
            && $this->value->format('Y-m-d') === $other->value->format('Y-m-d');
    }
}
