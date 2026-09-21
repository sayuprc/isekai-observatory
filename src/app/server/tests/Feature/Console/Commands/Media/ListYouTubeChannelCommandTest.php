<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\Media;

use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class ListYouTubeChannelCommandTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canListChannels(): void
    {
        $this->storeYouTubeChannels(
            $this->createYouTubeChannel('UCaaaaaaaaaaaaaaaaaaaaaa', 'チャンネルA'),
            $this->createYouTubeChannel('UCbbbbbbbbbbbbbbbbbbbbbb', 'チャンネルB'),
        );

        $this->artisan('media:youtube-channel:list')
            ->expectsTable(
                ['チャンネルID', 'チャンネル名'],
                [
                    ['UCaaaaaaaaaaaaaaaaaaaaaa', 'チャンネルA'],
                    ['UCbbbbbbbbbbbbbbbbbbbbbb', 'チャンネルB'],
                ],
            )
            ->assertSuccessful();
    }

    #[Test]
    public function warnsWhenNoChannelsRegistered(): void
    {
        $this->artisan('media:youtube-channel:list')
            ->expectsOutput('チャンネルが登録されていません')
            ->assertSuccessful();
    }
}
