<?php

declare(strict_types=1);

namespace Support\Notification\Contracts\Embed;

/**
 * @phpstan-type _field array{name?: string, value?: string, inline: bool}
 */
final readonly class Field
{
    private ?string $name;

    private ?string $value;

    public function __construct(
        ?string $name = null,
        ?string $value = null,
        private bool $inline = false,
    ) {
        $this->name = $name === null ? null : mb_trim($name);
        $this->value = $value === null ? null : mb_trim($value);
    }

    /**
     * @return _field
     */
    public function toArray(): array
    {
        $array = [
            'inline' => $this->inline,
        ];

        if ($this->name !== null && $this->name !== '') {
            $array['name'] = $this->name;
        }

        if ($this->value !== null && $this->value !== '') {
            $array['value'] = $this->value;
        }

        return $array;
    }
}
