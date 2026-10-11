<?php

declare(strict_types=1);

namespace Event\Application\Viewer\Query;

readonly class EventCoVocalistGroupSummary
{
    public function __construct(
        public string $personGroupId,
        public string $name,
    ) {
    }
}
