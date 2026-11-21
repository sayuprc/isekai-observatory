<?php

declare(strict_types=1);

namespace Person\Application\Admin\Get;

readonly class GetInputData
{
    public function __construct(public string $personId)
    {
    }
}
