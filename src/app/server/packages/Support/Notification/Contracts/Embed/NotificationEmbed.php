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
        $this->title = is_null($title) ? null : mb_trim($title);
        $this->description = is_null($description) ? null : mb_trim($description);
        $this->url = is_null($url) ? null : mb_trim($url);
    }

    /**
     * @return array{title?: string, description?: string, url?: string, color?: int, timestamp?: string, fields?: array<_field>}
     */
    public function toArray(): array
    {
        $array = [];

        if (! is_null($this->title) && $this->title !== '') {
            $array['title'] = $this->title;
        }

        if (! is_null($this->description) && $this->description !== '') {
            $array['description'] = $this->description;
        }

        if (! is_null($this->url) && $this->url !== '') {
            $array['url'] = $this->url;
        }

        if (! is_null($this->color)) {
            $array['color'] = $this->color->value;
        }

        if (! is_null($this->timestamp)) {
            $array['timestamp'] = $this->timestamp->format('c');
        }

        if (0 < count($this->fields)) {
            $array['fields'] = array_map(static fn (Field $field) => $field->toArray(), $this->fields);
        }

        return $array;
    }
}
