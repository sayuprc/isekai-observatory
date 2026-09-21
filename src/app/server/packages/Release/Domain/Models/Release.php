<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use DateType\ImmutableDate;
use Support\Domain\ValueObjects\OrderNo;

readonly class Release
{
    public function __construct(
        public ReleaseId $releaseId,
        public ReleaseGroupId $releaseGroupId,
        public ReleaseName $name,
        public ReleasedOn $releasedOn,
        public Description $description,
        public Color $color,
        public bool $isDisplay,
        public OrderNo $orderNo,
        public ReleaseFormats $formats,
        public Media $media,
    ) {
    }

    /**
     * @param list<int>                                                                                                     $formats
     * @param list<array{position: int, name: ?string, tracks: list<array{songId: ?string, title: ?string, trackNo: int}>}> $media
     */
    public static function reconstruct(
        string $releaseId,
        string $releaseGroupId,
        string $name,
        ImmutableDate $releasedOn,
        string $description,
        string $color,
        bool $isDisplay,
        int $orderNo,
        array $formats,
        array $media,
    ): self {
        return new self(
            new ReleaseId($releaseId),
            new ReleaseGroupId($releaseGroupId),
            new ReleaseName($name),
            new ReleasedOn($releasedOn),
            new Description($description),
            new Color($color),
            $isDisplay,
            new OrderNo($orderNo),
            ReleaseFormats::reconstruct($formats),
            Media::reconstruct($media),
        );
    }

    /**
     * @return array{release_id: string, release_group_id: string, name: string, released_on: string, description: string, color: string, is_display: bool, order_no: int, formats: list<value-of<ReleaseFormat>>, media: list<array{position: int, name: ?string, tracks: list<array{song_id: ?string, title: ?string, track_no: int}>}>}
     */
    public function toArray(): array
    {
        return [
            'release_id' => $this->releaseId->value,
            'release_group_id' => $this->releaseGroupId->value,
            'name' => $this->name->value,
            'released_on' => $this->releasedOn->value->format('Y-m-d'),
            'description' => $this->description->value,
            'color' => $this->color->value,
            'is_display' => $this->isDisplay,
            'order_no' => $this->orderNo->value,
            'formats' => $this->formats->toArray(),
            'media' => $this->media->toArray(),
        ];
    }

    public function equals(self $other): bool
    {
        return $this->releaseId->equals($other->releaseId);
    }
}
