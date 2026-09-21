<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use Song\Domain\Models\Media\SongMediaLinks;
use Song\Domain\Models\Persons\SongPersonRole;
use Song\Domain\Models\Persons\SongPersons;
use Song\Domain\Models\Tags\SongTagReferences;
use Support\Domain\ValueObjects\OrderNo;

readonly class Song
{
    public function __construct(
        public SongId $songId,
        public Title $title,
        public Description $description,
        public ?LyricsLink $lyricsLink,
        public SongType $type,
        public bool $isDisplay,
        public OrderNo $orderNo,
        public SongTagReferences $tags,
        public SongPersons $persons,
        public SongMediaLinks $media,
    ) {
    }

    /**
     * @param list<array{songTagId: string}>                         $tags
     * @param list<array{personId: string, role: int, orderNo: int}> $persons
     * @param list<array{mediaId: string, orderNo: int}>             $media
     */
    public static function reconstruct(
        string $songId,
        string $title,
        string $description,
        ?string $lyricsLink,
        int $type,
        bool $isDisplay,
        int $orderNo,
        array $tags,
        array $persons,
        array $media,
    ): self {
        return new self(
            new SongId($songId),
            new Title($title),
            new Description($description),
            is_null($lyricsLink) ? null : new LyricsLink($lyricsLink),
            SongType::from($type),
            $isDisplay,
            new OrderNo($orderNo),
            SongTagReferences::reconstruct($tags),
            SongPersons::reconstruct($persons),
            SongMediaLinks::reconstruct($media),
        );
    }

    /**
     * @return array{song_id: string, title: string, description: string, lyrics_link: string|null, type: value-of<SongType>, is_display: bool, order_no: int, persons: array<int, array{person_id: string, role: value-of<SongPersonRole>, order_no: int}>, tags: array<int, array{song_tag_id: string}>, media: array<int, array{media_id: string, order_no: int}>}
     */
    public function toArray(): array
    {
        return [
            'song_id' => $this->songId->value,
            'title' => $this->title->value,
            'description' => $this->description->value,
            'lyrics_link' => $this->lyricsLink?->value,
            'type' => $this->type->value,
            'is_display' => $this->isDisplay,
            'order_no' => $this->orderNo->value,
            'persons' => $this->persons->toArray(),
            'tags' => $this->tags->toArray(),
            'media' => $this->media->toArray(),
        ];
    }

    public function equals(self $other): bool
    {
        return $this->songId->equals($other->songId);
    }
}
