<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Domain\Models;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongType;
use Tests\TestCase;

class SongTest extends TestCase
{
    #[Test]
    #[DataProvider('equalsDataProvider')]
    public function equals(Song $object, Song $other, bool $expected): void
    {
        $this->assertSame($expected, $object->equals($other));
    }

    public static function equalsDataProvider(): array
    {
        return [
            [
                self::song('11111111-1111-1111-1111-111111111111', 'Song Title', 'Song Description', SongType::Original, true, 1),
                self::song('11111111-1111-1111-1111-111111111111', 'Song Title', 'Song Description', SongType::Original, true, 1),
                true,
            ],
            [
                self::song('11111111-1111-1111-1111-111111111111', 'Song Title', 'Song Description', SongType::Original, true, 1),
                self::song('11111111-1111-1111-1111-111111111111', 'Other Song', 'Other Description', SongType::Cover, false, 2),
                true,
            ],
            [
                self::song('11111111-1111-1111-1111-111111111111', 'Song Title', 'Song Description', SongType::Original, true, 1),
                self::song('22222222-2222-2222-2222-222222222222', 'Song Title', 'Song Description', SongType::Original, true, 1),
                false,
            ],
            [
                self::song('11111111-1111-1111-1111-111111111111', 'Song Title', 'Song Description', SongType::Original, true, 1),
                self::song('22222222-2222-2222-2222-222222222222', 'Other Song', 'Other Description', SongType::Cover, false, 2),
                false,
            ],
        ];
    }

    private static function song(
        string $songId,
        string $title,
        string $description,
        SongType $type,
        bool $isDisplay,
        int $orderNo,
    ): Song {
        return Song::reconstruct(
            $songId,
            $title,
            $description,
            null,
            $type->value,
            $isDisplay,
            $orderNo,
            [],
            [
                ['personId' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'role' => 1, 'orderNo' => 1],
                ['personId' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', 'role' => 2, 'orderNo' => 2],
                ['personId' => 'cccccccc-cccc-cccc-cccc-cccccccccccc', 'role' => 3, 'orderNo' => 3],
            ],
            [],
        );
    }
}
