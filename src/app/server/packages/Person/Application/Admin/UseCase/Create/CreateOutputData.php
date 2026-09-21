<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Create;

use Person\Domain\Models\Person;

readonly class CreateOutputData
{
    public function __construct(public Person $person)
    {
    }
}
