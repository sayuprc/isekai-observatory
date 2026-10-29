<?php

declare(strict_types=1);

namespace Release\Infrastructures;

use Override;
use Release\Domain\Models\ReleaseGroup;
use Release\Domain\Models\ReleaseGroupId;
use Release\Domain\Models\ReleaseGroupRepositoryInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class ReleaseGroupRepository implements ReleaseGroupRepositoryInterface
{
    private const string TABLE = 'release_groups';

    /** @var array<int, string> */
    private const array COLUMNS = [
        'release_group_id',
        'title',
        'type',
        'description',
        'note',
        'is_display',
        'order_no',
    ];

    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function find(ReleaseGroupId $releaseGroupId): ?ReleaseGroup
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('release_group_id', '=', $this->converter->toBin($releaseGroupId->value))
                ->limit(1),
        );

        return $this->hydrateAll($rows)[0] ?? null;
    }

    #[Override]
    public function findByIds(ReleaseGroupId ...$releaseGroupIds): array
    {
        if ($releaseGroupIds === []) {
            return [];
        }

        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('release_group_id', 'IN', array_map(
                    fn (ReleaseGroupId $releaseGroupId): string => $this->converter->toBin($releaseGroupId->value),
                    $releaseGroupIds,
                )),
        );

        return $this->hydrateAll($rows);
    }

    #[Override]
    public function save(ReleaseGroup $releaseGroup): ReleaseGroup
    {
        $data = $releaseGroup->toArray();
        $now = now()->toDateTimeString();
        $binReleaseGroupId = $this->converter->toBin($releaseGroup->releaseGroupId->value);

        $this->queryFactory->deleteFromTables(['release_group_taggings'], 'release_group_id', $binReleaseGroupId);

        $this->queryFactory->insert()
            ->into(self::TABLE, [
                'release_group_id',
                'title',
                'type',
                'description',
                'note',
                'is_display',
                'order_no',
                'created_at',
                'updated_at',
            ])
            ->values([
                $binReleaseGroupId,
                $data['title'],
                $data['type'],
                $data['description'],
                $data['note'],
                $data['is_display'],
                $data['order_no'],
                $now,
                $now,
            ])
            ->build()
            ->append(
                'ON DUPLICATE KEY UPDATE '
                    . '`title` = VALUES(`title`), '
                    . '`type` = VALUES(`type`), '
                    . '`description` = VALUES(`description`), '
                    . '`note` = VALUES(`note`), '
                    . '`is_display` = VALUES(`is_display`), '
                    . '`order_no` = VALUES(`order_no`), '
                    . '`updated_at` = VALUES(`updated_at`)',
            )
            ->execute($this->queryFactory->pdo());

        $this->queryFactory->insertRows(
            'release_group_taggings',
            ['release_group_id', 'tag_id'],
            array_map(
                fn (array $tag): array => [$binReleaseGroupId, $this->converter->toBin($tag['tag_id'])],
                $data['tags'],
            ),
        );

        return $releaseGroup;
    }

    #[Override]
    public function delete(ReleaseGroupId $releaseGroupId): void
    {
        $this->queryFactory->delete()
            ->from(self::TABLE)
            ->where('release_group_id', '=', $this->converter->toBin($releaseGroupId->value))
            ->execute($this->queryFactory->pdo());
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return array<int, ReleaseGroup>
     */
    private function hydrateAll(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        // タグ付けはまとめて引き、リリースグループごとの問い合わせを避ける
        $taggingRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['release_group_taggings.release_group_id', 'release_group_taggings.tag_id'])
                ->from('release_group_taggings')
                ->join('tags', 'release_group_taggings.tag_id = tags.tag_id')
                ->where('release_group_taggings.release_group_id', 'IN', array_map(
                    static fn (array $row): string => Row::string($row, 'release_group_id'),
                    $rows,
                ))
                ->orderBy('tags.order_no'),
        );

        $tags = [];
        foreach ($taggingRows as $taggingRow) {
            $tags[Row::string($taggingRow, 'release_group_id')][] = ['tagId' => $this->converter->toUuid(Row::string(
                $taggingRow,
                'tag_id',
            ))];
        }

        return array_map(
            fn (array $row): ReleaseGroup => $this->hydrate($row, $tags[Row::string($row, 'release_group_id')] ?? []),
            $rows,
        );
    }

    /**
     * @param array<string, mixed>             $row
     * @param array<int, array{tagId: string}> $tags
     */
    private function hydrate(array $row, array $tags): ReleaseGroup
    {
        return ReleaseGroup::reconstruct(
            $this->converter->toUuid(Row::string($row, 'release_group_id')),
            Row::string($row, 'title'),
            Row::int($row, 'type'),
            Row::string($row, 'description'),
            Row::string($row, 'note'),
            Row::bool($row, 'is_display'),
            Row::int($row, 'order_no'),
            $tags,
        );
    }
}
