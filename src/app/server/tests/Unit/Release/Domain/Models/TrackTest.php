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

    private const string OTHER_SONG_ID = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';

    #[Test]
    public function canCreateReferenceTrack(): void
    {
        $track = Track::create(
            [new SongId(self::SONG_ID)],
            null,
            new OrderNo(1),
        );
        $this->assertNull($track->title);
        $this->assertSame(['song_ids' => [self::SONG_ID], 'title' => null, 'track_no' => 1], $track->toArray());
    }

    #[Test]
    public function canCreateTitleOnlyTrack(): void
    {
        $track = Track::create(
            [],
            new TrackTitle('管理対象外の楽曲'),
            new OrderNo(1),
        );
        $this->assertSame([], $track->songIds);
        $this->assertSame(['song_ids' => [], 'title' => '管理対象外の楽曲', 'track_no' => 1], $track->toArray());
    }

    #[Test]
    public function canCreateReferenceTrackWithOverriddenTitle(): void
    {
        $track = Track::create(
            [new SongId(self::SONG_ID)],
            new TrackTitle('楽曲A -instrumental-'),
            new OrderNo(1),
        );
        $this->assertSame(
            ['song_ids' => [self::SONG_ID], 'title' => '楽曲A -instrumental-', 'track_no' => 1],
            $track->toArray(),
        );
    }

    #[Test]
    public function canCreateMedleyTrackInOrder(): void
    {
        $track = Track::create(
            [new SongId(self::OTHER_SONG_ID), new SongId(self::SONG_ID)],
            new TrackTitle('メドレー'),
            new OrderNo(1),
        );
        $this->assertSame(
            ['song_ids' => [self::OTHER_SONG_ID, self::SONG_ID], 'title' => 'メドレー', 'track_no' => 1],
            $track->toArray(),
        );
    }

    #[Test]
    public function canCreateMedleyTrackWithoutTitle(): void
    {
        $track = Track::create(
            [new SongId(self::SONG_ID), new SongId(self::OTHER_SONG_ID)],
            null,
            new OrderNo(1),
        );
        $this->assertNull($track->title);
        $this->assertCount(2, $track->songIds);
    }

    #[Test]
    public function cannotCreateWithNeitherSongsNorTitle(): void
    {
        $this->expectException(BusinessRuleViolationException::class);
        $this->expectExceptionMessage('収録曲には楽曲かタイトルの少なくとも一方を指定してください。');

        Track::create([], null, new OrderNo(1));
    }

    #[Test]
    public function cannotCreateWithDuplicatedSongs(): void
    {
        $this->expectException(BusinessRuleViolationException::class);
        $this->expectExceptionMessage('1 つの収録曲に同じ楽曲を複数指定することはできません。');

        Track::create(
            [new SongId(self::SONG_ID), new SongId(self::SONG_ID)],
            new TrackTitle('メドレー'),
            new OrderNo(1),
        );
    }

    #[Test]
    public function canReconstructReferenceTrack(): void
    {
        $track = Track::reconstruct([self::SONG_ID], null, 1);

        $this->assertSame(
            [self::SONG_ID],
            array_map(static fn (SongId $songId): string => $songId->value, $track->songIds),
        );
        $this->assertNull($track->title);
        $this->assertSame(1, $track->trackNo->value);
    }

    #[Test]
    public function canReconstructTitleOnlyTrack(): void
    {
        $track = Track::reconstruct([], '管理対象外の楽曲', 1);

        $this->assertSame([], $track->songIds);
        $this->assertSame('管理対象外の楽曲', $track->title?->value);
        $this->assertSame(1, $track->trackNo->value);
    }

    #[Test]
    public function canReconstructMedleyTrack(): void
    {
        $track = Track::reconstruct([self::OTHER_SONG_ID, self::SONG_ID], 'メドレー', 1);

        $this->assertSame(
            [self::OTHER_SONG_ID, self::SONG_ID],
            array_map(static fn (SongId $songId): string => $songId->value, $track->songIds),
        );
        $this->assertSame('メドレー', $track->title?->value);
    }
}
