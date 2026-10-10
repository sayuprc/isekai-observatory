<?php

declare(strict_types=1);

namespace Event\Application\Public\Query;

interface EventQueryServiceInterface
{
    public function list(?string $pageToken, int $pageSize): EventListPage;
}
