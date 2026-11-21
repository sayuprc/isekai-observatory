<?php

declare(strict_types=1);

namespace Public\Application\Event\UseCase\List\Query;

use SensitiveParameter;

interface EventQueryServiceInterface
{
    public function list(#[SensitiveParameter] ?string $pageToken, int $pageSize): EventListPage;
}
