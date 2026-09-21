<?php

declare(strict_types=1);

namespace Media\Application\Viewer\Query;

use InvalidArgumentException;

final class MediaListCursor
{
    public static function encode(string $publishedAt, string $mediaId): string
    {
        return base64_encode(
            (string)json_encode(
                [
                    'publishedAt' => $publishedAt,
                    'mediaId' => $mediaId,
                ],
                JSON_THROW_ON_ERROR,
            ),
        );
    }

    public static function decode(string $value): DecodedMediaListCursor
    {
        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            throw new InvalidArgumentException('Invalid cursor.');
        }

        $data = json_decode($decoded, true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($data) || ! isset($data['publishedAt'], $data['mediaId']) || ! is_string($data['publishedAt']) || ! is_string($data['mediaId'])) {
            throw new InvalidArgumentException('Invalid cursor.');
        }

        return new DecodedMediaListCursor($data['publishedAt'], $data['mediaId']);
    }
}
