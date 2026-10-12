<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use Song\Domain\Models\SongId;
use Support\Collection\ImmutableCollection;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\ValueObjects\OrderNo;

/**
 * @extends ImmutableCollection<int, Track>
 */
readonly class Tracks extends ImmutableCollection
{
    /**
     * @param list<array{songIds: list<string>, title: ?string, trackNo: int}> $items
     *
     * @throws BusinessRuleViolationException
     */
    public static function fromArray(array $items): self
    {
        $tracks = [];
        $seenTrackNos = [];

        foreach ($items as $item) {
            $track = Track::create(
                array_map(static fn (string $songId): SongId => new SongId($songId), $item['songIds']),
                $item['title'] === null ? null : new TrackTitle($item['title']),
                new OrderNo($item['trackNo']),
            );

            if (isset($seenTrackNos[$track->trackNo->value])) {
                throw new BusinessRuleViolationException('同じ曲順を複数指定することはできません。');
            }

            $seenTrackNos[$track->trackNo->value] = true;
            $tracks[] = $track;
        }

        return new self($tracks);
    }

    /**
     * @param list<array{songIds: list<string>, title: ?string, trackNo: int}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(
            static fn (array $item): Track => Track::reconstruct(
                $item['songIds'],
                $item['title'],
                $item['trackNo'],
            ),
            $items,
        ));
    }

    /**
     * @return list<array{song_ids: list<string>, title: ?string, track_no: int}>
     */
    public function toArray(): array
    {
        $items = [];

        foreach ($this->toGeneric() as $item) {
            $items[] = $item->toArray();
        }

        return $items;
    }
}
