<?php

declare(strict_types=1);

namespace Support\Notification\Contracts\Embed;

use DateTimeInterface;
use Support\Notification\Contracts\Color;

/**
 * @phpstan-import-type _field from Field
 */
readonly class NotificationEmbed
{
    private ?string $title;

    private ?string $description;

    private ?string $url;

    /**
     * @param array<Field> $fields
     */
    public function __construct(
        ?string $title = null,
        ?string $description = null,
        ?string $url = null,
        private ?Color $color = null,
        private ?DateTimeInterface $timestamp = null,
        private array $fields = [],
    ) {
        $this->title = $title === null ? null : mb_trim($title);
        $this->description = $description === null ? null : mb_trim($description);
        $this->url = $url === null ? null : mb_trim($url);
    }

    /**
     * @return array{title?: string, description?: string, url?: string, color?: int, timestamp?: string, fields?: array<_field>}
     */
    public function toArray(): array
    {
        $array = [];

        if ($this->title !== null && $this->title !== '') {
            $array['title'] = $this->title;
        }

        if ($this->description !== null && $this->description !== '') {
            $array['description'] = $this->description;
        }

        if ($this->url !== null && $this->url !== '') {
            $array['url'] = $this->url;
        }

        if ($this->color !== null) {
            $array['color'] = $this->color->value;
        }

        if ($this->timestamp !== null) {
            $array['timestamp'] = $this->timestamp->format('c');
        }

        if (0 < count($this->fields)) {
            $array['fields'] = array_map(static fn (Field $field) => $field->toArray(), $this->fields);
        }

        return $array;
    }
}
