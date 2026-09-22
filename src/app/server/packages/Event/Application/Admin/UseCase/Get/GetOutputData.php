<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Get;

use Event\Domain\Models\Event;

readonly class GetOutputData
{
    public function __construct(public Event $event)
    {
    }
}
