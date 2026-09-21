<?php

declare(strict_types=1);

namespace Venue\Domain\Models;

readonly class Venue
{
    public function __construct(
        public VenueId $venueId,
        public VenueName $name,
        public VenueKind $kind,
    ) {
    }

    public static function reconstruct(string $venueId, string $name, int $kind): self
    {
        return new self(
            new VenueId($venueId),
            new VenueName($name),
            VenueKind::from($kind),
        );
    }

    /**
     * @return array{venue_id: string, name: string, kind: int}
     */
    public function toArray(): array
    {
        return [
            'venue_id' => $this->venueId->value,
            'name' => $this->name->value,
            'kind' => $this->kind->value,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->venueId->equals($other->venueId);
    }
}
