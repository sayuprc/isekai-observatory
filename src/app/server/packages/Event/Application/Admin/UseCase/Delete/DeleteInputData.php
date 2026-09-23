<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Delete;

readonly class DeleteInputData
{
    public function __construct(public string $eventId)
    {
    }
}
