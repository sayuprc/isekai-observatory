<?php

declare(strict_types=1);

namespace Media\Application\Viewer\Query;

interface MediaQueryServiceInterface
{
    public function list(?string $cursor, int $limit): MediaListPage;
}
