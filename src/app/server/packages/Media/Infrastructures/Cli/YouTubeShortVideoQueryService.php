<?php

declare(strict_types=1);

namespace Media\Infrastructures\Cli;

use Generator;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Pool;
use Media\Application\Cli\Query\YouTubeShortVideoQueryServiceInterface;
use Media\Application\Cli\Query\YouTubeUploadedVideo;
use Override;
use Psr\Http\Message\ResponseInterface;
use Throwable;

readonly class YouTubeShortVideoQueryService implements YouTubeShortVideoQueryServiceInterface
{
    private const int CONCURRENCY = 8;

    public function __construct(private ClientInterface $httpClient)
    {
    }

    #[Override]
    public function detectShorts(array $videos): array
    {
        $results = [];

        $pool = new Pool($this->httpClient, $this->requests($videos), [
            'concurrency' => self::CONCURRENCY,
            'fulfilled' => static function (ResponseInterface $response, string $videoId) use (&$results): void {
                $statusCode = $response->getStatusCode();
                $results[$videoId] = $statusCode >= 200 && $statusCode < 300;
            },
            'rejected' => static function (Throwable $reason, string $videoId) use (&$results): void {
                $results[$videoId] = false;
            },
        ]);

        $pool->promise()->wait();

        return $results;
    }

    /**
     * @param list<YouTubeUploadedVideo> $videos
     *
     * @return Generator<string, callable(): mixed>
     */
    private function requests(array $videos): Generator
    {
        foreach ($videos as $video) {
            yield $video->videoId => fn () => $this->httpClient->requestAsync(
                'GET',
                sprintf('https://www.youtube.com/shorts/%s', rawurlencode($video->videoId)),
                [
                    'allow_redirects' => false,
                    'connect_timeout' => 2,
                    'http_errors' => false,
                    'timeout' => 5,
                ],
            );
        }
    }
}
