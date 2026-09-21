<?php

declare(strict_types=1);

namespace Song\Domain\Models\Tags;

use Song\Domain\Models\Tag\SongTagId;

readonly class SongTagReference
{
    public function __construct(public SongTagId $songTagId)
    {
    }

    public static function reconstruct(string $songTagId): self
    {
        return new self(new SongTagId($songTagId));
    }

    /**
     * @return array{song_tag_id: string}
     */
    public function toArray(): array
    {
        return [
            'song_tag_id' => $this->songTagId->value,
        ];
    }
}
