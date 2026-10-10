<?php

declare(strict_types=1);

namespace Song\Infrastructures\Public;

use Emonkak\Orm\Sql;
use Override;
use Song\Application\Public\Query\SongCredit;
use Song\Application\Public\Query\SongListItem;
use Song\Application\Public\Query\SongListPage;
use Song\Application\Public\Query\SongListPageToken;
use Song\Application\Public\Query\SongQueryServiceInterface;
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
    public function list(?string $pageToken, int $pageSize): SongListPage
    {
        $query = $this->queryFactory->select()
            ->withSelect(['song_id', 'title', 'description', 'type', 'order_no'])
            ->from('songs')
            ->where('is_display', '=', true);

        if (is_string($pageToken)) {
            $decoded = SongListPageToken::decode($pageToken);

            // 降順と昇順が混在し行値比較では表せないため、条件を展開して書く
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
                ->limit($pageSize + 1),
        );

        $hasNextPage = count($songRows) > $pageSize;
        $pageRows = $hasNextPage ? array_slice($songRows, 0, $pageSize) : $songRows;
        $binSongIds = array_map(static fn (array $row): string => Row::string($row, 'song_id'), $pageRows);

        $creditsBySong = $this->loadCredits($binSongIds);
        $mediaIdsBySong = $this->loadMediaIds($binSongIds);

        $songs = array_map(
            function (array $row) use ($creditsBySong, $mediaIdsBySong): SongListItem {
                $binSongId = Row::string($row, 'song_id');

                return new SongListItem(
                    $this->converter->toUuid($binSongId),
                    Row::string($row, 'title'),
                    Row::string($row, 'description'),
                    SongType::from(Row::int($row, 'type')),
                    $creditsBySong[$binSongId] ?? [],
                    $mediaIdsBySong[$binSongId] ?? [],
                );
            },
            $pageRows,
        );

        $lastRow = $hasNextPage && $pageRows !== [] ? $pageRows[count($pageRows) - 1] : null;

        return new SongListPage(
            $songs,
            $lastRow === null
                ? null
                : SongListPageToken::encode(Row::int($lastRow, 'order_no'), $this->converter->toUuid(Row::string($lastRow, 'song_id'))),
        );
    }

    /**
     * @param list<string> $binSongIds
     *
     * @return array<string, list<SongCredit>>
     */
    private function loadCredits(array $binSongIds): array
    {
        if ($binSongIds === []) {
            return [];
        }

        $grouped = [];
        foreach ($this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['song_id', 'person_id', 'role'])
                ->from('song_persons')
                ->where('song_id', 'IN', $binSongIds)
                ->orderBy('order_no'),
        ) as $row) {
            $grouped[Row::string($row, 'song_id')][] = new SongCredit(
                $this->converter->toUuid(Row::string($row, 'person_id')),
                SongPersonRole::from(Row::int($row, 'role')),
            );
        }

        return $grouped;
    }

    /**
     * @param list<string> $binSongIds
     *
     * @return array<string, list<string>>
     */
    private function loadMediaIds(array $binSongIds): array
    {
        if ($binSongIds === []) {
            return [];
        }

        $grouped = [];
        foreach ($this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['song_media_links.song_id', 'media.media_id'])
                ->from('song_media_links')
                ->join('media', 'song_media_links.media_id = media.media_id')
                ->where('song_media_links.song_id', 'IN', $binSongIds)
                ->where('media.is_display', '=', true)
                ->orderBy('song_media_links.order_no'),
        ) as $row) {
            $grouped[Row::string($row, 'song_id')][] = $this->converter->toUuid(Row::string($row, 'media_id'));
        }

        return $grouped;
    }
}
