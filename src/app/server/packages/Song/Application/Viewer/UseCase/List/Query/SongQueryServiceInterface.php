<?php

declare(strict_types=1);

namespace Song\Application\Viewer\UseCase\List\Query;

interface SongQueryServiceInterface
{
    public function list(?string $cursor, int $limit): SongListPage;
}
