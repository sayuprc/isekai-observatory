<?php

declare(strict_types=1);

namespace Event\Domain\Models\Setlist;

use Event\Domain\Models\Performances\PerformanceId;
use Support\Domain\ValueObjects\OrderNo;

readonly class SetlistItem
{
    /**
     * @param list<PerformanceId> $performanceIds
     */
    public function __construct(
        public SetlistItemId $setlistItemId,
        public OrderNo $orderNo,
        public ?SetlistLabel $label,
        public array $performanceIds,
    ) {
    }

    /**
     * @return array{setlist_item_id: string, order_no: int, label: ?string, performance_ids: list<string>}
     */
    public function toArray(): array
    {
        return [
            'setlist_item_id' => $this->setlistItemId->value,
            'order_no' => $this->orderNo->value,
            'label' => $this->label?->value,
            'performance_ids' => array_map(static fn (PerformanceId $id): string => $id->value, $this->performanceIds),
        ];
    }
}
