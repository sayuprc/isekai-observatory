<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\Media;

use Illuminate\Support\Facades\DB;
use Media\Application\Cli\Query\YouTubeShortVideoQueryServiceInterface;
use Media\Application\Cli\Query\YouTubeUploadedVideo;
use Media\Application\Cli\Query\YouTubeVideoQueryServiceInterface;
use Media\Domain\Models\MediaType;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelId;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class ImportYouTubeCommandTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    private const string CHANNEL_ID = 'UCabcdefghijklmnopqrstuv';

    #[Test]
    public function canImportVideosAsMedia(): void
    {
        $this->storeYouTubeChannels($this->createYouTubeChannel(self::CHANNEL_ID, 'テストチャンネル'));

        $this->fakeVideoQueryService([
            self::CHANNEL_ID => [
                new YouTubeUploadedVideo('video-mv', '【Official Music Video】テスト曲', '2024-06-04T10:00:00Z'),
                new YouTubeUploadedVideo('video-short', 'テスト曲 #shorts', '2024-06-03T10:00:00Z'),
                new YouTubeUploadedVideo('video-short-no-tag', '縦型動画', '2024-06-03T09:00:00Z'),
                new YouTubeUploadedVideo('video-cover', '【歌ってみた】テストカバー', '2024-06-02T10:00:00Z'),
                new YouTubeUploadedVideo('video-other', '雑談配信アーカイブ', '2024-06-01T10:00:00Z'),
            ],
        ], ['video-short-no-tag']);

        $this->artisan('media:youtube:import')
            ->expectsOutput('テストチャンネル: 5 件取り込みました')
            ->assertSuccessful();

        $rows = DB::table('media')->orderByDesc('published_at')->get()->all();
        $this->assertCount(5, $rows);

        $expected = [
            ['https://www.youtube.com/watch?v=video-mv', '【Official Music Video】テスト曲', MediaType::Mv, '2024-06-04 19:00:00'],
            ['https://www.youtube.com/watch?v=video-short', 'テスト曲 #shorts', MediaType::Short, '2024-06-03 19:00:00'],
            ['https://www.youtube.com/watch?v=video-short-no-tag', '縦型動画', MediaType::Short, '2024-06-03 18:00:00'],
            ['https://www.youtube.com/watch?v=video-cover', '【歌ってみた】テストカバー', MediaType::AudioVideo, '2024-06-02 19:00:00'],
            ['https://www.youtube.com/watch?v=video-other', '雑談配信アーカイブ', MediaType::Other, '2024-06-01 19:00:00'],
        ];

        foreach ($expected as $i => [$url, $title, $type, $publishedAt]) {
            $this->assertSame($url, $rows[$i]->url);
            $this->assertSame($title, $rows[$i]->title);
            $this->assertSame($type->value, (int)$rows[$i]->type);
            $this->assertSame($publishedAt, $rows[$i]->published_at);
            $this->assertSame(0, (int)$rows[$i]->is_display);
        }
    }

    #[Test]
    public function stopsImportingWhenReachingSavedVideo(): void
    {
        $this->storeYouTubeChannels($this->createYouTubeChannel(self::CHANNEL_ID, 'テストチャンネル'));
        $this->storeMedia($this->createMedia(
            '00000000-0000-7000-8000-000000000001',
            '保存済み動画',
            'https://www.youtube.com/watch?v=video-saved',
            MediaType::Other,
            true,
        ));

        $this->fakeVideoQueryService([
            self::CHANNEL_ID => [
                new YouTubeUploadedVideo('video-new', '新しい動画', '2024-06-03T10:00:00Z'),
                new YouTubeUploadedVideo('video-saved', '保存済み動画', '2024-06-02T10:00:00Z'),
                new YouTubeUploadedVideo('video-old', '古い動画', '2024-06-01T10:00:00Z'),
            ],
        ]);

        $this->artisan('media:youtube:import')
            ->expectsOutput('テストチャンネル: 1 件取り込みました')
            ->assertSuccessful();

        $urls = DB::table('media')->pluck('url')->all();
        $this->assertEqualsCanonicalizing([
            'https://www.youtube.com/watch?v=video-saved',
            'https://www.youtube.com/watch?v=video-new',
        ], $urls);
    }

    #[Test]
    public function warnsWhenChannelNotFound(): void
    {
        $this->storeYouTubeChannels($this->createYouTubeChannel(self::CHANNEL_ID, 'テストチャンネル'));

        $this->fakeVideoQueryService([]);

        $this->artisan('media:youtube:import')
            ->expectsOutput(sprintf('テストチャンネル: チャンネルが見つかりませんでした (%s)', self::CHANNEL_ID))
            ->assertSuccessful();

        $this->assertSame(0, DB::table('media')->count());
    }

    #[Test]
    public function warnsWhenNoChannelsRegistered(): void
    {
        $this->fakeVideoQueryService([]);

        $this->artisan('media:youtube:import')
            ->expectsOutput('チャンネルが登録されていません')
            ->assertSuccessful();

        $this->assertSame(0, DB::table('media')->count());
    }

    /**
     * @param array<string, list<YouTubeUploadedVideo>> $videosByChannelId
     */
    private function fakeVideoQueryService(array $videosByChannelId, array $shortVideoIds = []): void
    {
        $this->app->instance(
            YouTubeVideoQueryServiceInterface::class,
            new readonly class ($videosByChannelId) implements YouTubeVideoQueryServiceInterface {
                /**
                 * @param array<string, list<YouTubeUploadedVideo>> $videosByChannelId
                 */
                public function __construct(private array $videosByChannelId)
                {
                }

                #[Override]
                public function fetchUploadedVideos(YouTubeChannelId $channelId): ?iterable
                {
                    return $this->videosByChannelId[$channelId->value] ?? null;
                }
            },
        );
        $this->app->instance(
            YouTubeShortVideoQueryServiceInterface::class,
            new readonly class ($shortVideoIds) implements YouTubeShortVideoQueryServiceInterface {
                /**
                 * @param list<string> $shortVideoIds
                 */
                public function __construct(private array $shortVideoIds)
                {
                }

                /**
                 * @param list<YouTubeUploadedVideo> $videos
                 *
                 * @return array<string, bool>
                 */
                #[Override]
                public function detectShorts(array $videos): array
                {
                    $shortVideoIds = array_flip($this->shortVideoIds);
                    $results = [];

                    foreach ($videos as $video) {
                        $results[$video->videoId] = isset($shortVideoIds[$video->videoId]);
                    }

                    return $results;
                }
            },
        );
    }
}
