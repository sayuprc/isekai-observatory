<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\BulkUpdate;

readonly class BulkUpdateOutputData
{
    public function __construct(public int $updatedCount)
    {
    }
}
