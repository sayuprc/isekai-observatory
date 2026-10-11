<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects\Date;

use DateTimeImmutable;
use Support\Domain\Exceptions\InvalidDomainException;

abstract readonly class ImmutableDateTimeValueObject
{
    /**
     * @throws InvalidDomainException
     */
    final public function __construct(public DateTimeImmutable $value)
    {
        if (! static::isValid($this->value)) {
            throw new InvalidDomainException(static::getMessage($this->value));
        }
    }

    protected static function isValid(DateTimeImmutable $value): bool
    {
        return true;
    }

    protected static function getMessage(DateTimeImmutable $value): string
    {
        return "日時が不正です: {$value->format('Y-m-d H:i:s')}";
    }

    public function equals(self $other): bool
    {
        return $this::class === $other::class
            && $this->value->format('Y-m-d H:i:s') === $other->value->format('Y-m-d H:i:s');
    }
}
