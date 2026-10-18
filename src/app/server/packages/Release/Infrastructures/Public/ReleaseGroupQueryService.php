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
use Release\Application\Public\Query\ReleaseTrackSongItem;
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
        $binGroupIds = array_map(static fn (array $row): string => Row::string($row, 'release_group_id'), $pageRows);
        $releasesByGroup = $this->loadReleases($binGroupIds);
        $tagIdsByGroup = $this->loadTagIds($binGroupIds);

        $releaseGroups = array_map(
            fn (array $row): ReleaseGroupListItem => new ReleaseGroupListItem(
                $this->converter->toUuid(Row::string($row, 'release_group_id')),
                Row::string($row, 'title'),
                ReleaseGroupType::from(Row::int($row, 'type')),
                Row::string($row, 'description'),
                $releasesByGroup[Row::string($row, 'release_group_id')] ?? [],
                $tagIdsByGroup[Row::string($row, 'release_group_id')] ?? [],
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
                ->withSelect(['release_id', 'release_group_id', 'name', 'released_on', 'description'])
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
     * 参照先の楽曲が非公開なら楽曲 ID を伏せる
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
                ->withSelect(['release_id', 'position', 'track_no', 'title'])
                ->from('release_tracks')
                ->where('release_id', 'IN', $binReleaseIds)
                ->orderBy('position')
                ->orderBy('track_no'),
        );

        $trackSongRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect([
                    'release_track_songs.release_id',
                    'release_track_songs.position',
                    'release_track_songs.track_no',
                    'release_track_songs.song_id',
                    'songs.title',
                    'songs.is_display',
                ])
                ->from('release_track_songs')
                ->join('songs', 'songs.song_id = release_track_songs.song_id')
                ->where('release_track_songs.release_id', 'IN', $binReleaseIds)
                ->orderBy('release_track_songs.order_no'),
        );

        $songsByTrack = [];
        foreach ($trackSongRows as $row) {
            $songsByTrack[Row::string($row, 'release_id')][Row::int($row, 'position')][Row::int($row, 'track_no')][] = new ReleaseTrackSongItem(
                Row::bool($row, 'is_display') ? $this->converter->toUuid(Row::string($row, 'song_id')) : null,
                Row::string($row, 'title'),
            );
        }

        $tracksByMedium = [];
        foreach ($trackRows as $row) {
            $binReleaseId = Row::string($row, 'release_id');
            $position = Row::int($row, 'position');
            $trackNo = Row::int($row, 'track_no');
            $tracksByMedium[$binReleaseId][$position][] = new ReleaseTrackItem(
                $trackNo,
                Row::nullableString($row, 'title'),
                $songsByTrack[$binReleaseId][$position][$trackNo] ?? [],
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

    /**
     * @param list<string> $binGroupIds
     *
     * @return array<string, list<string>>
     */
    private function loadTagIds(array $binGroupIds): array
    {
        if ($binGroupIds === []) {
            return [];
        }

        $grouped = [];
        foreach ($this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['release_group_taggings.release_group_id', 'tags.tag_id'])
                ->from('release_group_taggings')
                ->join('tags', 'release_group_taggings.tag_id = tags.tag_id')
                ->where('release_group_taggings.release_group_id', 'IN', $binGroupIds)
                ->orderBy('tags.order_no')
                ->orderBy('tags.tag_id'),
        ) as $row) {
            $grouped[Row::string($row, 'release_group_id')][] = $this->converter->toUuid(Row::string($row, 'tag_id'));
        }

        return $grouped;
    }
}
