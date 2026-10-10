<?php

declare(strict_types=1);

namespace Song\Application\Public\Query;

use Support\Pagination\KeysetCursor;

final class SongListPageToken
{
    public static function encode(int $orderNo, string $songId): string
    {
        return KeysetCursor::encode([
            'orderNo' => $orderNo,
            'songId' => $songId,
        ]);
    }

    public static function decode(string $value): DecodedSongListPageToken
    {
        $cursor = KeysetCursor::decode($value);

        return new DecodedSongListPageToken($cursor->int('orderNo'), $cursor->string('songId'));
    }
}
