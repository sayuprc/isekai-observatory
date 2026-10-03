<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use Override;
use Person\Application\Admin\Query\PersonGroupQueryServiceInterface;
use Person\Domain\Models\PersonGroupRepositoryInterface;
use Person\Domain\Models\PersonRepositoryInterface;
use Person\Infrastructures\Admin\PersonGroupQueryService;
use Person\Infrastructures\PersonGroupRepository;
use Person\Infrastructures\PersonRepository;

class PersonServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(PersonRepositoryInterface::class, PersonRepository::class);
        $this->app->bind(PersonGroupRepositoryInterface::class, PersonGroupRepository::class);
        $this->app->bind(PersonGroupQueryServiceInterface::class, PersonGroupQueryService::class);
    }
}
