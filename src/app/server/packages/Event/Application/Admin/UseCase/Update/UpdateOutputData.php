<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Update;

use Event\Domain\Models\Event;

readonly class UpdateOutputData
{
    public function __construct(public Event $event)
    {
    }
}
