<?php

declare(strict_types=1);

namespace Venue\Application\Admin\Get;

readonly class GetInputData
{
    public function __construct(public string $venueId)
    {
    }
}
