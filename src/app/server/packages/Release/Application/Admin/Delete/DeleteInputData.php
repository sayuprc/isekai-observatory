<?php

declare(strict_types=1);

namespace Release\Application\Admin\Delete;

readonly class DeleteInputData
{
    public function __construct(public string $releaseId)
    {
    }
}
