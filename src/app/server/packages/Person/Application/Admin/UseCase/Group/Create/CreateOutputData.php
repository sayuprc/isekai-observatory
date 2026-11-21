<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Group\Create;

use Person\Application\Admin\Query\PersonGroupSummary;

readonly class CreateOutputData
{
    public function __construct(public PersonGroupSummary $personGroup)
    {
    }
}
