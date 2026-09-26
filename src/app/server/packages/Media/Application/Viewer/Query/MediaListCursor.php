<?php

declare(strict_types=1);

namespace Media\Application\Viewer\Query;

use Support\Pagination\KeysetCursor;

final class MediaListCursor
{
    public static function encode(string $publishedAt, string $mediaId): string
    {
        return KeysetCursor::encode([
            'publishedAt' => $publishedAt,
            'mediaId' => $mediaId,
        ]);
    }

    public static function decode(string $value): DecodedMediaListCursor
    {
        $cursor = KeysetCursor::decode($value);

        return new DecodedMediaListCursor($cursor->string('publishedAt'), $cursor->string('mediaId'));
    }
}
