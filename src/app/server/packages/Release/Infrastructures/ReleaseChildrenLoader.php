<?php

declare(strict_types=1);

namespace Release\Infrastructures;

use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

/**
 * Release 集約の子テーブルを、複数リリース分まとめて読み込む
 *
 * どのメソッドもバイナリのリリース ID をキーにして返す。子を持たないリリースはキーに含まれない
 */
readonly class ReleaseChildrenLoader
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    /**
     * @param list<string> $binReleaseIds
     *
     * @return array<string, list<int>>
     */
    public function loadFormats(array $binReleaseIds): array
    {
        $formatRows = $this->queryFactory
            ->select()
            ->withSelect(['release_id', 'format'])
            ->from('release_formats')
            ->where('release_id', 'IN', $binReleaseIds)
            ->orderBy('format')
            |> $this->queryFactory->fetchAll(...);

        $formatsByRelease = [];

        foreach ($formatRows as $formatRow) {
            $formatsByRelease[Row::string($formatRow, 'release_id')][] = Row::int($formatRow, 'format');
        }

        return $formatsByRelease;
    }

    /**
     * @param list<string> $binReleaseIds
     *
     * @return array<string, list<array{position: int, name: ?string, tracks: list<array{songIds: list<string>, title: ?string, trackNo: int}>}>>
     */
    public function loadMedia(array $binReleaseIds): array
    {
        $mediumRows = $this->queryFactory
            ->select()
            ->withSelect(['release_id', 'position', 'name'])
            ->from('release_media')
            ->where('release_id', 'IN', $binReleaseIds)
            ->orderBy('position')
            |> $this->queryFactory->fetchAll(...);

        $trackRows = $this->queryFactory
            ->select()
            ->withSelect(['release_id', 'position', 'track_no', 'title'])
            ->from('release_tracks')
            ->where('release_id', 'IN', $binReleaseIds)
            ->orderBy('position')
            ->orderBy('track_no')
            |> $this->queryFactory->fetchAll(...);

        $trackSongRows = $this->queryFactory
            ->select()
            ->withSelect(['release_id', 'position', 'track_no', 'song_id'])
            ->from('release_track_songs')
            ->where('release_id', 'IN', $binReleaseIds)
            ->orderBy('order_no')
            |> $this->queryFactory->fetchAll(...);

        $songIdsByTrack = [];

        foreach ($trackSongRows as $trackSongRow) {
            $binReleaseId = Row::string($trackSongRow, 'release_id');
            $position = Row::int($trackSongRow, 'position');
            $trackNo = Row::int($trackSongRow, 'track_no');
            $songIdsByTrack[$binReleaseId][$position][$trackNo][] = $this->converter->toUuid(
                Row::string($trackSongRow, 'song_id'),
            );
        }

        $tracksByPosition = [];

        foreach ($trackRows as $trackRow) {
            $binReleaseId = Row::string($trackRow, 'release_id');
            $position = Row::int($trackRow, 'position');
            $trackNo = Row::int($trackRow, 'track_no');

            $tracksByPosition[$binReleaseId][$position][] = [
                'songIds' => $songIdsByTrack[$binReleaseId][$position][$trackNo] ?? [],
                'title' => Row::nullableString($trackRow, 'title'),
                'trackNo' => $trackNo,
            ];
        }

        $mediaByRelease = [];

        foreach ($mediumRows as $mediumRow) {
            $binReleaseId = Row::string($mediumRow, 'release_id');
            $position = Row::int($mediumRow, 'position');

            $mediaByRelease[$binReleaseId][] = [
                'position' => $position,
                'name' => Row::nullableString($mediumRow, 'name'),
                'tracks' => $tracksByPosition[$binReleaseId][$position] ?? [],
            ];
        }

        return $mediaByRelease;
    }
}
