<?php

declare(strict_types=1);

namespace Release\Domain\Models;

enum ReleaseFormat: int
{
    case Digital = 1;

    case Cd = 2;

    case Dvd = 3;

    case BluRay = 4;

    case Other = 99;

    public function getName(): string
    {
        return match ($this) {
            self::Digital => '配信',
            self::Cd => 'CD',
            self::Dvd => 'DVD',
            self::BluRay => 'Blu-ray',
            self::Other => 'その他',
        };
    }

    public function equals(self $other): bool
    {
        return $this === $other;
    }
}
