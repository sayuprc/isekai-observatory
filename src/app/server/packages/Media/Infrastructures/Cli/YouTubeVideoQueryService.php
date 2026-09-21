<?php

declare(strict_types=1);

namespace Media\Infrastructures\Cli;

use Generator;
use Google\Service\YouTube;
use Google\Service\YouTube\Resource\Channels;
use Google\Service\YouTube\Resource\PlaylistItems;
use Media\Application\Cli\Query\YouTubeUploadedVideo;
use Media\Application\Cli\Query\YouTubeVideoQueryServiceInterface;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelId;
use Override;

readonly class YouTubeVideoQueryService implements YouTubeVideoQueryServiceInterface
{
    private const int MAX_RESULTS = 50;

    private Channels $channels;

    private PlaylistItems $playlistItems;

    // Google\Service\YouTube のリソースプロパティは型宣言を持たないため、ここで型を確定させる
    public function __construct(YouTube $youtube)
    {
        $channels = $youtube->channels;
        assert($channels instanceof Channels);

        $playlistItems = $youtube->playlistItems;
        assert($playlistItems instanceof PlaylistItems);

        $this->channels = $channels;
        $this->playlistItems = $playlistItems;
    }

    #[Override]
    public function fetchUploadedVideos(YouTubeChannelId $channelId): ?Generator
    {
        $response = $this->channels->listChannels('contentDetails', ['id' => $channelId->value]);

        $channel = $response->getItems()[0] ?? null;

        if (is_null($channel)) {
            return null;
        }

        $uploadsPlaylistId = $channel->getContentDetails()->getRelatedPlaylists()->getUploads();

        return $this->iterate($uploadsPlaylistId);
    }

    /**
     * @return Generator<int, YouTubeUploadedVideo>
     */
    private function iterate(string $playlistId): Generator
    {
        $pageToken = '';

        do {
            $options = [
                'playlistId' => $playlistId,
                'maxResults' => self::MAX_RESULTS,
            ];

            if ($pageToken !== '') {
                $options['pageToken'] = $pageToken;
            }

            $response = $this->playlistItems->listPlaylistItems('snippet', $options);

            foreach ($response->getItems() as $item) {
                $snippet = $item->getSnippet();

                yield new YouTubeUploadedVideo(
                    $snippet->getResourceId()->getVideoId(),
                    mb_trim($snippet->getTitle()),
                    $snippet->getPublishedAt(),
                );
            }

            // docblock 上は string だが、次ページが無い場合は実際には null が返る
            $pageToken = (string)$response->getNextPageToken();
        } while ($pageToken !== '');
    }
}
