<?php

declare(strict_types=1);

namespace Person\Application\Admin\Delete;

readonly class DeleteInputData
{
    public function __construct(public string $personId)
    {
    }
}
