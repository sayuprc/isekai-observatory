<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Create;

use Release\Domain\Models\Release;

readonly class CreateOutputData
{
    public function __construct(public Release $release)
    {
    }
}
