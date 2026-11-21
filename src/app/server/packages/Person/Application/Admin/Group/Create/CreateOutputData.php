<?php

declare(strict_types=1);

namespace Person\Application\Admin\Group\Create;

use Person\Application\Admin\Group\Query\PersonGroupSummary;

readonly class CreateOutputData
{
    public function __construct(public PersonGroupSummary $personGroup)
    {
    }
}
