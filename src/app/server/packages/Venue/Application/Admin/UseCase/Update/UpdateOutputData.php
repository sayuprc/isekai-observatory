<?php

declare(strict_types=1);

namespace Venue\Application\Admin\UseCase\Update;

use Venue\Domain\Models\Venue;

readonly class UpdateOutputData
{
    public function __construct(public Venue $venue)
    {
    }
}
