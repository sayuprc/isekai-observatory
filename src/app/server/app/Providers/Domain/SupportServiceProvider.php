<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use CuyZ\Valinor\MapperBuilder;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Override;
use Support\Contracts\ClockInterface;
use Support\Contracts\MapperInterface;
use Support\Contracts\TransactionInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Infrastructures\AuditLog\AuditLogRecorder;
use Support\Infrastructures\Clock;
use Support\Infrastructures\DbTransaction;
use Support\Infrastructures\Mapper;
use Support\Infrastructures\Query\AuditLog\AuditLogQueryService;
use Support\Infrastructures\Uuid\UuidConverter;
use Support\Infrastructures\Uuid\UuidGenerator;
use Support\Infrastructures\Valinor\MapperBuilderFactory;
use Support\Notification\Contracts\NotificationDriverInterface;
use Support\Notification\DebugInfrastructures\NopNotificationPubSubDriver;
use Support\Notification\Infrastructures\NotificationConfig;
use Support\Notification\Infrastructures\NotificationPubSubDriver;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\Query\AuditLogQueryServiceInterface;

class SupportServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->singleton(
            MapperBuilder::class,
            static fn (Application $app): MapperBuilder => $app->make(MapperBuilderFactory::class)->create(),
        );
        $this->app->bind(MapperInterface::class, Mapper::class);
        $this->app->bind(UuidGeneratorInterface::class, UuidGenerator::class);
        $this->app->bind(UuidConverterInterface::class, UuidConverter::class);
        $this->app->bind(TransactionInterface::class, DbTransaction::class);
        $this->app->bind(ClockInterface::class, Clock::class);
        $this->app->bind(AuditLogRecorderInterface::class, AuditLogRecorder::class);
        $this->app->bind(AuditLogQueryServiceInterface::class, AuditLogQueryService::class);

        $this->app->singleton(
            NotificationConfig::class,
            static fn () => new NotificationConfig(
                config()->string('gc.google_cloud_project'),
                config()->string('gc.pubsub.notification.topic'),
            ),
        );
        $this->app->singleton(
            NotificationDriverInterface::class,
            static fn (Application $app) => config()->boolean('gc.pubsub.notification.enabled')
                ? $app->make(NotificationPubSubDriver::class)
                : $app->make(NopNotificationPubSubDriver::class),
        );
    }
}
