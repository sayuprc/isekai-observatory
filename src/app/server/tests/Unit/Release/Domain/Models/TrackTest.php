<?php

declare(strict_types=1);

namespace Tests\Unit\Release\Domain\Models;

use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\Track;
use Release\Domain\Models\TrackTitle;
use Song\Domain\Models\SongId;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class TrackTest extends TestCase
{
    private const string SONG_ID = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

    #[Test]
    public function canCreateReferenceTrack(): void
    {
        $track = Track::create(
            new SongId(self::SONG_ID),
            null,
            new OrderNo(1),
        );
        $this->assertSame(self::SONG_ID, $track->songId?->value);
        $this->assertNull($track->title);
        $this->assertSame(['song_id' => self::SONG_ID, 'title' => null, 'track_no' => 1], $track->toArray());
    }

    #[Test]
    public function canCreateTitleOnlyTrack(): void
    {
        $track = Track::create(
            null,
            new TrackTitle('管理対象外の楽曲'),
            new OrderNo(1),
        );
        $this->assertNull($track->songId);
        $this->assertSame('管理対象外の楽曲', $track->title?->value);
        $this->assertSame(['song_id' => null, 'title' => '管理対象外の楽曲', 'track_no' => 1], $track->toArray());
    }

    #[Test]
    public function canCreateReferenceTrackWithOverriddenTitle(): void
    {
        $track = Track::create(
            new SongId(self::SONG_ID),
            new TrackTitle('楽曲A -instrumental-'),
            new OrderNo(1),
        );
        $this->assertSame(self::SONG_ID, $track->songId?->value);
        $this->assertSame('楽曲A -instrumental-', $track->title?->value);
        $this->assertSame(['song_id' => self::SONG_ID, 'title' => '楽曲A -instrumental-', 'track_no' => 1], $track->toArray());
    }

    #[Test]
    public function cannotCreateWithNeitherSongIdNorTitle(): void
    {
        $this->expectException(BusinessRuleViolationException::class);

        $track = Track::create(null, null, new OrderNo(1));
    }

    #[Test]
    public function canReconstructReferenceTrack(): void
    {
        $track = Track::reconstruct(self::SONG_ID, null, 1);

        $this->assertSame(self::SONG_ID, $track->songId?->value);
        $this->assertNull($track->title);
        $this->assertSame(1, $track->trackNo->value);
    }

    #[Test]
    public function canReconstructTitleOnlyTrack(): void
    {
        $track = Track::reconstruct(null, '管理対象外の楽曲', 1);

        $this->assertNull($track->songId);
        $this->assertSame('管理対象外の楽曲', $track->title?->value);
        $this->assertSame(1, $track->trackNo->value);
    }

    #[Test]
    public function canReconstructReferenceTrackWithOverriddenTitle(): void
    {
        $track = Track::reconstruct(self::SONG_ID, '楽曲A -instrumental-', 1);

        $this->assertSame(self::SONG_ID, $track->songId?->value);
        $this->assertSame('楽曲A -instrumental-', $track->title?->value);
        $this->assertSame(1, $track->trackNo->value);
    }
}
