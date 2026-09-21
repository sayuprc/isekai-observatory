<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use Media\Domain\Models\MediaRepositoryInterface;
use Media\Infrastructures\MediaRepository;
use Override;
use Person\Domain\Services\PersonUsageCheckerInterface;
use Song\Application\Admin\Query\SongQueryServiceInterface;
use Song\Application\Viewer\Query\SongQueryServiceInterface as ViewerSongQueryServiceInterface;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Song\Infrastructures\Admin\SongQueryService;
use Song\Infrastructures\PersonUsageChecker;
use Song\Infrastructures\SongRepository;
use Song\Infrastructures\Tag\SongTagRepository;
use Song\Infrastructures\Viewer\SongQueryService as ViewerSongQueryService;

class SongServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(SongRepositoryInterface::class, SongRepository::class);
        $this->app->bind(SongTagRepositoryInterface::class, SongTagRepository::class);
        $this->app->bind(PersonUsageCheckerInterface::class, PersonUsageChecker::class);
        $this->app->bind(MediaRepositoryInterface::class, MediaRepository::class);

        $this->app->bind(SongQueryServiceInterface::class, SongQueryService::class);
        $this->app->bind(ViewerSongQueryServiceInterface::class, ViewerSongQueryService::class);
    }
}
