<?php

declare(strict_types=1);

namespace Tag\Infrastructures\Admin;

use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Tag\Application\Admin\Query\TagUsageCount;
use Tag\Application\Admin\Query\TagUsageCountQueryServiceInterface;
use Tag\Domain\Models\TagId;

readonly class TagUsageCountQueryService implements TagUsageCountQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function countUsages(array $tagIds): array
    {
        // 件数は渡された ID でまとめて引き、タグごとの問い合わせを避ける
        $binTagIds = array_map(fn (TagId $tagId): string => $this->converter->toBin($tagId->value), $tagIds);
        $songCounts = $this->queryFactory->countBy('song_taggings', 'tag_id', $binTagIds);
        $eventCounts = $this->queryFactory->countBy('event_taggings', 'tag_id', $binTagIds);

        $usages = [];
        foreach ($binTagIds as $binTagId) {
            $usages[$this->converter->toUuid($binTagId)] = new TagUsageCount(
                $songCounts[$binTagId] ?? 0,
                $eventCounts[$binTagId] ?? 0,
            );
        }

        return $usages;
    }
}
