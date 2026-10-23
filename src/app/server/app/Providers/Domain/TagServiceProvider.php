<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use Override;
use Tag\Application\Admin\Query\TagUsageCountQueryServiceInterface;
use Tag\Application\Public\Query\TagQueryServiceInterface as PublicTagQueryServiceInterface;
use Tag\Domain\Models\TagRepositoryInterface;
use Tag\Infrastructures\Admin\TagUsageCountQueryService;
use Tag\Infrastructures\Public\TagQueryService as PublicTagQueryService;
use Tag\Infrastructures\TagRepository;

class TagServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(TagUsageCountQueryServiceInterface::class, TagUsageCountQueryService::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
        $this->app->bind(PublicTagQueryServiceInterface::class, PublicTagQueryService::class);
    }
}
