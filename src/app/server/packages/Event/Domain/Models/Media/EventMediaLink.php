<?php

declare(strict_types=1);

namespace Event\Domain\Models\Media;

use Media\Domain\Models\MediaId;
use Support\Domain\ValueObjects\OrderNo;

readonly class EventMediaLink
{
    public function __construct(
        public MediaId $mediaId,
        public OrderNo $orderNo,
    ) {
    }

    /**
     * @return array{media_id: string, order_no: int}
     */
    public function toArray(): array
    {
        return [
            'media_id' => $this->mediaId->value,
            'order_no' => $this->orderNo->value,
        ];
    }
}
