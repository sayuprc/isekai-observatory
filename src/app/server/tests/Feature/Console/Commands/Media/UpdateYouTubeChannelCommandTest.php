<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\Media;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class UpdateYouTubeChannelCommandTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    private const string CHANNEL_ID = 'UCabcdefghijklmnopqrstuv';

    #[Test]
    public function canUpdateChannelName(): void
    {
        $this->storeYouTubeChannels($this->createYouTubeChannel(self::CHANNEL_ID, '旧チャンネル名'));

        $this->artisan(sprintf('media:youtube-channel:update %s 新チャンネル名', self::CHANNEL_ID))
            ->expectsOutput(sprintf('チャンネルを更新しました: 新チャンネル名 (%s)', self::CHANNEL_ID))
            ->assertSuccessful();

        $rows = DB::table('youtube_channels')->get()->all();
        $this->assertCount(1, $rows);
        $this->assertSame('新チャンネル名', array_first($rows)->name);
    }

    #[Test]
    public function failureWhenChannelNotRegistered(): void
    {
        $this->artisan(sprintf('media:youtube-channel:update %s 新チャンネル名', self::CHANNEL_ID))
            ->expectsOutput(sprintf('登録されていないチャンネルです "%s"', self::CHANNEL_ID))
            ->assertFailed();
    }

    #[Test]
    public function failureWithEmptyName(): void
    {
        $this->storeYouTubeChannels($this->createYouTubeChannel(self::CHANNEL_ID, '旧チャンネル名'));

        $this->artisan('media:youtube-channel:update', ['channelId' => self::CHANNEL_ID, 'name' => ' '])
            ->expectsOutput('チャンネル名を入力してください')
            ->assertFailed();

        $this->assertSame('旧チャンネル名', array_first(DB::table('youtube_channels')->get()->all())->name);
    }
}
