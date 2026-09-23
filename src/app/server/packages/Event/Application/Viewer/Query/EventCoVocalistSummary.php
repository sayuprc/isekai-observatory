<?php

declare(strict_types=1);

namespace Event\Application\Viewer\Query;

readonly class EventCoVocalistSummary
{
    public function __construct(
        public string $personId,
        public string $name,
        public ?string $creditName,
        public int $orderNo,
    ) {
    }
}
