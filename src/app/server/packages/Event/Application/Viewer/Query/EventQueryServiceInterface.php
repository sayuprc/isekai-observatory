<?php

declare(strict_types=1);

namespace Event\Application\Viewer\Query;

interface EventQueryServiceInterface
{
    public function list(?string $cursor, int $limit): EventListPage;
}
