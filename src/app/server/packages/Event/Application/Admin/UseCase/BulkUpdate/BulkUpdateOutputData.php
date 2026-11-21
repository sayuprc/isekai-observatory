<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\BulkUpdate;

readonly class BulkUpdateOutputData
{
    public function __construct(public int $updatedCount)
    {
    }
}
