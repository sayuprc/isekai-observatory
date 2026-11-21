<?php

declare(strict_types=1);

namespace Event\Application\Admin\Delete;

readonly class DeleteInputData
{
    public function __construct(public string $eventId)
    {
    }
}
