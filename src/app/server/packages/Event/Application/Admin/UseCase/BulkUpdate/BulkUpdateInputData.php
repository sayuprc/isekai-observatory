<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\BulkUpdate;

use Support\Optional\Arg;

readonly class BulkUpdateInputData
{
    /**
     * @param list<string> $eventIds
     * @param list<string> $addTagIds
     * @param list<string> $removeTagIds
     */
    public function __construct(
        public array $eventIds,
        public array $addTagIds = [],
        public array $removeTagIds = [],
        public Arg|bool $isDisplay = Arg::Optional,
    ) {
    }
}
