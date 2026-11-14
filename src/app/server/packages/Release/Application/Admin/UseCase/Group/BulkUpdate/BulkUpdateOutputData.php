<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\BulkUpdate;

readonly class BulkUpdateOutputData
{
    public function __construct(public int $updatedCount)
    {
    }
}
