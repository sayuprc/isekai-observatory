<?php

declare(strict_types=1);

namespace Media\Infrastructures\Viewer;

use DateTimeImmutable;
use Emonkak\Orm\Sql;
use Media\Application\Viewer\Query\MediaListCursor;
use Media\Application\Viewer\Query\MediaListItem;
use Media\Application\Viewer\Query\MediaListPage;
use Media\Application\Viewer\Query\MediaQueryServiceInterface;
use Media\Application\Viewer\Query\MediaSongSummary;
use Media\Domain\Models\MediaType;
use Override;
use Song\Domain\Models\SongType;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class MediaQueryService implements MediaQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function list(?string $cursor, int $limit): MediaListPage
    {
        $query = $this->queryFactory->select()
            ->withSelect(['media_id', 'title', 'url', 'published_at', 'type'])
            ->from('media')
            ->where('is_display', '=', true);

        if (is_string($cursor)) {
            $decoded = MediaListCursor::decode($cursor);

            // キーセットページング: (published_at 降順, media_id 昇順) で cursor より後ろを取る
            $query = $query->where(Sql::format(
                '(published_at < %s OR (published_at = %s AND media_id > %s))',
                Sql::value($decoded->publishedAt),
                Sql::value($decoded->publishedAt),
                Sql::value($this->converter->toBin($decoded->mediaId)),
            ));
        }

        $mediaRows = $this->queryFactory->fetchAll(
            $query
                ->orderBy('published_at', 'desc')
                ->orderBy('media_id')
                ->limit($limit + 1),
        );

        $hasNextPage = count($mediaRows) > $limit;
        $pageRows = $hasNextPage ? array_slice($mediaRows, 0, $limit) : $mediaRows;

        $binMediaIds = array_map(static fn (array $row): string => Row::string($row, 'media_id'), $pageRows);

        $songsByMedia = $this->loadSongs($binMediaIds);

        $media = array_map(
            function (array $mediaRow) use ($songsByMedia): MediaListItem {
                $binMediaId = Row::string($mediaRow, 'media_id');
                $songRows = $songsByMedia[$binMediaId] ?? [];

                return new MediaListItem(
                    $this->converter->toUuid($binMediaId),
                    Row::string($mediaRow, 'title'),
                    Row::string($mediaRow, 'url'),
                    new DateTimeImmutable(Row::string($mediaRow, 'published_at')),
                    MediaType::from(Row::int($mediaRow, 'type')),
                    array_map($this->toSongSummary(...), $songRows),
                );
            },
            $pageRows,
        );

        $lastMedia = $hasNextPage && $media !== [] ? $media[count($media) - 1] : null;

        $nextCursor = is_null($lastMedia)
            ? null
            : MediaListCursor::encode($lastMedia->publishedAt->format('Y-m-d H:i:s'), $lastMedia->mediaId);

        return new MediaListPage($media, $nextCursor);
    }

    /**
     * @param list<string> $binMediaIds
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private function loadSongs(array $binMediaIds): array
    {
        if ($binMediaIds === []) {
            return [];
        }

        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['song_media_links.media_id', 'songs.song_id', 'songs.title', 'songs.type'])
                ->from('song_media_links')
                ->join('songs', 'song_media_links.song_id = songs.song_id')
                ->where('song_media_links.media_id', 'IN', $binMediaIds)
                ->where('songs.is_display', '=', true)
                ->orderBy('song_media_links.order_no'),
        );

        $grouped = [];

        foreach ($rows as $row) {
            $grouped[Row::string($row, 'media_id')][] = $row;
        }

        return $grouped;
    }

    /**
     * @param array<string, mixed> $songRow
     */
    private function toSongSummary(array $songRow): MediaSongSummary
    {
        return new MediaSongSummary(
            $this->converter->toUuid(Row::string($songRow, 'song_id')),
            Row::string($songRow, 'title'),
            SongType::from(Row::int($songRow, 'type')),
        );
    }
}
