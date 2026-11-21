<?php

declare(strict_types=1);

namespace Venue\Application\Public\UseCase\List\Query;

use SensitiveParameter;

interface VenueQueryServiceInterface
{
    public function list(#[SensitiveParameter] ?string $pageToken, int $pageSize): VenueListPage;
}
