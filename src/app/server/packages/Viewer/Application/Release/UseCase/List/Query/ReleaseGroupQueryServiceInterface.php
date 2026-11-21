<?php

declare(strict_types=1);

namespace Viewer\Application\Release\UseCase\List\Query;

interface ReleaseGroupQueryServiceInterface
{
    public function list(?string $cursor, int $limit): ReleaseGroupListPage;
}
