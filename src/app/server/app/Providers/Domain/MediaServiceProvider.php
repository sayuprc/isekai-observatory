<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Google\Client;
use Google\Service\YouTube;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use Illuminate\Support\ServiceProvider;
use Media\Application\Admin\Query\MediaDetailQueryServiceInterface;
use Media\Application\Cli\Query\YouTubeShortVideoQueryServiceInterface;
use Media\Application\Cli\Query\YouTubeVideoQueryServiceInterface;
use Media\Application\Viewer\Query\MediaQueryServiceInterface as ViewerMediaQueryServiceInterface;
use Media\Domain\Models\MediaRepositoryInterface;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelRepositoryInterface;
use Media\Infrastructures\Admin\MediaDetailQueryService;
use Media\Infrastructures\Cli\YouTubeShortVideoQueryService;
use Media\Infrastructures\Cli\YouTubeVideoQueryService;
use Media\Infrastructures\MediaRepository;
use Media\Infrastructures\Viewer\MediaQueryService as ViewerMediaQueryService;
use Media\Infrastructures\YouTubeChannelRepository;
use Override;

class MediaServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(MediaRepositoryInterface::class, MediaRepository::class);
        $this->app->bind(MediaDetailQueryServiceInterface::class, MediaDetailQueryService::class);
        $this->app->bind(ViewerMediaQueryServiceInterface::class, ViewerMediaQueryService::class);
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
