<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use Support\Domain\ValueObjects\Note;
use Support\Domain\ValueObjects\OrderNo;

readonly class ReleaseGroup
{
    public function __construct(
        public ReleaseGroupId $releaseGroupId,
        public ReleaseGroupTitle $title,
        public ReleaseGroupType $type,
        public Description $description,
        public Note $note,
        public bool $isDisplay,
        public OrderNo $orderNo,
    ) {
    }

    public static function reconstruct(
        string $releaseGroupId,
        string $title,
        int $type,
        string $description,
        string $note,
        bool $isDisplay,
        int $orderNo,
    ): self {
        return new self(
            new ReleaseGroupId($releaseGroupId),
            new ReleaseGroupTitle($title),
            ReleaseGroupType::from($type),
            new Description($description),
            new Note($note),
            $isDisplay,
            new OrderNo($orderNo),
        );
    }

    /**
     * @return array{release_group_id: string, title: string, type: value-of<ReleaseGroupType>, description: string, note: string, is_display: bool, order_no: int}
     */
    public function toArray(): array
    {
        return [
            'release_group_id' => $this->releaseGroupId->value,
            'title' => $this->title->value,
            'type' => $this->type->value,
            'description' => $this->description->value,
            'note' => $this->note->value,
            'is_display' => $this->isDisplay,
            'order_no' => $this->orderNo->value,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->releaseGroupId->equals($other->releaseGroupId);
    }
}
