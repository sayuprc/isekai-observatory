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

    public function getName(): string
    {
        return match ($this) {
            self::Mv => 'MV',
            self::AudioVideo => '音源動画',
            self::LiveStream => '配信',
            self::Short => 'ショート',
            self::Post => '投稿',
            self::Other => 'その他',
        };
    }

    public function equals(self $other): bool
    {
        return $this === $other;
    }
}
