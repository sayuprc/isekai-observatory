<?php

declare(strict_types=1);

namespace Release\Domain\Models;

enum ReleaseGroupType: int
{
    case Single = 1;

    case Album = 2;

    case Ep = 3;

    case Other = 99;

    public function equals(self $other): bool
    {
        return $this === $other;
    }
}
