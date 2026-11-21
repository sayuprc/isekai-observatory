<?php

declare(strict_types=1);

namespace Release\Application\Admin\Group\BulkUpdate;

readonly class BulkUpdateOutputData
{
    public function __construct(public int $updatedCount)
    {
    }
}
