<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Notification\Contracts;

use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use Support\Notification\Contracts\Action;
use Support\Notification\Contracts\Embed\Field;
use Support\Notification\Contracts\Embed\NotificationEmbed;
use Support\Notification\Contracts\NotificationMessage;
use Support\Notification\Contracts\Status;
use Tests\TestCase;

class NotificationMessageTest extends TestCase
{
    #[Test]
    public function toJsonMatchesDiscordNotifierContract(): void
    {
        $timestamp = new DateTimeImmutable('2026-08-11T22:00:00+09:00');
        $message = new NotificationMessage(
            Action::MediaYoutubeImport,
            Status::Failed,
            content: 'YouTube 取り込みでエラーが発生しました',
            embeds: [
                new NotificationEmbed(
                    title: 'YouTube 取り込みでエラーが発生しました',
                    timestamp: $timestamp,
                    fields: [
                        new Field(name: 'executed_at', value: '2026-08-11T22:00:00+09:00', inline: true),
                        new Field(name: 'channel_id', value: 'UCxxxx', inline: true),
                    ],
                ),
            ],
        );

        $this->assertSame(
            [
                'action' => 'media.youtube_import',
                'status' => 'failed',
                'content' => 'YouTube 取り込みでエラーが発生しました',
                'embeds' => [
                    [
                        'title' => 'YouTube 取り込みでエラーが発生しました',
                        'timestamp' => '2026-08-11T22:00:00+09:00',
                        'fields' => [
                            [
                                'inline' => true,
                                'name' => 'executed_at',
                                'value' => '2026-08-11T22:00:00+09:00',
                            ],
                            [
                                'inline' => true,
                                'name' => 'channel_id',
                                'value' => 'UCxxxx',
                            ],
                        ],
                    ],
                ],
            ],
            json_decode($message->toJson(), associative: true, flags: JSON_THROW_ON_ERROR),
        );
    }

    #[Test]
    public function toJsonAllowsContentOnly(): void
    {
        $message = new NotificationMessage(
            Action::MediaYoutubeImport,
            Status::Started,
            content: '取り込みを開始しました',
        );

        $this->assertSame(
            [
                'action' => 'media.youtube_import',
                'status' => 'started',
                'content' => '取り込みを開始しました',
            ],
            json_decode($message->toJson(), associative: true, flags: JSON_THROW_ON_ERROR),
        );
    }

    #[Test]
    public function toJsonAllowsEmbedsOnly(): void
    {
        $message = new NotificationMessage(
            Action::MediaYoutubeImport,
            Status::Succeeded,
            embeds: [
                new NotificationEmbed(title: 'first'),
                new NotificationEmbed(title: 'second'),
            ],
        );

        $decoded = json_decode($message->toJson(), associative: true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('media.youtube_import', $decoded['action']);
        $this->assertSame('succeeded', $decoded['status']);
        $this->assertArrayNotHasKey('content', $decoded);
        $this->assertCount(2, $decoded['embeds']);
        $this->assertSame('first', $decoded['embeds'][0]['title']);
        $this->assertSame('second', $decoded['embeds'][1]['title']);
    }

    #[Test]
    public function toJsonTrimsContentAndOmitsBlankContent(): void
    {
        $message = new NotificationMessage(
            Action::MediaYoutubeImport,
            Status::Failed,
            content: '  取り込み失敗  ',
            embeds: [
                new NotificationEmbed(title: 'boom'),
            ],
        );
        $decoded = json_decode($message->toJson(), associative: true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('取り込み失敗', $decoded['content']);

        $blankContent = new NotificationMessage(
            Action::MediaYoutubeImport,
            Status::Failed,
            content: '   ',
            embeds: [
                new NotificationEmbed(title: 'boom'),
            ],
        );
        $blankDecoded = json_decode($blankContent->toJson(), associative: true, flags: JSON_THROW_ON_ERROR);
        $this->assertArrayNotHasKey('content', $blankDecoded);
    }

    #[Test]
    public function constructorRequiresContentOrEmbeds(): void
    {
        $this->expectException(LogicException::class);

        new NotificationMessage(Action::MediaYoutubeImport, Status::Started);
    }

    #[Test]
    public function constructorRejectsBlankContentWithoutEmbeds(): void
    {
        $this->expectException(LogicException::class);

        new NotificationMessage(Action::MediaYoutubeImport, Status::Started, content: '   ');
    }
}
