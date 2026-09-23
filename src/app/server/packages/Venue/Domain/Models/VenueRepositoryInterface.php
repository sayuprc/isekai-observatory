<?php

declare(strict_types=1);

namespace Venue\Domain\Models;

use Venue\Domain\Criteria\VenueSearchCriteria;

interface VenueRepositoryInterface
{
    /**
     * @return array<Venue>
     */
    public function search(VenueSearchCriteria $criteria): array;

    public function maxPage(VenueSearchCriteria $criteria): int;

    public function find(VenueId $venueId): ?Venue;

    public function isUsed(VenueId $venueId): bool;

    public function save(Venue $venue): Venue;

    public function delete(VenueId $venueId): void;
}
