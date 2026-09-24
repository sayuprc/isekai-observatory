<?php

declare(strict_types=1);

namespace Event\Domain\Models;

enum EventStatus: int
{
    case Normal = 1;

    case Postponed = 2;

    case Cancelled = 3;
}
