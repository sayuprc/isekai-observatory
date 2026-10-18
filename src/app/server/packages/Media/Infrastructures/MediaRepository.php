<?php

declare(strict_types=1);

namespace Media\Infrastructures;

use DateTimeImmutable;
use Emonkak\Orm\SelectBuilder;
use Media\Domain\Criteria\MediaSearchCriteria;
use Media\Domain\Models\Media;
use Media\Domain\Models\MediaId;
use Media\Domain\Models\MediaRepositoryInterface;
use Media\Domain\Models\MediaUrl;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;
use Support\Infrastructures\Database\SqlHelper;

readonly class MediaRepository implements MediaRepositoryInterface
{
    private const string TABLE = 'media';

    /** @var array<int, string> */
    private const array COLUMNS = [
        'media_id',
        'title',
        'url',
        'published_at',
        'type',
        'is_display',
    ];

    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function find(MediaId $mediaId): ?Media
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('media_id', '=', $this->converter->toBin($mediaId->value))
                ->limit(1),
        );

        return $this->hydrateAll($rows)[0] ?? null;
    }

    #[Override]
    public function findByUrl(MediaUrl $url): ?Media
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('url', '=', $url->value)
                ->limit(1),
        );

        return $this->hydrateAll($rows)[0] ?? null;
    }

    #[Override]
    public function isUsed(MediaId $mediaId): bool
    {
        $binMediaId = $this->converter->toBin($mediaId->value);
        $count = Row::intValue(
            $this->queryFactory->select()
                ->from('song_media_links')
                ->where('media_id', '=', $binMediaId)
                ->aggregate($this->queryFactory->pdo(), 'COUNT(*)'),
        );
        $eventCount = Row::intValue(
            $this->queryFactory->select()
                ->from('event_media')
                ->where('media_id', '=', $binMediaId)
                ->aggregate($this->queryFactory->pdo(), 'COUNT(*)'),
        );

        return $count > 0 || $eventCount > 0;
    }

    #[Override]
    public function search(MediaSearchCriteria $criteria): array
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->paginate(
                $this->buildSearchQuery($criteria)
                    ->withSelect(self::COLUMNS)
                    ->orderBy($criteria->sort->value, $criteria->order->value),
                $criteria->page,
                $criteria->perPage,
            ),
        );

        return $this->hydrateAll($rows);
    }

    #[Override]
    public function maxPage(MediaSearchCriteria $criteria): int
    {
        return $this->queryFactory->maxPage($this->buildSearchQuery($criteria), $criteria->perPage);
    }

    #[Override]
    public function findByIds(MediaId ...$mediaIds): array
    {
        if ($mediaIds === []) {
            return [];
        }

        $binIds = array_map(fn (MediaId $mediaId): string => $this->converter->toBin($mediaId->value), $mediaIds);

        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('media_id', 'IN', $binIds),
        );

        return $this->hydrateAll($rows);
    }

    #[Override]
    public function save(Media $media): Media
    {
        $data = $media->toArray();
        $now = now()->toDateTimeString();
        $binMediaId = $this->converter->toBin($media->mediaId->value);

        $this->queryFactory->deleteFromTables(['media_taggings'], 'media_id', $binMediaId);

        $this->queryFactory->insert()
            ->into(self::TABLE, [
                'media_id',
                'title',
                'url',
                'published_at',
                'type',
                'is_display',
                'created_at',
                'updated_at',
            ])
            ->values([
                $binMediaId,
                $data['title'],
                $data['url'],
                $data['published_at'],
                $data['type'],
                $data['is_display'],
                $now,
                $now,
            ])
            ->build()
            ->append(
                'ON DUPLICATE KEY UPDATE '
                    . '`title` = VALUES(`title`), '
                    . '`url` = VALUES(`url`), '
                    . '`published_at` = VALUES(`published_at`), '
                    . '`type` = VALUES(`type`), '
                    . '`is_display` = VALUES(`is_display`), '
                    . '`updated_at` = VALUES(`updated_at`)',
            )
            ->execute($this->queryFactory->pdo());

        $this->queryFactory->insertRows(
            'media_taggings',
            ['media_id', 'tag_id'],
            array_map(
                fn (array $tag): array => [$binMediaId, $this->converter->toBin($tag['tag_id'])],
                $data['tags'],
            ),
        );

        return $media;
    }

    #[Override]
    public function delete(MediaId $mediaId): void
    {
        $this->queryFactory->delete()
            ->from(self::TABLE)
            ->where('media_id', '=', $this->converter->toBin($mediaId->value))
            ->execute($this->queryFactory->pdo());
    }

    private function buildSearchQuery(MediaSearchCriteria $criteria): SelectBuilder
    {
        $query = $this->queryFactory->select()->from(self::TABLE);

        if ($criteria->title->isPresent()) {
            $query = $query->where(
                'title_lower',
                'LIKE',
                SqlHelper::containsPattern(mb_strtolower($criteria->title->get())),
            );
        }

        if ($criteria->type->isPresent()) {
            $query = $query->where('type', '=', $criteria->type->get()->value);
        }

        if ($criteria->isDisplay->isPresent()) {
            $query = $query->where('is_display', '=', $criteria->isDisplay->get());
        }

        if ($criteria->tagId->isPresent()) {
            $query = $query->where(
                'media_id',
                'IN',
                $this->queryFactory->select()
                    ->withSelect(['media_id'])
                    ->from('media_taggings')
                    ->where('tag_id', '=', $this->converter->toBin($criteria->tagId->get()->value)),
            );
        }

        return $query;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return array<int, Media>
     */
    private function hydrateAll(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        // タグ付けはまとめて引き、メディアごとの問い合わせを避ける
        $taggingRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['media_taggings.media_id', 'media_taggings.tag_id'])
                ->from('media_taggings')
                ->join('tags', 'media_taggings.tag_id = tags.tag_id')
                ->where('media_taggings.media_id', 'IN', array_map(static fn (array $row): string => Row::string($row, 'media_id'), $rows))
                ->orderBy('tags.order_no'),
        );

        $tags = [];
        foreach ($taggingRows as $taggingRow) {
            $tags[Row::string($taggingRow, 'media_id')][] = ['tagId' => $this->converter->toUuid(Row::string($taggingRow, 'tag_id'))];
        }

        return array_map(
            fn (array $row): Media => $this->hydrate($row, $tags[Row::string($row, 'media_id')] ?? []),
            $rows,
        );
    }

    /**
     * @param array<string, mixed>             $row
     * @param array<int, array{tagId: string}> $tags
     */
    private function hydrate(array $row, array $tags): Media
    {
        return Media::reconstruct(
            $this->converter->toUuid(Row::string($row, 'media_id')),
            Row::string($row, 'title'),
            Row::string($row, 'url'),
            new DateTimeImmutable(Row::string($row, 'published_at')),
            Row::int($row, 'type'),
            Row::bool($row, 'is_display'),
            $tags,
        );
    }
}
