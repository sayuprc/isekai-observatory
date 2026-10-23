<?php

declare(strict_types=1);

namespace Venue\Application\Public\Query;

interface VenueQueryServiceInterface
{
    public function list(?string $pageToken, int $pageSize): VenueListPage;
}
