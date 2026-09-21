<?php

declare(strict_types=1);

namespace Song\Infrastructures\Viewer;

use DateTimeImmutable;
use Emonkak\Orm\Sql;
use Media\Domain\Models\MediaType;
use Override;
use Song\Application\Viewer\Query\SongListCursor;
use Song\Application\Viewer\Query\SongListItem;
use Song\Application\Viewer\Query\SongListPage;
use Song\Application\Viewer\Query\SongMediaSummary;
use Song\Application\Viewer\Query\SongQueryServiceInterface;
use Song\Application\Viewer\Query\SongReleaseGroupSummary;
use Song\Domain\Models\Persons\SongPersonRole;
use Song\Domain\Models\SongType;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class SongQueryService implements SongQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function list(?string $cursor, int $limit): SongListPage
    {
        $query = $this->queryFactory->select()
            ->withSelect(['song_id', 'title', 'description', 'type', 'order_no'])
            ->from('songs')
            ->where('is_display', '=', true);

        if (is_string($cursor)) {
            $decoded = SongListCursor::decode($cursor);

            // キーセットページング: (order_no 降順, song_id 昇順) で cursor より後ろを取る
            $query = $query->where(Sql::format(
                '(order_no < %s OR (order_no = %s AND song_id > %s))',
                Sql::value($decoded->orderNo),
                Sql::value($decoded->orderNo),
                Sql::value($this->converter->toBin($decoded->songId)),
            ));
        }

        $songRows = $this->queryFactory->fetchAll(
            $query->orderBy('order_no', 'desc')
                ->orderBy('song_id')
                ->limit($limit + 1),
        );

        $hasNextPage = count($songRows) > $limit;
        $pageRows = $hasNextPage ? array_slice($songRows, 0, $limit) : $songRows;

        $binSongIds = array_map(static fn (array $row): string => Row::string($row, 'song_id'), $pageRows);

        $personsBySong = $this->loadPersons($binSongIds);
        $mediaBySong = $this->loadMedia($binSongIds);
        $releaseGroupsBySong = $this->loadReleaseGroups($binSongIds);

        $songs = array_map(
            function (array $songRow) use ($personsBySong, $mediaBySong, $releaseGroupsBySong): SongListItem {
                $binSongId = Row::string($songRow, 'song_id');
                $personRows = $personsBySong[$binSongId] ?? [];
                $mediaRows = $mediaBySong[$binSongId] ?? [];

                return new SongListItem(
                    $this->converter->toUuid($binSongId),
                    Row::string($songRow, 'title'),
                    SongType::from(Row::int($songRow, 'type')),
                    Row::string($songRow, 'description'),
                    $this->personNamesByRole($personRows, SongPersonRole::Lyricist),
                    $this->personNamesByRole($personRows, SongPersonRole::Composer),
                    $this->personNamesByRole($personRows, SongPersonRole::Arranger),
                    array_map($this->toMediaSummary(...), $mediaRows),
                    $releaseGroupsBySong[$binSongId] ?? [],
                    Row::int($songRow, 'order_no'),
                );
            },
            $pageRows,
        );

        $lastSongRow = $hasNextPage && $pageRows !== [] ? $pageRows[count($pageRows) - 1] : null;

        $nextCursor = is_null($lastSongRow)
            ? null
            : SongListCursor::encode(
                Row::int($lastSongRow, 'order_no'),
                $this->converter->toUuid(Row::string($lastSongRow, 'song_id')),
            );

        return new SongListPage($songs, $nextCursor);
    }

    /**
     * @param list<string> $binSongIds
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private function loadPersons(array $binSongIds): array
    {
        if ($binSongIds === []) {
            return [];
        }

        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['song_persons.song_id', 'song_persons.role', 'song_persons.order_no', 'persons.name'])
                ->from('song_persons')
                ->join('persons', 'song_persons.person_id = persons.person_id')
                ->where('song_persons.song_id', 'IN', $binSongIds)
                ->orderBy('song_persons.order_no'),
        );

        return $this->groupBySongId($rows);
    }

    /**
     * @param list<string> $binSongIds
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private function loadMedia(array $binSongIds): array
    {
        if ($binSongIds === []) {
            return [];
        }

        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect([
                    'song_media_links.song_id',
                    'song_media_links.order_no',
                    'media.media_id',
                    'media.title',
                    'media.type',
                    'media.url',
                    'media.published_at',
                ])
                ->from('song_media_links')
                ->join('media', 'song_media_links.media_id = media.media_id')
                ->where('song_media_links.song_id', 'IN', $binSongIds)
                ->where('media.is_display', '=', true)
                ->orderBy('song_media_links.order_no'),
        );

        return $this->groupBySongId($rows);
    }

    /**
     * 楽曲 → 公開リリースグループの逆引き。公開リリース経由のもののみ
     *
     * @param list<string> $binSongIds
     *
     * @return array<string, list<SongReleaseGroupSummary>>
     */
    private function loadReleaseGroups(array $binSongIds): array
    {
        if ($binSongIds === []) {
            return [];
        }

        $linkRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect([
                    'release_tracks.song_id',
                    'release_groups.release_group_id',
                    'release_groups.title',
                    'release_groups.type',
                ])
                ->from('release_tracks')
                ->join('releases', 'releases.release_id = release_tracks.release_id')
                ->join('release_groups', 'release_groups.release_group_id = releases.release_group_id')
                ->where('release_tracks.song_id', 'IN', $binSongIds)
                ->where('releases.is_display', '=', true)
                ->where('release_groups.is_display', '=', true),
        );

        if ($linkRows === []) {
            return [];
        }

        $binGroupIds = array_values(array_unique(array_map(
            static fn (array $row): string => Row::string($row, 'release_group_id'),
            $linkRows,
        )));

        // グループごとの最古公開リリースから代表発売日と代表色を引く
        $releaseRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['release_group_id', 'released_on', 'color'])
                ->from('releases')
                ->where('release_group_id', 'IN', $binGroupIds)
                ->where('is_display', '=', true)
                ->orderBy('released_on')
                ->orderBy('name'),
        );

        $firstReleaseByGroup = [];

        foreach ($releaseRows as $row) {
            $binGroupId = Row::string($row, 'release_group_id');
            $firstReleaseByGroup[$binGroupId] ??= $row;
        }

        $grouped = [];
        $seen = [];

        foreach ($linkRows as $row) {
            $binSongId = Row::string($row, 'song_id');
            $binGroupId = Row::string($row, 'release_group_id');

            // 同一グループ内の複数リリースに収録されていても 1 件にまとめる
            if (isset($seen[$binSongId][$binGroupId])) {
                continue;
            }

            $firstRelease = $firstReleaseByGroup[$binGroupId] ?? null;

            if (is_null($firstRelease)) {
                continue;
            }

            $seen[$binSongId][$binGroupId] = true;
            $grouped[$binSongId][] = new SongReleaseGroupSummary(
                $this->converter->toUuid($binGroupId),
                Row::string($row, 'title'),
                Row::int($row, 'type'),
                Row::string($firstRelease, 'released_on'),
                Row::string($firstRelease, 'color'),
            );
        }

        // 最古発売日の降順で並べる
        foreach ($grouped as &$summaries) {
            usort(
                $summaries,
                static fn (SongReleaseGroupSummary $a, SongReleaseGroupSummary $b): int => [$b->firstReleasedOn, $a->title] <=> [$a->firstReleasedOn, $b->title],
            );
        }

        return $grouped;
    }

    /**
     * @param list<array<string, mixed>> $rows
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private function groupBySongId(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $grouped[Row::string($row, 'song_id')][] = $row;
        }

        return $grouped;
    }

    /**
     * @param list<array<string, mixed>> $personRows
     *
     * @return list<string>
     */
    private function personNamesByRole(array $personRows, SongPersonRole $role): array
    {
        $names = [];

        foreach ($personRows as $row) {
            if (Row::int($row, 'role') === $role->value) {
                $names[] = Row::string($row, 'name');
            }
        }

        return $names;
    }

    /**
     * @param array<string, mixed> $mediaRow
     */
    private function toMediaSummary(array $mediaRow): SongMediaSummary
    {
        return new SongMediaSummary(
            $this->converter->toUuid(Row::string($mediaRow, 'media_id')),
            Row::string($mediaRow, 'title'),
            MediaType::from(Row::int($mediaRow, 'type')),
            Row::string($mediaRow, 'url'),
            new DateTimeImmutable(Row::string($mediaRow, 'published_at')),
        );
    }
}
