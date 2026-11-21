<?php

declare(strict_types=1);

namespace Song\Application\Viewer\List\Query;

interface SongQueryServiceInterface
{
    public function list(?string $cursor, int $limit): SongListPage;
}
