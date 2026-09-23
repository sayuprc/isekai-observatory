<?php

declare(strict_types=1);

namespace Event\Domain\Models\Performances;

use Song\Domain\Models\SongId;
use Support\Domain\ValueObjects\OrderNo;

readonly class SongPerformance
{
    public function __construct(
        public PerformanceId $performanceId,
        public SongId $songId,
        public OrderNo $orderNo,
        public CoVocalists $coVocalists,
    ) {
    }

    /**
     * @return array{performance_id: string, song_id: string, order_no: int, co_vocalists: list<array{person_id: string, credit_name: ?string, order_no: int}>}
     */
    public function toArray(): array
    {
        return [
            'performance_id' => $this->performanceId->value,
            'song_id' => $this->songId->value,
            'order_no' => $this->orderNo->value,
            'co_vocalists' => $this->coVocalists->toArray(),
        ];
    }
}
