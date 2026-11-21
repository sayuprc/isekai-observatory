<?php

declare(strict_types=1);

namespace Tag\Application\Admin\Create;

readonly class CreateInputData
{
    public function __construct(public string $name)
    {
    }
}
