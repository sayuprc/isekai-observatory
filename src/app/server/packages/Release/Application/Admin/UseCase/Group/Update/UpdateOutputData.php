<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\Update;

use Release\Domain\Models\ReleaseGroup;

readonly class UpdateOutputData
{
    public function __construct(public ReleaseGroup $releaseGroup)
    {
    }
}
