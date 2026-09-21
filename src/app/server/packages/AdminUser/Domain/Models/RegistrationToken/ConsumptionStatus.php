<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models\RegistrationToken;

enum ConsumptionStatus: int
{
    case Unused = 0;

    case Consumed = 1;

    public function isAvailable(): bool
    {
        return $this === self::Unused;
    }
}
