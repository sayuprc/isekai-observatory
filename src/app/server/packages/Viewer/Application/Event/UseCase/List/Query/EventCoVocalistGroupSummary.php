<?php

declare(strict_types=1);

namespace Viewer\Application\Event\UseCase\List\Query;

readonly class EventCoVocalistGroupSummary
{
    public function __construct(
        public string $personGroupId,
        public string $name,
    ) {
    }
}
