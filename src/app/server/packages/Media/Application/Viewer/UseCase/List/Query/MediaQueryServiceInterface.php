<?php

declare(strict_types=1);

namespace Media\Application\Viewer\UseCase\List\Query;

interface MediaQueryServiceInterface
{
    public function list(?string $cursor, int $limit): MediaListPage;
}
