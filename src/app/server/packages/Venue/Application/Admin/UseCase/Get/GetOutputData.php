<?php

declare(strict_types=1);

namespace Venue\Application\Admin\UseCase\Get;

use Venue\Domain\Models\Venue;

readonly class GetOutputData
{
    public function __construct(public Venue $venue)
    {
    }
}
