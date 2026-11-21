<?php

declare(strict_types=1);

namespace Release\Application\Viewer\UseCase\List\Query;

interface ReleaseGroupQueryServiceInterface
{
    public function list(?string $cursor, int $limit): ReleaseGroupListPage;
}
