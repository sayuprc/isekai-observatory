<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\Get;

readonly class GetInputData
{
    public function __construct(public string $releaseGroupId)
    {
    }
}
