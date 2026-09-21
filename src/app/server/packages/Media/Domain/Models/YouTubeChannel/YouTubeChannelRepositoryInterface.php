<?php

declare(strict_types=1);

namespace Media\Domain\Models\YouTubeChannel;

interface YouTubeChannelRepositoryInterface
{
    public function find(YouTubeChannelId $channelId): ?YouTubeChannel;

    /**
     * @return list<YouTubeChannel>
     */
    public function findAll(): array;

    public function save(YouTubeChannel $channel): YouTubeChannel;

    public function delete(YouTubeChannelId $channelId): void;
}
