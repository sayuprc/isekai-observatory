<?php

declare(strict_types=1);

namespace Song\Application\Viewer\Query;

use Support\Pagination\KeysetCursor;

final class SongListCursor
{
    public static function encode(int $orderNo, string $songId): string
    {
        return KeysetCursor::encode([
            'orderNo' => $orderNo,
            'songId' => $songId,
        ]);
    }

    public static function decode(string $value): DecodedSongListCursor
    {
        $cursor = KeysetCursor::decode($value);

        return new DecodedSongListCursor($cursor->int('orderNo'), $cursor->string('songId'));
    }
}
