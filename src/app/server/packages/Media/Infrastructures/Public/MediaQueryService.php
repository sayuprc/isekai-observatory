<?php

declare(strict_types=1);

namespace Media\Infrastructures\Public;

use DateTimeImmutable;
use Emonkak\Orm\Sql;
use Media\Application\Public\Query\MediaListItem;
use Media\Application\Public\Query\MediaListPage;
use Media\Application\Public\Query\MediaListPageToken;
use Media\Application\Public\Query\MediaQueryServiceInterface;
use Media\Domain\Models\MediaType;
use Override;
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
    public function list(?string $pageToken, int $pageSize): MediaListPage
    {
        $query = $this->queryFactory->select()
            ->withSelect(['media_id', 'title', 'url', 'published_at', 'type'])
            ->from('media')
            ->where('is_display', '=', true);

        if (is_string($pageToken)) {
            $decoded = MediaListPageToken::decode($pageToken);

            $query = $query->where(Sql::format(
                '(title > %s OR (title = %s AND media_id > %s))',
                Sql::value($decoded->title),
                Sql::value($decoded->title),
                Sql::value($this->converter->toBin($decoded->mediaId)),
            ));
        }

        $rows = $this->queryFactory->fetchAll(
            $query->orderBy('title')
                ->orderBy('media_id')
                ->limit($pageSize + 1),
        );

        $hasNextPage = count($rows) > $pageSize;
        $pageRows = $hasNextPage ? array_slice($rows, 0, $pageSize) : $rows;

        $media = array_map(
            fn (array $row): MediaListItem => new MediaListItem(
                $this->converter->toUuid(Row::string($row, 'media_id')),
                Row::string($row, 'title'),
                Row::string($row, 'url'),
                new DateTimeImmutable(Row::string($row, 'published_at')),
                MediaType::from(Row::int($row, 'type')),
            ),
            $pageRows,
        );

        $lastRow = $hasNextPage && $pageRows !== [] ? $pageRows[count($pageRows) - 1] : null;

        return new MediaListPage(
            $media,
            $lastRow === null
                ? null
                : MediaListPageToken::encode(Row::string($lastRow, 'title'), $this->converter->toUuid(Row::string($lastRow, 'media_id'))),
        );
    }
}
