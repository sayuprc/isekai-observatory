<?php

declare(strict_types=1);

namespace Person\Application\Admin\Group\Get;

use Person\Application\Admin\Group\Query\PersonGroupSummary;

readonly class GetOutputData
{
    public function __construct(public PersonGroupSummary $personGroup)
    {
    }
}
