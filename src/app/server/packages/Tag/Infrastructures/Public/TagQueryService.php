<?php

declare(strict_types=1);

namespace Tag\Infrastructures\Public;

use Emonkak\Orm\Sql;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;
use Tag\Application\Public\Query\TagListItem;
use Tag\Application\Public\Query\TagListPage;
use Tag\Application\Public\Query\TagListPageToken;
use Tag\Application\Public\Query\TagQueryServiceInterface;

readonly class TagQueryService implements TagQueryServiceInterface
{
    /**
     * 公開記録に付いているタグ ID
     *
     * リリースグループは公開 API に載るもの (公開リリースを 1 件以上持つもの) に合わせる
     */
    private const string PUBLIC_TAG_IDS = <<<'SQL'
        SELECT song_taggings.tag_id
        FROM song_taggings
        INNER JOIN songs ON songs.song_id = song_taggings.song_id
        WHERE songs.is_display = TRUE
        UNION
        SELECT event_taggings.tag_id
        FROM event_taggings
        INNER JOIN events ON events.event_id = event_taggings.event_id
        WHERE events.is_display = TRUE
        UNION
        SELECT release_group_taggings.tag_id
        FROM release_group_taggings
        INNER JOIN release_groups ON release_groups.release_group_id = release_group_taggings.release_group_id
        WHERE release_groups.is_display = TRUE
          AND release_groups.release_group_id IN (SELECT release_group_id FROM releases WHERE is_display = TRUE)
        UNION
        SELECT media_taggings.tag_id
        FROM media_taggings
        INNER JOIN media ON media.media_id = media_taggings.media_id
        WHERE media.is_display = TRUE
        SQL;

    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function list(?string $pageToken, int $pageSize): TagListPage
    {
        $query = $this->queryFactory->select()
            ->withSelect(['tag_id', 'name', 'order_no'])
            ->from('tags')
            ->where(new Sql('tag_id IN (' . self::PUBLIC_TAG_IDS . ')'));

        if (is_string($pageToken)) {
            $decoded = TagListPageToken::decode($pageToken);

            $query = $query->where(Sql::format(
                '(order_no > %s OR (order_no = %s AND tag_id > %s))',
                Sql::value($decoded->orderNo),
                Sql::value($decoded->orderNo),
                Sql::value($this->converter->toBin($decoded->tagId)),
            ));
        }

        $rows = $this->queryFactory->fetchAll(
            $query->orderBy('order_no')
                ->orderBy('tag_id')
                ->limit($pageSize + 1),
        );

        $hasNextPage = count($rows) > $pageSize;
        $pageRows = $hasNextPage ? array_slice($rows, 0, $pageSize) : $rows;

        $tags = array_map(
            fn (array $row): TagListItem => new TagListItem(
                $this->converter->toUuid(Row::string($row, 'tag_id')),
                Row::string($row, 'name'),
            ),
            $pageRows,
        );

        $lastRow = $hasNextPage && $pageRows !== [] ? $pageRows[count($pageRows) - 1] : null;

        return new TagListPage(
            $tags,
            $lastRow === null
                ? null
                : TagListPageToken::encode(
                    Row::int($lastRow, 'order_no'),
                    $this->converter->toUuid(Row::string($lastRow, 'tag_id')),
                ),
        );
    }
}
