<?php

declare(strict_types=1);

namespace Media\Application\Public\Query;

interface MediaQueryServiceInterface
{
    public function list(?string $pageToken, int $pageSize): MediaListPage;
}
