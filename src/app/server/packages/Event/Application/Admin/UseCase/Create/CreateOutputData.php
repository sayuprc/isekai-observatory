<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Create;

use Event\Domain\Models\Event;

readonly class CreateOutputData
{
    public function __construct(public Event $event)
    {
    }
}
