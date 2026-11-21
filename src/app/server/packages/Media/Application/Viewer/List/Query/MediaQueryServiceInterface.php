<?php

declare(strict_types=1);

namespace Media\Application\Viewer\List\Query;

interface MediaQueryServiceInterface
{
    public function list(?string $cursor, int $limit): MediaListPage;
}
