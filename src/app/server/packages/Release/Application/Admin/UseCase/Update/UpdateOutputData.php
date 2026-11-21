<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Update;

use Release\Domain\Models\Release;

readonly class UpdateOutputData
{
    public function __construct(public Release $release)
    {
    }
}
