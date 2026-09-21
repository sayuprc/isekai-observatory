<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface;
use AdminUser\Domain\Services\RegistrationToken\RandomTokenGeneratorInterface;
use AdminUser\Domain\Services\RegistrationToken\TokenHasherInterface;
use AdminUser\Infrastructures\AdminUserRepository;
use AdminUser\Infrastructures\RegistrationToken\RandomTokenGenerator;
use AdminUser\Infrastructures\RegistrationToken\RegistrationTokenRepository;
use AdminUser\Infrastructures\RegistrationToken\TokenHasher;
use Illuminate\Support\ServiceProvider;
use Override;

class AdminUserServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(AdminUserRepositoryInterface::class, AdminUserRepository::class);
        $this->app->bind(RegistrationTokenRepositoryInterface::class, RegistrationTokenRepository::class);
        $this->app->bind(RandomTokenGeneratorInterface::class, RandomTokenGenerator::class);
        $this->app->bind(TokenHasherInterface::class, TokenHasher::class);
    }
}
