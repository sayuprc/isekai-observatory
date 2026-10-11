<?php

declare(strict_types=1);

namespace Tests\Unit\Release\Domain\Models;

use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\Tracks;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\Exceptions\InvalidDomainException;
use Tests\TestCase;

class TracksTest extends TestCase
{
    private const string SONG_ID = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

    #[Test]
    public function canCreateFromMixedTracks(): void
    {
        $result = Tracks::fromArray([
            ['songId' => self::SONG_ID, 'title' => null, 'trackNo' => 1],
            ['songId' => null, 'title' => '管理対象外の楽曲', 'trackNo' => 2],
            ['songId' => self::SONG_ID, 'title' => '楽曲A -instrumental-', 'trackNo' => 3],
        ]);

        $this->assertSame([
            ['song_id' => self::SONG_ID, 'title' => null, 'track_no' => 1],
            ['song_id' => null, 'title' => '管理対象外の楽曲', 'track_no' => 2],
            ['song_id' => self::SONG_ID, 'title' => '楽曲A -instrumental-', 'track_no' => 3],
        ], $result->toArray());
    }

    #[Test]
    public function cannotCreateWithNeitherSongIdNorTitle(): void
    {
        $this->expectException(BusinessRuleViolationException::class);
        $this->expectExceptionMessage('収録曲には楽曲かタイトルの少なくとも一方を指定してください。');

        Tracks::fromArray([
            ['songId' => null, 'title' => null, 'trackNo' => 1],
        ]);
    }

    #[Test]
    public function cannotCreateWithEmptyTitle(): void
    {
        $this->expectException(InvalidDomainException::class);

        $result = Tracks::fromArray([
            ['songId' => null, 'title' => '', 'trackNo' => 1],
        ]);
    }

    #[Test]
    public function cannotCreateWithDuplicatedTrackNos(): void
    {
        $this->expectException(BusinessRuleViolationException::class);
        $this->expectExceptionMessage('同じ曲順を複数指定することはできません。');

        Tracks::fromArray([
            ['songId' => self::SONG_ID, 'title' => null, 'trackNo' => 1],
            ['songId' => null, 'title' => '管理対象外の楽曲', 'trackNo' => 1],
        ]);
    }

    #[Test]
    public function canReconstruct(): void
    {
        $tracks = Tracks::reconstruct([
            ['songId' => self::SONG_ID, 'title' => null, 'trackNo' => 1],
            ['songId' => null, 'title' => '管理対象外の楽曲', 'trackNo' => 2],
        ]);

        $this->assertSame([
            ['song_id' => self::SONG_ID, 'title' => null, 'track_no' => 1],
            ['song_id' => null, 'title' => '管理対象外の楽曲', 'track_no' => 2],
        ], $tracks->toArray());
    }
}
