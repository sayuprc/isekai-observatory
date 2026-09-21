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

    /** @var list<string> */
    private const array COLUMNS = ['release_group_id', 'title', 'type', 'description', 'is_display', 'order_no'];

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

        $row = $rows[0] ?? null;

        if (is_null($row)) {
            return null;
        }

        return $this->hydrate($row);
    }

    #[Override]
    public function save(ReleaseGroup $releaseGroup): ReleaseGroup
    {
        $data = $releaseGroup->toArray();
        $now = now()->toDateTimeString();

        $this->queryFactory->insert()
            ->into(self::TABLE, ['release_group_id', 'title', 'type', 'description', 'is_display', 'order_no', 'created_at', 'updated_at'])
            ->values([
                $this->converter->toBin($releaseGroup->releaseGroupId->value),
                $data['title'],
                $data['type'],
                $data['description'],
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
                . '`is_display` = VALUES(`is_display`), '
                . '`order_no` = VALUES(`order_no`), '
                . '`updated_at` = VALUES(`updated_at`)',
            )
            ->execute($this->queryFactory->pdo());

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
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): ReleaseGroup
    {
        return ReleaseGroup::reconstruct(
            $this->converter->toUuid(Row::string($row, 'release_group_id')),
            Row::string($row, 'title'),
            Row::int($row, 'type'),
            Row::string($row, 'description'),
            Row::bool($row, 'is_display'),
            Row::int($row, 'order_no'),
        );
    }
}
