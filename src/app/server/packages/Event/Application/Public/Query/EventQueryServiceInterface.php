<?php

declare(strict_types=1);

namespace Event\Application\Public\Query;

use SensitiveParameter;

interface EventQueryServiceInterface
{
    public function list(#[SensitiveParameter] ?string $pageToken, int $pageSize): EventListPage;
}
