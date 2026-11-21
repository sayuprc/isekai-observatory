<?php

declare(strict_types=1);

namespace Venue\Application\Admin\UseCase\Create;

use Venue\Domain\Models\Venue;

readonly class CreateOutputData
{
    public function __construct(public Venue $venue)
    {
    }
}
