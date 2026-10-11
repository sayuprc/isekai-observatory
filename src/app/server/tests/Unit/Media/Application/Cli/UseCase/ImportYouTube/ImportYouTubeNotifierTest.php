<?php

declare(strict_types=1);

namespace Tests\Unit\Media\Application\Cli\UseCase\ImportYouTube;

use Media\Application\Cli\UseCase\ImportYouTube\ChannelImportResult;
use Media\Application\Cli\UseCase\ImportYouTube\ImportedVideo;
use Media\Application\Cli\UseCase\ImportYouTube\ImportYouTubeNotifier;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Support\Notification\Contracts\Action;
use Support\Notification\Contracts\Color;
use Support\Notification\Contracts\Embed\NotificationEmbed;
use Support\Notification\Contracts\NotificationDriverInterface;
use Support\Notification\Contracts\NotificationMessage;
use Support\Notification\Contracts\Status;
use Support\Notification\NotificationService;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class ImportYouTubeNotifierTest extends TestCase
{
    use EntityFactory;

    private MockInterface&NotificationDriverInterface $driver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->driver = Mockery::mock(NotificationDriverInterface::class);
    }

    #[Test]
    public function succeededNotifiesWithResultEmbeds(): void
    {
        $expected = new NotificationMessage(
            Action::MediaYoutubeImport,
            Status::Succeeded,
            content: 'YouTube からの取り込みを実行しました',
            embeds: [
                new NotificationEmbed(
                    title: '成功ch の取り込みが完了',
                    description: implode("\n", [
                        '取り込み件数: 2',
                        '',
                        '- [動画A](https://www.youtube.com/watch?v=aaaaaaaaaaa)',
                        '- [動画B](https://www.youtube.com/watch?v=bbbbbbbbbbb)',
                    ]),
                    color: Color::Success,
                    url: 'https://www.youtube.com/channel/UCabcdefghijklmnopqrstuv',
                ),
                new NotificationEmbed(
                    title: '失敗ch に失敗',
                    description: 'チャンネルが見つかりませんでした',
                    color: Color::Error,
                    url: 'https://www.youtube.com/channel/UCabcdefghijklmnopqrstuw',
                ),
                new NotificationEmbed(
                    title: '0件ch の取り込みが完了',
                    description: '取り込み件数: 0',
                    color: Color::Warning,
                    url: 'https://www.youtube.com/channel/UCabcdefghijklmnopqrstux',
                ),
            ],
        );

        $this->driver->shouldReceive('notice')
            ->once()
            ->with(Mockery::on(
                static fn (NotificationMessage $message): bool => $message->toJson() === $expected->toJson(),
            ));

        $this->getInstance()->succeeded([
            ChannelImportResult::imported(
                $this->createYouTubeChannel('UCabcdefghijklmnopqrstuv', '成功ch'),
                [
                    new ImportedVideo('動画A', 'https://www.youtube.com/watch?v=aaaaaaaaaaa'),
                    new ImportedVideo('動画B', 'https://www.youtube.com/watch?v=bbbbbbbbbbb'),
                ],
            ),
            ChannelImportResult::channelNotFound(
                $this->createYouTubeChannel('UCabcdefghijklmnopqrstuw', '失敗ch'),
            ),
            ChannelImportResult::imported(
                $this->createYouTubeChannel('UCabcdefghijklmnopqrstux', '0件ch'),
                [],
            ),
        ]);
    }

    #[Test]
    public function succeededOmitsOverflowEmbedsWithLimitNotice(): void
    {
        $results = [];

        for ($i = 0; $i < 12; $i++) {
            $channelId = sprintf('UCabcdefghijklmnopqrstu%c', 97 + $i);
            $results[] = ChannelImportResult::imported(
                $this->createYouTubeChannel($channelId, sprintf('ch%d', $i)),
                [],
            );
        }

        $this->driver->shouldReceive('notice')
            ->once()
            ->with(Mockery::on(function (NotificationMessage $message): bool {
                $payload = json_decode($message->toJson(), true);
                $this->assertSame(Action::MediaYoutubeImport->value, $payload['action']);
                $this->assertSame(Status::Succeeded->value, $payload['status']);
                $this->assertCount(10, $payload['embeds']);
                $this->assertSame('ch0 の取り込みが完了', $payload['embeds'][0]['title']);
                $this->assertSame('ch8 の取り込みが完了', $payload['embeds'][8]['title']);
                $this->assertSame('一部の結果を省略しました', $payload['embeds'][9]['title']);
                $this->assertSame(
                    'Discord の embeds 上限 (10件) のため、他 3 チャンネル分の結果は省略しています',
                    $payload['embeds'][9]['description'],
                );
                $this->assertSame(Color::Warning->value, $payload['embeds'][9]['color']);

                return true;
            }));

        $this->getInstance()->succeeded($results);
    }

    #[Test]
    public function succeededOmitsOverflowVideoLinksWithDescriptionLimitNotice(): void
    {
        $videos = [];

        for ($i = 0; $i < 80; $i++) {
            $videos[] = new ImportedVideo(
                str_repeat('あ', 40) . $i,
                sprintf('https://www.youtube.com/watch?v=%s', str_pad((string)$i, 11, '0', STR_PAD_LEFT)),
            );
        }

        $this->driver->shouldReceive('notice')
            ->once()
            ->with(Mockery::on(function (NotificationMessage $message) use ($videos): bool {
                $payload = json_decode($message->toJson(), true);
                $description = $payload['embeds'][0]['description'];
                $this->assertLessThanOrEqual(4096, mb_strlen($description));
                $this->assertStringStartsWith('取り込み件数: 80', $description);
                $this->assertStringContainsString(
                    'Discord の description 上限 (4096文字) のため省略しています',
                    $description,
                );
                $this->assertStringContainsString($videos[0]->url, $description);
                $this->assertStringNotContainsString($videos[79]->url, $description);

                return true;
            }));

        $this->getInstance()->succeeded([
            ChannelImportResult::imported(
                $this->createYouTubeChannel('UCabcdefghijklmnopqrstuv', '長文ch'),
                $videos,
            ),
        ]);
    }

    #[Test]
    public function failedNotifiesWithExceptionMessage(): void
    {
        $expected = new NotificationMessage(
            Action::MediaYoutubeImport,
            Status::Failed,
            content: 'YouTube からの取り込みで例外が発生しました',
            embeds: [
                new NotificationEmbed(
                    title: 'boom',
                    color: Color::Error,
                ),
            ],
        );

        $this->driver->shouldReceive('notice')
            ->once()
            ->with(Mockery::on(
                static fn (NotificationMessage $message): bool => $message->toJson() === $expected->toJson(),
            ));

        $this->getInstance()->failed(new RuntimeException('boom'));
    }

    private function getInstance(): ImportYouTubeNotifier
    {
        return new ImportYouTubeNotifier(new NotificationService($this->driver));
    }
}
