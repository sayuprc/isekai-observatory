<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

use Support\Domain\Exceptions\InvalidDomainException;

enum Role: int
{
    case Privilege = 1;

    case Console = 2;

    case General = 3;

    /**
     * @throws InvalidDomainException
     */
    public static function fromValue(int $value): self
    {
        return self::tryFrom($value) ?? throw new InvalidDomainException("不正なロールです: {$value}");
    }

    public function isPrivilege(): bool
    {
        return in_array($this, [self::Privilege, self::Console], true);
    }

    public function getName(): string
    {
        return match ($this) {
            self::Privilege => '特権',
            self::Console => 'コンソール',
            self::General => '一般',
        };
    }
}
