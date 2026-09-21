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

    /** @var list<string> */
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

        $row = $rows[0] ?? null;

        return is_null($row) ? null : $this->hydrate($row);
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

        $row = $rows[0] ?? null;

        return is_null($row) ? null : $this->hydrate($row);
    }

    #[Override]
    public function isUsed(MediaId $mediaId): bool
    {
        $count = Row::intValue(
            $this->queryFactory->select()
                ->from('song_media_links')
                ->where('media_id', '=', $this->converter->toBin($mediaId->value))
                ->aggregate($this->queryFactory->pdo(), 'COUNT(*)'),
        );

        return $count > 0;
    }

    #[Override]
    public function search(MediaSearchCriteria $criteria): array
    {
        $offset = ($criteria->page - 1) * $criteria->perPage->value;

        $rows = $this->queryFactory->fetchAll(
            $this->buildSearchQuery($criteria)
                ->withSelect(self::COLUMNS)
                ->orderBy($criteria->sort->value, $criteria->order->value)
                ->limit($criteria->perPage->value)
                ->offset($offset),
        );

        return array_map($this->hydrate(...), $rows);
    }

    #[Override]
    public function maxPage(MediaSearchCriteria $criteria): int
    {
        $count = Row::intValue($this->buildSearchQuery($criteria)->aggregate($this->queryFactory->pdo(), 'COUNT(*)'));

        return (int)ceil($count / $criteria->perPage->value);
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

        return array_map($this->hydrate(...), $rows);
    }

    #[Override]
    public function save(Media $media): Media
    {
        $data = $media->toArray();
        $now = now()->toDateTimeString();

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
                $this->converter->toBin($media->mediaId->value),
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
                'title',
                'LIKE',
                '%' . SqlHelper::escapeLike($criteria->title->get()) . '%',
            );
        }

        if ($criteria->type->isPresent()) {
            $query = $query->where('type', '=', $criteria->type->get()->value);
        }

        if ($criteria->isDisplay->isPresent()) {
            $query = $query->where('is_display', '=', $criteria->isDisplay->get());
        }

        return $query;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Media
    {
        return Media::reconstruct(
            $this->converter->toUuid(Row::string($row, 'media_id')),
            Row::string($row, 'title'),
            Row::string($row, 'url'),
            new DateTimeImmutable(Row::string($row, 'published_at')),
            Row::int($row, 'type'),
            Row::bool($row, 'is_display'),
        );
    }
}
