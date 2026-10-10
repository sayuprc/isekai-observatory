<?php

declare(strict_types=1);

namespace Media\Application\Public\Query;

use Support\Pagination\KeysetCursor;

final class MediaListPageToken
{
    public static function encode(string $title, string $mediaId): string
    {
        return KeysetCursor::encode([
            'title' => $title,
            'mediaId' => $mediaId,
        ]);
    }

    public static function decode(string $value): DecodedMediaListPageToken
    {
        $cursor = KeysetCursor::decode($value);

        return new DecodedMediaListPageToken($cursor->string('title'), $cursor->string('mediaId'));
    }
}
