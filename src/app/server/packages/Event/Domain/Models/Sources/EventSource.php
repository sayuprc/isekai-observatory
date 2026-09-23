<?php

declare(strict_types=1);

namespace Event\Domain\Models\Sources;

use Support\Domain\ValueObjects\OrderNo;

readonly class EventSource
{
    public function __construct(
        public EventSourceName $displayName,
        public EventSourceUrl $url,
        public OrderNo $orderNo,
    ) {
    }

    /**
     * @return array{name: string, url: string, order_no: int}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->displayName->value,
            'url' => $this->url->value,
            'order_no' => $this->orderNo->value,
        ];
    }
}
