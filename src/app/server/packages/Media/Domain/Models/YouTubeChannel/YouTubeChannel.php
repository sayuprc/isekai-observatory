<?php

declare(strict_types=1);

namespace Media\Domain\Models\YouTubeChannel;

readonly class YouTubeChannel
{
    public function __construct(
        public YouTubeChannelId $channelId,
        public YouTubeChannelName $name,
    ) {
    }

    public static function reconstruct(string $channelId, string $name): self
    {
        return new self(
            new YouTubeChannelId($channelId),
            new YouTubeChannelName($name),
        );
    }

    /**
     * @return array{channel_id: string, name: string}
     */
    public function toArray(): array
    {
        return [
            'channel_id' => $this->channelId->value,
            'name' => $this->name->value,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->channelId->equals($other->channelId);
    }
}
