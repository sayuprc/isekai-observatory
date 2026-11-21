<?php

declare(strict_types=1);

namespace Song\Application\Admin\Get;

readonly class GetInputData
{
    public function __construct(public string $songId)
    {
    }
}
