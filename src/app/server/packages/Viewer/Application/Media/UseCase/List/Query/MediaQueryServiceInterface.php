<?php

declare(strict_types=1);

namespace Viewer\Application\Media\UseCase\List\Query;

interface MediaQueryServiceInterface
{
    public function list(?string $cursor, int $limit): MediaListPage;
}
