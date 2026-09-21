<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use Support\Domain\ValueObjects\OrderNo;

readonly class Medium
{
    public function __construct(
        public OrderNo $position,
        public ?MediumName $name,
        public Tracks $tracks,
    ) {
    }

    /**
     * @param list<array{songId: ?string, title: ?string, trackNo: int}> $tracks
     */
    public static function reconstruct(int $position, ?string $name, array $tracks): self
    {
        return new self(
            new OrderNo($position),
            is_null($name) ? null : new MediumName($name),
            Tracks::reconstruct($tracks),
        );
    }

    /**
     * @return array{position: int, name: ?string, tracks: list<array{song_id: ?string, title: ?string, track_no: int}>}
     */
    public function toArray(): array
    {
        return [
            'position' => $this->position->value,
            'name' => $this->name?->value,
            'tracks' => $this->tracks->toArray(),
        ];
    }
}
