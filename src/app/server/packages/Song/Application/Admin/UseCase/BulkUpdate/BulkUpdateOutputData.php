<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\BulkUpdate;

readonly class BulkUpdateOutputData
{
    public function __construct(public int $updatedCount)
    {
    }
}
