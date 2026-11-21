<?php

declare(strict_types=1);

namespace Viewer\Application\Event\UseCase\List\Query;

interface EventQueryServiceInterface
{
    public function list(?string $cursor, int $limit): EventListPage;
}
