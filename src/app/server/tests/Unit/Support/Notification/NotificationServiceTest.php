<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Notification;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Notification\Contracts\Action;
use Support\Notification\Contracts\Embed\NotificationEmbed;
use Support\Notification\Contracts\NotificationDriverInterface;
use Support\Notification\Contracts\NotificationMessage;
use Support\Notification\Contracts\Status;
use Support\Notification\NotificationService;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    private MockInterface&NotificationDriverInterface $driver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->driver = Mockery::mock(NotificationDriverInterface::class);
    }

    #[Test]
    public function noticeDelegatesMessageToDriver(): void
    {
        $embeds = [
            new NotificationEmbed(title: 'ok'),
        ];
        $expected = new NotificationMessage(
            Action::MediaYoutubeImport,
            Status::Succeeded,
            embeds: $embeds,
        );

        $this->driver->shouldReceive('notice')
            ->once()
            ->with(Mockery::on(
                static fn (NotificationMessage $message): bool => $message->toJson() === $expected->toJson(),
            ));

        $this->getInstance()->notice(Action::MediaYoutubeImport, Status::Succeeded, embeds: $embeds);
    }

    #[Test]
    public function noticeDelegatesContentOnly(): void
    {
        $expected = new NotificationMessage(
            Action::MediaYoutubeImport,
            Status::Started,
            content: '取り込みを開始しました',
        );

        $this->driver->shouldReceive('notice')
            ->once()
            ->with(Mockery::on(
                static fn (NotificationMessage $message): bool => $message->toJson() === $expected->toJson(),
            ));

        $this->getInstance()->notice(
            Action::MediaYoutubeImport,
            Status::Started,
            content: '取り込みを開始しました',
        );
    }

    private function getInstance(): NotificationService
    {
        return new NotificationService($this->driver);
    }
}
