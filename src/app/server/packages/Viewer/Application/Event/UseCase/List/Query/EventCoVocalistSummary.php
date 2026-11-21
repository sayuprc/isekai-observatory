<?php

declare(strict_types=1);

namespace Viewer\Application\Event\UseCase\List\Query;

readonly class EventCoVocalistSummary
{
    public function __construct(
        public string $personId,
        public string $name,
        public ?string $creditName,
        public ?EventCoVocalistGroupSummary $personGroup,
        public int $orderNo,
    ) {
    }
}
