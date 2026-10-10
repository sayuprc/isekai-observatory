<?php

declare(strict_types=1);

namespace Release\Infrastructures\Public;

use Emonkak\Orm\Sql;
use Override;
use Release\Application\Public\Query\ReleaseGroupListItem;
use Release\Application\Public\Query\ReleaseGroupListPage;
use Release\Application\Public\Query\ReleaseGroupListPageToken;
use Release\Application\Public\Query\ReleaseGroupQueryServiceInterface;
use Release\Application\Public\Query\ReleaseItem;
use Release\Application\Public\Query\ReleaseMediumItem;
use Release\Application\Public\Query\ReleaseTrackItem;
use Release\Domain\Models\ReleaseFormat;
use Release\Domain\Models\ReleaseGroupType;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class ReleaseGroupQueryService implements ReleaseGroupQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    /**
     * 公開リリースを 1 件以上持つ公開リリースグループだけを返す
     */
    #[Override]
    public function list(?string $pageToken, int $pageSize): ReleaseGroupListPage
    {
        $query = $this->queryFactory->select()
            ->withSelect(['release_group_id', 'title', 'type', 'description', 'order_no'])
            ->from('release_groups')
            ->where('is_display', '=', true)
            ->where(new Sql('release_group_id IN (SELECT release_group_id FROM releases WHERE is_display = TRUE)'));

        if (is_string($pageToken)) {
            $decoded = ReleaseGroupListPageToken::decode($pageToken);

            // 降順と昇順が混在し行値比較では表せないため、条件を展開して書く
            $query = $query->where(Sql::format(
                '(order_no < %s OR (order_no = %s AND release_group_id > %s))',
                Sql::value($decoded->orderNo),
                Sql::value($decoded->orderNo),
                Sql::value($this->converter->toBin($decoded->releaseGroupId)),
            ));
        }

        $rows = $this->queryFactory->fetchAll(
            $query->orderBy('order_no', 'desc')
                ->orderBy('release_group_id')
                ->limit($pageSize + 1),
        );

        $hasNextPage = count($rows) > $pageSize;
        $pageRows = $hasNextPage ? array_slice($rows, 0, $pageSize) : $rows;
        $releasesByGroup = $this->loadReleases(array_map(static fn (array $row): string => Row::string($row, 'release_group_id'), $pageRows));

        $releaseGroups = array_map(
            fn (array $row): ReleaseGroupListItem => new ReleaseGroupListItem(
                $this->converter->toUuid(Row::string($row, 'release_group_id')),
                Row::string($row, 'title'),
                ReleaseGroupType::from(Row::int($row, 'type')),
                Row::string($row, 'description'),
                $releasesByGroup[Row::string($row, 'release_group_id')] ?? [],
            ),
            $pageRows,
        );

        $lastRow = $hasNextPage && $pageRows !== [] ? $pageRows[count($pageRows) - 1] : null;

        return new ReleaseGroupListPage(
            $releaseGroups,
            $lastRow === null
                ? null
                : ReleaseGroupListPageToken::encode(Row::int($lastRow, 'order_no'), $this->converter->toUuid(Row::string($lastRow, 'release_group_id'))),
        );
    }

    /**
     * @param list<string> $binGroupIds
     *
     * @return array<string, list<ReleaseItem>>
     */
    private function loadReleases(array $binGroupIds): array
    {
        if ($binGroupIds === []) {
            return [];
        }

        $releaseRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['release_id', 'release_group_id', 'name', 'released_on', 'description', 'color'])
                ->from('releases')
                ->where('release_group_id', 'IN', $binGroupIds)
                ->where('is_display', '=', true)
                ->orderBy('order_no')
                ->orderBy('release_id'),
        );

        $binReleaseIds = array_map(static fn (array $row): string => Row::string($row, 'release_id'), $releaseRows);
        $formatsByRelease = $this->loadFormats($binReleaseIds);
        $mediaByRelease = $this->loadMedia($binReleaseIds);

        $grouped = [];
        foreach ($releaseRows as $row) {
            $binReleaseId = Row::string($row, 'release_id');
            $grouped[Row::string($row, 'release_group_id')][] = new ReleaseItem(
                $this->converter->toUuid($binReleaseId),
                Row::string($row, 'name'),
                Row::string($row, 'released_on'),
                Row::string($row, 'description'),
                Row::string($row, 'color'),
                $formatsByRelease[$binReleaseId] ?? [],
                $mediaByRelease[$binReleaseId] ?? [],
            );
        }

        return $grouped;
    }

    /**
     * @param list<string> $binReleaseIds
     *
     * @return array<string, list<ReleaseFormat>>
     */
    private function loadFormats(array $binReleaseIds): array
    {
        if ($binReleaseIds === []) {
            return [];
        }

        $grouped = [];
        foreach ($this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['release_id', 'format'])
                ->from('release_formats')
                ->where('release_id', 'IN', $binReleaseIds)
                ->orderBy('format'),
        ) as $row) {
            $grouped[Row::string($row, 'release_id')][] = ReleaseFormat::from(Row::int($row, 'format'));
        }

        return $grouped;
    }

    /**
     * 楽曲を参照しないトラックと、参照先の楽曲が非公開のトラックは楽曲 ID を伏せる
     *
     * @param list<string> $binReleaseIds
     *
     * @return array<string, list<ReleaseMediumItem>>
     */
    private function loadMedia(array $binReleaseIds): array
    {
        if ($binReleaseIds === []) {
            return [];
        }

        $mediumRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['release_id', 'position', 'name'])
                ->from('release_media')
                ->where('release_id', 'IN', $binReleaseIds)
                ->orderBy('position'),
        );

        $trackRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['release_tracks.release_id', 'release_tracks.position', 'release_tracks.track_no', 'release_tracks.song_id', 'songs.is_display'])
                ->select(new Sql('COALESCE(release_tracks.title, songs.title)'), 'title')
                ->from('release_tracks')
                ->outerJoin('songs', 'songs.song_id = release_tracks.song_id')
                ->where('release_tracks.release_id', 'IN', $binReleaseIds)
                ->orderBy('release_tracks.position')
                ->orderBy('release_tracks.track_no'),
        );

        $tracksByMedium = [];
        foreach ($trackRows as $row) {
            $binSongId = Row::nullableString($row, 'song_id');
            $tracksByMedium[Row::string($row, 'release_id')][Row::int($row, 'position')][] = new ReleaseTrackItem(
                Row::int($row, 'track_no'),
                $binSongId !== null && Row::bool($row, 'is_display') ? $this->converter->toUuid($binSongId) : null,
                Row::string($row, 'title'),
            );
        }

        $grouped = [];
        foreach ($mediumRows as $row) {
            $binReleaseId = Row::string($row, 'release_id');
            $position = Row::int($row, 'position');
            $grouped[$binReleaseId][] = new ReleaseMediumItem(
                $position,
                Row::nullableString($row, 'name'),
                $tracksByMedium[$binReleaseId][$position] ?? [],
            );
        }

        return $grouped;
    }
}
