<?php

declare(strict_types=1);

namespace Event\Domain\Models\Releases;

use Release\Domain\Models\ReleaseId;
use Support\Domain\ValueObjects\OrderNo;

readonly class EventReleaseLink
{
    public function __construct(
        public ReleaseId $releaseId,
        public OrderNo $orderNo,
    ) {
    }

    /**
     * @return array{release_id: string, order_no: int}
     */
    public function toArray(): array
    {
        return [
            'release_id' => $this->releaseId->value,
            'order_no' => $this->orderNo->value,
        ];
    }
}
