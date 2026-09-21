<?php

declare(strict_types=1);

namespace Release\Infrastructures\Admin;

use Emonkak\Orm\Sql;
use Override;
use Release\Application\Admin\Query\ReleaseDetailQueryServiceInterface;
use Release\Application\Admin\Query\ReleaseReferencedSong;
use Release\Domain\Models\ReleaseId;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class ReleaseDetailQueryService implements ReleaseDetailQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function findReferencedSongs(ReleaseId $releaseId): array
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect([
                    'release_tracks.position',
                    'release_tracks.track_no',
                    'release_tracks.song_id',
                ])
                // 参照トラックは楽曲の正式名を返す (上書き名は Release 集約の tracks 側で扱う)
                ->select(new Sql('COALESCE(songs.title, release_tracks.title)'), 'title')
                ->from('release_tracks')
                ->outerJoin('songs', 'release_tracks.song_id = songs.song_id')
                ->where('release_tracks.release_id', '=', $this->converter->toBin($releaseId->value))
                ->orderBy('release_tracks.position')
                ->orderBy('release_tracks.track_no'),
        );

        return array_map(
            function (array $row): ReleaseReferencedSong {
                $binSongId = Row::nullableString($row, 'song_id');

                return new ReleaseReferencedSong(
                    Row::int($row, 'position'),
                    Row::int($row, 'track_no'),
                    is_null($binSongId) ? null : $this->converter->toUuid($binSongId),
                    Row::string($row, 'title'),
                );
            },
            $rows,
        );
    }
}
