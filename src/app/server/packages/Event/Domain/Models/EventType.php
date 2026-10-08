<?php

declare(strict_types=1);

namespace Event\Domain\Models;

enum EventType: int
{
    case Live = 1;

    case Stream = 2;

    case Exhibition = 3;

    case Radio = 4;

    case Other = 99;

    /**
     * セットリストはライブと配信だけが持てる
     */
    public function allowsSetlist(): bool
    {
        return in_array($this, [self::Live, self::Stream], true);
    }
}
