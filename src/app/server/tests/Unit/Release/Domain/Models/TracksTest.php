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
            ['songIds' => [self::SONG_ID], 'title' => null, 'trackNo' => 1],
            ['songIds' => [], 'title' => '管理対象外の楽曲', 'trackNo' => 2],
            ['songIds' => [self::SONG_ID], 'title' => '楽曲A -instrumental-', 'trackNo' => 3],
        ]);

        $this->assertSame(
            [
                ['song_ids' => [self::SONG_ID], 'title' => null, 'track_no' => 1],
                ['song_ids' => [], 'title' => '管理対象外の楽曲', 'track_no' => 2],
                ['song_ids' => [self::SONG_ID], 'title' => '楽曲A -instrumental-', 'track_no' => 3],
            ],
            $result->toArray(),
        );
    }

    #[Test]
    public function cannotCreateWithNeitherSongsNorTitle(): void
    {
        $this->expectException(BusinessRuleViolationException::class);
        $this->expectExceptionMessage('収録曲には楽曲かタイトルの少なくとも一方を指定してください。');

        Tracks::fromArray([
            ['songIds' => [], 'title' => null, 'trackNo' => 1],
        ]);
    }

    #[Test]
    public function cannotCreateWithEmptyTitle(): void
    {
        $this->expectException(InvalidDomainException::class);

        $result = Tracks::fromArray([
            ['songIds' => [], 'title' => '', 'trackNo' => 1],
        ]);
    }

    #[Test]
    public function cannotCreateWithDuplicatedTrackNos(): void
    {
        $this->expectException(BusinessRuleViolationException::class);
        $this->expectExceptionMessage('同じ曲順を複数指定することはできません。');

        Tracks::fromArray([
            ['songIds' => [self::SONG_ID], 'title' => null, 'trackNo' => 1],
            ['songIds' => [], 'title' => '管理対象外の楽曲', 'trackNo' => 1],
        ]);
    }

    #[Test]
    public function canReconstruct(): void
    {
        $tracks = Tracks::reconstruct([
            ['songIds' => [self::SONG_ID], 'title' => null, 'trackNo' => 1],
            ['songIds' => [], 'title' => '管理対象外の楽曲', 'trackNo' => 2],
        ]);

        $this->assertSame(
            [
                ['song_ids' => [self::SONG_ID], 'title' => null, 'track_no' => 1],
                ['song_ids' => [], 'title' => '管理対象外の楽曲', 'track_no' => 2],
            ],
            $tracks->toArray(),
        );
    }
}
