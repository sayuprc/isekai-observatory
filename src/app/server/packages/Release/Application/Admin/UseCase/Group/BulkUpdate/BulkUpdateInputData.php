<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\BulkUpdate;

use Support\Optional\Arg;

readonly class BulkUpdateInputData
{
    /**
     * @param list<string> $releaseGroupIds
     * @param list<string> $addTagIds
     * @param list<string> $removeTagIds
     */
    public function __construct(
        public array $releaseGroupIds,
        public array $addTagIds = [],
        public array $removeTagIds = [],
        public Arg|bool $isDisplay = Arg::Optional,
    ) {
    }
}
