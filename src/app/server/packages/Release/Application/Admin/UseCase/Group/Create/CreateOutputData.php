<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\Create;

use Release\Domain\Models\ReleaseGroup;

readonly class CreateOutputData
{
    public function __construct(public ReleaseGroup $releaseGroup)
    {
    }
}
