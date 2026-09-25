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

    public function getName(): string
    {
        return match ($this) {
            self::Live => 'ライブ',
            self::Stream => '配信',
            self::Exhibition => '個展',
            self::Radio => 'ラジオ',
            self::Other => 'その他',
        };
    }

    /**
     * セットリストはライブと配信だけが持てる
     */
    public function allowsSetlist(): bool
    {
        return in_array($this, [self::Live, self::Stream], true);
    }
}
