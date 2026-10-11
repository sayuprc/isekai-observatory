<?php

declare(strict_types=1);

namespace Release\Application\Viewer\Query;

interface ReleaseGroupQueryServiceInterface
{
    public function list(?string $cursor, int $limit): ReleaseGroupListPage;
}
