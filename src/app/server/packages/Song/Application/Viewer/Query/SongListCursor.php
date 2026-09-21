<?php

declare(strict_types=1);

namespace Song\Application\Viewer\Query;

use InvalidArgumentException;

final class SongListCursor
{
    public static function encode(int $orderNo, string $songId): string
    {
        return base64_encode(
            (string)json_encode(
                [
                    'orderNo' => $orderNo,
                    'songId' => $songId,
                ],
                JSON_THROW_ON_ERROR,
            ),
        );
    }

    public static function decode(string $value): DecodedSongListCursor
    {
        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            throw new InvalidArgumentException('Invalid cursor.');
        }

        $data = json_decode($decoded, true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($data) || ! isset($data['orderNo'], $data['songId']) || ! is_int($data['orderNo']) || ! is_string($data['songId'])) {
            throw new InvalidArgumentException('Invalid cursor.');
        }

        return new DecodedSongListCursor($data['orderNo'], $data['songId']);
    }
}
