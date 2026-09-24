<?php

declare(strict_types=1);

namespace Event\Domain\Models;

enum EventType: int
{
    case Live = 1;

    case Stream = 2;

    case Exhibition = 3;

    case Other = 99;
}
