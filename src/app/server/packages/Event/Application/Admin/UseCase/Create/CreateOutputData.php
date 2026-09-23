<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Create;

use Event\Application\Admin\Assemble\AssembledEvent;

readonly class CreateOutputData
{
    public function __construct(public AssembledEvent $event)
    {
    }
}
