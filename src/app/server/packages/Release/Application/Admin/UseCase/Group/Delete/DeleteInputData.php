<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\Delete;

readonly class DeleteInputData
{
    public function __construct(public string $releaseGroupId)
    {
    }
}
