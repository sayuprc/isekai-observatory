<?php

declare(strict_types=1);

namespace Venue\Domain\Services;

use Support\Contracts\Uuid\UuidGeneratorInterface;
use Venue\Domain\Models\Venue;
use Venue\Domain\Models\VenueId;
use Venue\Domain\Models\VenueKind;
use Venue\Domain\Models\VenueName;

readonly class VenueIntegrityService
{
    public function __construct(private UuidGeneratorInterface $generator)
    {
    }

    public function prepareForCreate(string $name, int $kind): Venue
    {
        return $this->build($this->generator->generate(), $name, $kind);
    }

    public function prepareForUpdate(string $venueId, string $name, int $kind): Venue
    {
        return $this->build($venueId, $name, $kind);
    }

    private function build(string $venueId, string $name, int $kind): Venue
    {
        return new Venue(
            new VenueId($venueId),
            new VenueName($name),
            VenueKind::from($kind),
        );
    }
}
