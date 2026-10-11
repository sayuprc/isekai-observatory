<?php

declare(strict_types=1);

namespace Media\Domain\Models;

use DateTimeImmutable;
use DateTimeZone;

readonly class Media
{
    public function __construct(
        public MediaId $mediaId,
        public MediaTitle $title,
        public MediaUrl $url,
        public MediaPublishedAt $publishedAt,
        public MediaType $type,
        public bool $isDisplay,
    ) {
    }

    public static function reconstruct(
        string $mediaId,
        string $title,
        string $url,
        DateTimeImmutable $publishedAt,
        int $type,
        bool $isDisplay,
    ): self {
        return new self(
            new MediaId($mediaId),
            new MediaTitle($title),
            new MediaUrl($url),
            new MediaPublishedAt($publishedAt),
            MediaType::from($type),
            $isDisplay,
        );
    }

    /**
     * @return array{media_id: string, title: string, url: string, published_at: string, type: value-of<MediaType>, is_display: bool}
     */
    public function toArray(): array
    {
        return [
            'media_id' => $this->mediaId->value,
            'title' => $this->title->value,
            'url' => $this->url->value,
            'published_at' => $this->publishedAt->value
                ->setTimezone(new DateTimeZone(date_default_timezone_get()))
                ->format('Y-m-d H:i:s'),
            'type' => $this->type->value,
            'is_display' => $this->isDisplay,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->mediaId->equals($other->mediaId);
    }
}
