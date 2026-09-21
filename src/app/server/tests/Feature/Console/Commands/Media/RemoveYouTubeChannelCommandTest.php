<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\Media;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class RemoveYouTubeChannelCommandTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    private const string CHANNEL_ID = 'UCabcdefghijklmnopqrstuv';

    #[Test]
    public function canRemoveChannel(): void
    {
        $this->storeYouTubeChannels($this->createYouTubeChannel(self::CHANNEL_ID, 'テストチャンネル'));

        $this->artisan(sprintf('media:youtube-channel:remove %s', self::CHANNEL_ID))
            ->expectsOutput(sprintf('チャンネルを削除しました: %s', self::CHANNEL_ID))
            ->assertSuccessful();

        $this->assertSame(0, DB::table('youtube_channels')->count());
    }

    #[Test]
    public function failureWhenChannelNotRegistered(): void
    {
        $this->artisan(sprintf('media:youtube-channel:remove %s', self::CHANNEL_ID))
            ->expectsOutput(sprintf('登録されていないチャンネルです "%s"', self::CHANNEL_ID))
            ->assertFailed();
    }

    #[Test]
    public function failureWithInvalidChannelId(): void
    {
        $this->artisan('media:youtube-channel:remove invalid-channel-id')
            ->expectsOutput('YouTube チャンネルIDの形式が不正です: invalid-channel-id')
            ->assertFailed();
    }
}
