<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\Media;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class AddYouTubeChannelCommandTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    private const string CHANNEL_ID = 'UCabcdefghijklmnopqrstuv';

    #[Test]
    public function canAddChannel(): void
    {
        $this->artisan(sprintf('media:youtube-channel:add %s テストチャンネル', self::CHANNEL_ID))
            ->expectsOutput(sprintf('チャンネルを追加しました: テストチャンネル (%s)', self::CHANNEL_ID))
            ->assertSuccessful();

        $rows = DB::table('youtube_channels')->get()->all();
        $this->assertCount(1, $rows);

        $row = array_first($rows);
        $this->assertSame(self::CHANNEL_ID, $row->channel_id);
        $this->assertSame('テストチャンネル', $row->name);
    }

    #[Test]
    public function failureWithInvalidChannelId(): void
    {
        $this->artisan('media:youtube-channel:add invalid-channel-id テストチャンネル')
            ->expectsOutput('YouTube チャンネルIDの形式が不正です: invalid-channel-id')
            ->assertFailed();

        $this->assertSame(0, DB::table('youtube_channels')->count());
    }

    #[Test]
    public function failureWithEmptyName(): void
    {
        $this->artisan('media:youtube-channel:add', ['channelId' => self::CHANNEL_ID, 'name' => ' '])
            ->expectsOutput('チャンネル名を入力してください')
            ->assertFailed();

        $this->assertSame(0, DB::table('youtube_channels')->count());
    }

    #[Test]
    public function failureWithDuplicatedChannel(): void
    {
        $this->storeYouTubeChannels($this->createYouTubeChannel(self::CHANNEL_ID, '登録済みチャンネル'));

        $this->artisan(sprintf('media:youtube-channel:add %s テストチャンネル', self::CHANNEL_ID))
            ->expectsOutput(sprintf('すでに登録されているチャンネルです "%s"', self::CHANNEL_ID))
            ->assertFailed();

        $rows = DB::table('youtube_channels')->get()->all();
        $this->assertCount(1, $rows);
        $this->assertSame('登録済みチャンネル', array_first($rows)->name);
    }
}
