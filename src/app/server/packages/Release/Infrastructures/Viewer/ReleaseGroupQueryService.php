<?php

declare(strict_types=1);

namespace Release\Infrastructures\Viewer;

use Emonkak\Orm\Sql;
use Override;
use Release\Application\Viewer\Query\ReleaseGroupListCursor;
use Release\Application\Viewer\Query\ReleaseGroupListItem;
use Release\Application\Viewer\Query\ReleaseGroupListPage;
use Release\Application\Viewer\Query\ReleaseGroupQueryServiceInterface;
use Release\Application\Viewer\Query\ReleaseListItem;
use Release\Application\Viewer\Query\ReleaseMediumItem;
use Release\Application\Viewer\Query\ReleaseTrackItem;
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

    #[Override]
    public function list(?string $cursor, int $limit): ReleaseGroupListPage
    {
        // 公開リリースを 1 件以上持つ公開グループのみを対象にする
        $query = $this->queryFactory->select()
            ->withSelect([
                'release_groups.release_group_id',
                'release_groups.title',
                'release_groups.type',
                'release_groups.description',
                'release_groups.order_no',
            ])
            ->select(new Sql('MIN(releases.released_on)'), 'first_released_on')
            ->from('release_groups')
            ->join('releases', 'releases.release_group_id = release_groups.release_group_id')
            ->where('release_groups.is_display', '=', true)
            ->where('releases.is_display', '=', true)
            ->groupBy('release_groups.release_group_id')
            ->groupBy('release_groups.title')
            ->groupBy('release_groups.type')
            ->groupBy('release_groups.description')
            ->groupBy('release_groups.order_no');

        if (is_string($cursor)) {
            $decoded = ReleaseGroupListCursor::decode($cursor);

            // キーセットページング: (first_released_on DESC, order_no DESC, release_group_id ASC) で cursor より後ろを取る
            $query = $query->having(Sql::format(
                '(MIN(releases.released_on) < %s OR (MIN(releases.released_on) = %s AND (release_groups.order_no < %s OR (release_groups.order_no = %s AND release_groups.release_group_id > %s))))',
                Sql::value($decoded->firstReleasedOn),
                Sql::value($decoded->firstReleasedOn),
                Sql::value($decoded->orderNo),
                Sql::value($decoded->orderNo),
                Sql::value($this->converter->toBin($decoded->releaseGroupId)),
            ));
        }

        $groupRows = $this->queryFactory->fetchAll(
            $query
                ->orderBy('first_released_on', 'desc')
                ->orderBy('release_groups.order_no', 'desc')
                ->orderBy('release_groups.release_group_id')
                ->limit($limit + 1),
        );

        $hasNextPage = count($groupRows) > $limit;
        $pageRows = $hasNextPage ? array_slice($groupRows, 0, $limit) : $groupRows;

        $binGroupIds = array_map(static fn (array $row): string => Row::string($row, 'release_group_id'), $pageRows);
        $releasesByGroup = $this->loadReleases($binGroupIds);

        $releaseGroups = array_map(
            fn (array $row): ReleaseGroupListItem => new ReleaseGroupListItem(
                $this->converter->toUuid(Row::string($row, 'release_group_id')),
                Row::string($row, 'title'),
                ReleaseGroupType::from(Row::int($row, 'type')),
                Row::string($row, 'description'),
                Row::string($row, 'first_released_on'),
                $releasesByGroup[Row::string($row, 'release_group_id')] ?? [],
            ),
            $pageRows,
        );

        $lastRow = $hasNextPage && $pageRows !== [] ? $pageRows[count($pageRows) - 1] : null;

        $nextCursor = is_null($lastRow)
            ? null
            : ReleaseGroupListCursor::encode(
                Row::int($lastRow, 'order_no'),
                Row::string($lastRow, 'first_released_on'),
                $this->converter->toUuid(Row::string($lastRow, 'release_group_id')),
            );

        return new ReleaseGroupListPage($releaseGroups, $nextCursor);
    }

    /**
     * @param list<string> $binGroupIds
     *
     * @return array<string, list<ReleaseListItem>>
     */
    private function loadReleases(array $binGroupIds): array
    {
        if ($binGroupIds === []) {
            return [];
        }

        $releaseRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['release_id', 'release_group_id', 'name', 'released_on', 'description', 'color', 'order_no'])
                ->from('releases')
                ->where('release_group_id', 'IN', $binGroupIds)
                ->where('is_display', '=', true)
                ->orderBy('released_on')
                ->orderBy('order_no')
                ->orderBy('name'),
        );

        $binReleaseIds = array_map(static fn (array $row): string => Row::string($row, 'release_id'), $releaseRows);
        $formatsByRelease = $this->loadFormats($binReleaseIds);
        $mediaByRelease = $this->loadMedia($binReleaseIds);

        $grouped = [];

        foreach ($releaseRows as $row) {
            $binReleaseId = Row::string($row, 'release_id');

            $grouped[Row::string($row, 'release_group_id')][] = new ReleaseListItem(
                $this->converter->toUuid($binReleaseId),
                Row::string($row, 'name'),
                Row::string($row, 'released_on'),
                Row::string($row, 'description'),
                Row::string($row, 'color'),
                Row::int($row, 'order_no'),
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

        $formatRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['release_id', 'format'])
                ->from('release_formats')
                ->where('release_id', 'IN', $binReleaseIds)
                ->orderBy('format'),
        );

        $grouped = [];

        foreach ($formatRows as $row) {
            $grouped[Row::string($row, 'release_id')][] = ReleaseFormat::from(Row::int($row, 'format'));
        }

        return $grouped;
    }

    /**
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
                ->withSelect([
                    'release_tracks.release_id',
                    'release_tracks.position',
                    'release_tracks.track_no',
                    'release_tracks.song_id',
                    'songs.is_display',
                ])
                // 上書き名 (release_tracks.title) を優先し、なければ楽曲名で表示する
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

            // タイトルのみトラックは songs が結合されないため isDisplay: false(リンクなし表示)とする
            $tracksByMedium[Row::string($row, 'release_id')][Row::int($row, 'position')][] = new ReleaseTrackItem(
                Row::int($row, 'track_no'),
                is_null($binSongId) ? null : $this->converter->toUuid($binSongId),
                Row::string($row, 'title'),
                is_null($binSongId) ? false : Row::bool($row, 'is_display'),
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
