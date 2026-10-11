<?php

declare(strict_types=1);

namespace Tests\Unit\Media\Infrastructures\Cli;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Media\Application\Cli\Query\YouTubeUploadedVideo;
use Media\Infrastructures\Cli\YouTubeShortVideoQueryService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class YouTubeShortVideoQueryServiceTest extends TestCase
{
    #[Test]
    public function detectsShortsByRedirectStatus(): void
    {
        $service = new YouTubeShortVideoQueryService(new Client([
            'handler' => HandlerStack::create(new MockHandler([
                new Response(200),
                new Response(302),
            ])),
        ]));

        $results = $service->detectShorts([
            new YouTubeUploadedVideo('short-video', 'ショート動画', '2024-06-03T10:00:00Z'),
            new YouTubeUploadedVideo('normal-video', '通常動画', '2024-06-02T10:00:00Z'),
        ]);

        $this->assertSame([
            'short-video' => true,
            'normal-video' => false,
        ], $results);
    }
}
