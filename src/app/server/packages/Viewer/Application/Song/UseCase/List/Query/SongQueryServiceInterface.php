<?php

declare(strict_types=1);

namespace Viewer\Application\Song\UseCase\List\Query;

interface SongQueryServiceInterface
{
    public function list(?string $cursor, int $limit): SongListPage;
}
