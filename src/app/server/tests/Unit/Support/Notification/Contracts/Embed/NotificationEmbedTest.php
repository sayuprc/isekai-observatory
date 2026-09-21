<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Notification\Contracts\Embed;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Support\Notification\Contracts\Color;
use Support\Notification\Contracts\Embed\Field;
use Support\Notification\Contracts\Embed\NotificationEmbed;
use Tests\TestCase;

class NotificationEmbedTest extends TestCase
{
    #[Test]
    public function toArrayIncludesOnlyPresentOptionalFields(): void
    {
        $embed = new NotificationEmbed(
            title: 'title',
            url: 'https://example.com',
            fields: [
                new Field(name: 'name', value: 'value'),
            ],
        );

        $this->assertSame(
            [
                'title' => 'title',
                'url' => 'https://example.com',
                'fields' => [
                    [
                        'inline' => false,
                        'name' => 'name',
                        'value' => 'value',
                    ],
                ],
            ],
            $embed->toArray(),
        );
    }

    #[Test]
    public function toArrayFormatsTimestampAsIso8601(): void
    {
        $timestamp = new DateTimeImmutable('2026-01-01T12:34:56+00:00');
        $embed = new NotificationEmbed(
            description: 'description',
            timestamp: $timestamp,
        );

        $this->assertSame(
            [
                'description' => 'description',
                'timestamp' => '2026-01-01T12:34:56+00:00',
            ],
            $embed->toArray(),
        );
    }

    #[Test]
    public function toArrayIncludesColorValue(): void
    {
        $embed = new NotificationEmbed(
            title: 'failed',
            color: Color::Error,
        );

        $this->assertSame(
            [
                'title' => 'failed',
                'color' => 0xED_4245,
            ],
            $embed->toArray(),
        );
    }
}
