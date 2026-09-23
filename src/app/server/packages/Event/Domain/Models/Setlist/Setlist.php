<?php

declare(strict_types=1);

namespace Event\Domain\Models\Setlist;

use Event\Domain\Models\Performances\PerformanceId;
use Support\Collection\ImmutableCollection;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\ValueObjects\OrderNo;

/**
 * @extends ImmutableCollection<int, SetlistItem>
 */
readonly class Setlist extends ImmutableCollection
{
    /**
     * @param list<array{setlistItemId: string, orderNo: int, label: ?string, performanceIds: list<string>}> $items
     *
     * @throws BusinessRuleViolationException
     */
    public static function fromArray(array $items): self
    {
        if (count($items) !== count(array_unique(array_column($items, 'setlistItemId')))) {
            throw new BusinessRuleViolationException('セットリスト項目を重複して登録できません');
        }

        if (count($items) !== count(array_unique(array_column($items, 'orderNo')))) {
            throw new BusinessRuleViolationException('セットリストの順序を重複して登録できません');
        }

        $referencedIds = array_merge([], ...array_column($items, 'performanceIds'));
        if (count($referencedIds) !== count(array_unique($referencedIds))) {
            throw new BusinessRuleViolationException('同じ楽曲披露を複数のセットリスト項目から参照できません');
        }

        foreach ($items as $item) {
            if ($item['label'] === null && $item['performanceIds'] === []) {
                throw new BusinessRuleViolationException('セットリスト項目には表示名または楽曲披露を指定してください');
            }
        }

        return self::reconstruct($items);
    }

    /**
     * @param list<array{setlistItemId: string, orderNo: int, label: ?string, performanceIds: list<string>}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(
            static fn (array $item): SetlistItem => new SetlistItem(
                new SetlistItemId($item['setlistItemId']),
                new OrderNo($item['orderNo']),
                $item['label'] === null ? null : new SetlistLabel($item['label']),
                array_map(static fn (string $id): PerformanceId => new PerformanceId($id), $item['performanceIds']),
            ),
            $items,
        ));
    }

    /**
     * @return list<PerformanceId>
     */
    public function referencedPerformanceIds(): array
    {
        return array_merge([], ...array_map(static fn (SetlistItem $item): array => $item->performanceIds, array_values($this->items)));
    }

    /**
     * @return list<array{setlist_item_id: string, order_no: int, label: ?string, performance_ids: list<string>}>
     */
    public function toArray(): array
    {
        return array_values(array_map(static fn (SetlistItem $item): array => $item->toArray(), $this->items));
    }
}
