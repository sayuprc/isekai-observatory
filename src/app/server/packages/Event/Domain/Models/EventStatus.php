<?php

declare(strict_types=1);

namespace Event\Domain\Models;

enum EventStatus: int
{
    case Normal = 1;

    case Postponed = 2;

    case Cancelled = 3;

    public function getName(): string
    {
        return match ($this) {
            self::Normal => '通常',
            self::Postponed => '延期',
            self::Cancelled => '中止',
        };
    }

    /**
     * 延期・中止のイベントは楽曲披露とセットリストを持てない
     */
    public function allowsPerformances(): bool
    {
        return $this === self::Normal;
    }
}
