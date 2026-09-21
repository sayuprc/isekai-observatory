<?php

declare(strict_types=1);

namespace Support\Notification\Contracts;

use LogicException;
use Support\Notification\Contracts\Embed\NotificationEmbed;

final readonly class NotificationMessage
{
    private ?string $content;

    /**
     * @param array<NotificationEmbed> $embeds
     */
    public function __construct(
        private Action $action,
        private Status $status,
        ?string $content = null,
        private array $embeds = [],
    ) {
        $this->content = is_null($content) ? null : mb_trim($content);

        if ((is_null($this->content) || $this->content === '') && count($this->embeds) === 0) {
            throw new LogicException('$content か $embeds のどちらか一方は必須です');
        }
    }

    public function toJson(): string
    {
        $array = [
            'action' => $this->action->value,
            'status' => $this->status->value,
        ];

        if (! is_null($this->content) && $this->content !== '') {
            $array['content'] = $this->content;
        }

        if (0 < count($this->embeds)) {
            $array['embeds'] = array_map(static fn (NotificationEmbed $embed) => $embed->toArray(), $this->embeds);
        }

        return json_encode($array, JSON_THROW_ON_ERROR);
    }
}
