<?php

declare(strict_types=1);

return [
    'cache_path' => base_path('bootstrap/cache/valinor'),

    /**
     * MapperInterface::map() に渡される型
     * warmupCacheFor() は再帰的にプロパティ型もキャッシュする
     */
    'warmup' => [
        \Auth\Application\Admin\UseCase\Refresh\RefreshInputData::class,
        \Auth\Domain\Services\Token\AccessToken\AccessTokenPayload::class,
        \Media\Application\Admin\UseCase\Create\CreateInputData::class,
        \Media\Application\Admin\UseCase\Search\SearchInputData::class,
        \Person\Application\Admin\UseCase\Create\CreateInputData::class,
        \Person\Application\Admin\UseCase\Search\SearchInputData::class,
        \Person\Application\Admin\UseCase\Update\UpdateInputData::class,
        \Release\Application\Admin\UseCase\Create\CreateInputData::class,
        \Release\Application\Admin\UseCase\Group\Create\CreateInputData::class,
        \Release\Application\Admin\UseCase\Group\Search\SearchInputData::class,
        \Release\Application\Admin\UseCase\Group\Update\UpdateInputData::class,
        \Release\Application\Admin\UseCase\Update\UpdateInputData::class,
        \Song\Application\Admin\UseCase\Create\CreateInputData::class,
        \Song\Application\Admin\UseCase\Search\SearchInputData::class,
        \Song\Application\Admin\UseCase\Tag\Create\CreateInputData::class,
        \Song\Application\Admin\UseCase\Tag\Search\SearchInputData::class,
        \Song\Application\Admin\UseCase\Tag\Update\UpdateInputData::class,
        \Song\Application\Admin\UseCase\Update\UpdateInputData::class,
        \Support\UseCase\AuditLog\Search\SearchInputData::class,
    ],
];
