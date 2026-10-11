<?php

declare(strict_types=1);

namespace Media\Domain\Models;

enum MediaType: int
{
    case Mv = 1;

    case AudioVideo = 2;

    case LiveStream = 3;

    case Short = 4;

    case Post = 5;

    case Other = 99;

    public function equals(self $other): bool
    {
        return $this === $other;
    }
}
