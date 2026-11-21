<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Google\Client;
use Google\Service\YouTube;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use Illuminate\Support\ServiceProvider;
use Media\Application\Admin\UseCase\Get\Query\MediaDetailQueryServiceInterface;
use Media\Application\Admin\UseCase\ImportYouTube\Query\YouTubeShortVideoQueryServiceInterface;
use Media\Application\Admin\UseCase\ImportYouTube\Query\YouTubeVideoQueryServiceInterface;
use Media\Application\Admin\UseCase\Search\Query\MediaUsageCountQueryServiceInterface;
use Media\Domain\Models\MediaRepositoryInterface;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelRepositoryInterface;
use Media\Infrastructures\Admin\MediaDetailQueryService;
use Media\Infrastructures\Admin\MediaUsageCountQueryService;
use Media\Infrastructures\Admin\YouTubeShortVideoQueryService;
use Media\Infrastructures\Admin\YouTubeVideoQueryService;
use Media\Infrastructures\MediaRepository;
use Media\Infrastructures\YouTubeChannelRepository;
use Override;

class MediaServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(MediaRepositoryInterface::class, MediaRepository::class);
        $this->app->bind(MediaDetailQueryServiceInterface::class, MediaDetailQueryService::class);
        $this->app->bind(MediaUsageCountQueryServiceInterface::class, MediaUsageCountQueryService::class);
        $this->app->bind(YouTubeChannelRepositoryInterface::class, YouTubeChannelRepository::class);
        $this->app->bind(YouTubeVideoQueryServiceInterface::class, YouTubeVideoQueryService::class);
        $this->app->bind(YouTubeShortVideoQueryServiceInterface::class, YouTubeShortVideoQueryService::class);
        $this->app->bind(GuzzleClientInterface::class, GuzzleClient::class);

        $this->app->bind(YouTube::class, static function (): YouTube {
            $client = new Client();
            $client->setApplicationName(config()->string('app.name'));
            $client->setDeveloperKey(config()->string('services.youtube.api_key'));

            return new YouTube($client);
        });
    }
}
